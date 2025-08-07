<?php

namespace app\api\controller;

use app\admin\model\Service;
use app\admin\model\ServiceOrder;
use app\admin\model\ServiceSchedule;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Request;
use Webman\RedisQueue\Client;

class ServiceController extends Base
{

    /**
     * 获取项目列表
     * @param Request $request
     * @return \support\Response
     */
    function getServiceList(Request $request)
    {
        $rows = Service::normal()
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    /**
     * 获取项目详情
     * @param Request $request
     * @return \support\Response
     */
    function getServiceDetail(Request $request)
    {
        $id = $request->input('id');
        $row = Service::normal()->withExists([
            'favoritedByUser as is_favorited' => function ($query) use ($request) {
                $query->where('user_id', $request->user_id);
            }
        ])->find($id);
        return $this->success('成功', $row);
    }

    /**
     * 排班列表
     * @param Request $request
     * @return \support\Response
     */
    function getScheduleList(Request $request)
    {
        $id = $request->input('id');
        $startDate = Carbon::today(); // 00:00:00
        $endDate = Carbon::today()->addDays(7); // +7天后 00:00:00
        $schedules = ServiceSchedule::where('service_id', $id)->whereBetween('date', [$startDate, $endDate])->get()->groupBy(function ($item){
            return $item->date->toDateString();  // 保证 key 是 'Y-m-d' 格式
        });
        // 构建索引数组结构
        $result = [];

        foreach ($startDate->daysUntil($endDate->copy()->addDay()) as $date) {

            $dateKey = $date->toDateString();        // '2025-07-10'
            $formattedDate = $date->format('m/d');   // '07/10'
            $diff = (int) round($startDate->diffInDays($date));
            $dayLabel = match ($diff) {
                0 => '今天',
                1 => '明天',
                default => $date->locale('zh_CN')->translatedFormat('D'),
            };
            $data = [
                'date' => $formattedDate,
                'day_of_week' => $dayLabel,
                'schedules' => $schedules->get($dateKey, collect())->values(),
            ];

            $result[] = $data;
        }
        return $this->success('成功', $result);
    }

    /**
     * 预创建订单
     * @param Request $request
     * @return \support\Response
     */
    function preCreateOrder(Request $request)
    {
        $id = $request->input('id');
        $schedule_ids = $request->input('schedule_ids');//预约时间
        $schedule_ids = explode(',', $schedule_ids);
        $service = Service::normal()->find($id);
        $schedules = ServiceSchedule::whereIn('id', $schedule_ids)->get();

        $discount_amount = 0;

        $pay_amount = $service->price;

        $data = [
            'pay_amount' => $pay_amount,
            'discount_amount' => $discount_amount,
            'service' => $service,
            'schedules' => $schedules,
        ];

        return $this->success('成功', $data);
    }

    /**
     * 创建订单
     * @param Request $request
     * @return \support\Response
     */
    function createOrder(Request $request)
    {
        $id = $request->input('id');
        $schedule_ids = $request->input('schedule_ids');//预约时间
        $mobile = $request->input('mobile');
        $schedule_ids = explode(',', $schedule_ids);
        $service = Service::normal()->find($id);
        if (!$service) {
            return $this->fail('未找到该项目');
        }
        $user = $request->user();


        $schedules = ServiceSchedule::whereIn('id', $schedule_ids)->get();
        if ($schedules->isEmpty()) {
            return $this->fail('未找到该时间段');
        }
        $conflict = $schedules->first(function (ServiceSchedule $schedule) {
            return $schedule->status != 1;
        });

        if ($conflict) {
            return $this->fail($conflict->start_time . '-' . $conflict->end_time . ' 时间段已被预约');
        }


        if ($schedules->count() > 1) {
            return $this->fail('最多只能预约1个时间段');
        }



        $discount_amount = 0;
        $price = $service->price;

        $pay_amount = $price - $discount_amount;
        $ordersn = Pay::generateOrderSn();
        #创建订单
        $order = ServiceOrder::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'schedule_id' => $schedules->first()->id,
            'pay_amount' => $pay_amount,
            'price' => $price,
            'discount_amount' => $discount_amount,
            'ordersn' => $ordersn,
            'mobile' => $mobile,
        ]);


        #更改排班状态
        $schedules->each(function (ServiceSchedule $schedule) {
            $schedule->num -= 1;
            #如果剩余数量小于等于0 则设置为已满
            if ($schedule->num <= 0){
                $schedule->status = 2;
            }
            $schedule->save();
        });
        Client::send('job', ['id' => $order->id, 'event' => 'service_order_expire'], 60 * 5);
        return $this->success('成功', $order);
    }


    /**
     * 支付
     * @param Request $request
     * @return \support\Response
     * @throws \Throwable
     */
    function pay(Request $request)
    {
        $ordersn = $request->input('ordersn');
        $pay_type = $request->input('pay_type');#支付方式:1=微信,2=余额

        $order = ServiceOrder::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('请刷新订单列表');
        }
        $pay_amount = $order->pay_amount;
        if ($pay_amount <= 0) {
            $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'service']);
            $res = (new NotifyController())->balance($request);;
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            return $this->success('支付成功');
        } else {
            if ($pay_type == 1) {
                $result = Pay::pay($pay_type, $pay_amount, $ordersn, '挂号费', 'service');
                return $this->success('唤醒微信', $result);
            } else {
                $user = $request->user();
                if ($user->money < $pay_amount) {
                    return $this->fail('余额不足');
                }
                $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'service']);
                $res = (new NotifyController())->balance($request);;
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    return $this->fail($res->msg);
                }
                User::changeMoney(-$pay_amount, $user->id, '预约项目');
                return $this->success('支付成功');
            }
        }
    }

    /**
     * 我的预约
     * @param Request $request
     * @return \support\Response
     */
    function getMyOrderList(Request $request)
    {
        $status = $request->input('status');#状态：0全部 1待付款 2已预约 3已完成 4已过期
        $rows = ServiceOrder::with(['service', 'schedule'])
            ->where('user_id', $request->user_id)
            ->where(function ($query) use ($status) {
                if ($status == 1){
                    $query->where('status', 0);
                }
                if ($status == 2){
                    $query->where('status', 2);
                }
                if ($status == 3){
                    $query->where('status', 3);
                }
                if ($status == 4){
                    $query->where('status', 4);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    /**
     * 预约详情
     * @param Request $request
     * @return \support\Response
     */
    function getOrderDetail(Request $request)
    {
        $id = $request->input('id');
        $order = ServiceOrder::with(['service', 'schedule'])->find($id);
        return $this->success('成功', $order);
    }




}
