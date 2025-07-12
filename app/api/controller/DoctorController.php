<?php

namespace app\api\controller;

use app\admin\model\Doctor;
use app\admin\model\DoctorClass;
use app\admin\model\DoctorOrder;
use app\admin\model\DoctorOrderRecord;
use app\admin\model\DoctorSchedule;
use app\admin\model\User;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Request;
use Webman\RedisQueue\Client;

class DoctorController extends Base
{
    /**
     * 获取门店列表
     * @param Request $request
     * @return \support\Response
     */
    function getFirstClassList(Request $request)
    {
        $rows = DoctorClass::whereNull('pid')->orderByDesc('weigh')->get();
        return $this->success('成功', $rows);
    }

    /**
     * 获取科室列表
     * @param Request $request
     * @return \support\Response
     */
    function getSecClassList(Request $request)
    {
        $pid = $request->post('pid');
        $rows = DoctorClass::where('pid', $pid)->orderByDesc('weigh')->get();
        return $this->success('成功', $rows);
    }

    /**
     * 获取医生列表
     * @param Request $request
     * @return \support\Response
     */
    function getDoctorList(Request $request)
    {
        $class_first_id = $request->post('class_first_id');
        $class_sec_id = $request->post('class_sec_id');
        $rows = Doctor::normal()
            ->when(!empty($class_first_id), function ($query) use ($class_first_id) {
                $query->where('class_first_id', $class_first_id);
            })
            ->when(!empty($class_sec_id), function ($query) use ($class_sec_id) {
                $query->where('class_sec_id', $class_sec_id);
            })
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }


    /**
     * 获取医生详情
     * @param Request $request
     * @return \support\Response
     */
    function getDoctorDetail(Request $request)
    {
        $id = $request->input('id');
        $doctor = Doctor::normal()->find($id);
        return $this->success('成功', $doctor);
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
        $schedules = DoctorSchedule::where('doctor_id', $id)->whereBetween('date', [$startDate, $endDate])->get()->groupBy('date');
        // 构建索引数组结构
        $result = [];
        $weekMap = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];

        foreach (Carbon::parse($startDate)->daysUntil($endDate->copy()->addDay()) as $date) {
            $dateKey = $date->toDateString();        // '2025-07-10'
            $formattedDate = $date->format('m/d');   // '07/10'
            $diff = $date->diffInDays($startDate);

            $dayLabel = match ($diff) {
                0 => '今天',
                1 => '明天',
                default => $weekMap[$date->dayOfWeek],
            };

            $result[] = [
                'date' => $formattedDate,
                'day_of_week' => $dayLabel,
                'schedules' => $schedules->get($dateKey, collect())->values(),
            ];
        }
        return $this->success('成功', $result);
    }

    function preCreateOrder(Request $request)
    {
        $id = $request->input('id');
        $schedule_ids = $request->input('schedule_ids');//预约时间
        $schedule_ids = explode(',', $schedule_ids);
        $doctor = Doctor::normal()->find($id);
        if (!$doctor) {
            return $this->fail('未找到该医师');
        }
        $user = $request->user();
        if ($user->vip_level < $doctor->vip_level) {
            return $this->fail('请提升会员等级');
        }
        $schedules = DoctorSchedule::whereIn('id', $schedule_ids)->get();
        if ($schedules->isEmpty()) {
            return $this->fail('未找到该时间段');
        }
        $conflict = $schedules->first(function (DoctorSchedule $schedule) {
            return $schedule->status != 1;
        });

        if ($conflict) {
            return $this->fail($conflict->start_time . '-' . $conflict->end_time . ' 时间段已被预约');
        }

        $pay_amount = $schedules->count() * $doctor->price;
        if ($user->vip_level >= 5) {
            $pay_amount = 0;
        }

        $data = [
            'pay_amount' => $pay_amount,
            'doctor' => $doctor,
            'schedules' => $schedules,
        ];

        return $this->success('成功', $data);
    }

    function createOrder(Request $request)
    {
        $id = $request->input('id');
        $schedule_ids = $request->input('schedule_ids');//预约时间
        $schedule_ids = explode(',', $schedule_ids);
        $doctor = Doctor::normal()->find($id);
        if (!$doctor) {
            return $this->fail('未找到该医师');
        }
        $user = $request->user();
        if ($user->vip_level < $doctor->vip_level) {
            return $this->fail('请提升会员等级');
        }


        $schedules = DoctorSchedule::whereIn('id', $schedule_ids)->get();
        if ($schedules->isEmpty()) {
            return $this->fail('未找到该时间段');
        }
        $conflict = $schedules->first(function (DoctorSchedule $schedule) {
            return $schedule->status != 1;
        });

        if ($conflict) {
            return $this->fail($conflict->start_time . '-' . $conflict->end_time . ' 时间段已被预约');
        }

        if ($user->vip_level >= 3) {
            //预约的是专家号
        } else {
            //预约的是普通号
            if ($schedules->count() > 1) {
                return $this->fail('普通号最多只能预约1个时间段');
            }
        }


        $discount_amount = 0;
        $price = $doctor->price;
        if ($user->vip_level >= 5) {
            $discount_amount = $doctor->price;
        }

        $pay_amount = $price - $discount_amount;
        $ordersn = Pay::generateOrderSn();
        #创建订单
        $order = DoctorOrder::create([
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
            'pay_amount' => $pay_amount,
            'price' => $price,
            'discount_amount' => $discount_amount,
            'ordersn' => $ordersn,
        ]);


        #更改排班状态
        $schedules->each(function ($schedule) use ($order) {
            $schedule->status = 2;
            $schedule->save();
            $order->scheduleItem()->create([
                'schedule_id' => $schedule->id,
            ]);
        });
        Client::send('job', ['id' => $order->id, 'event' => 'order_expire'], 60 * 5);
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
        $name = $request->input('name');
        $sex = $request->input('sex');#性别：0女 1男
        $age = $request->input('age');
        $mobile = $request->input('mobile');
        if (!$name) {
            return $this->fail('请填写姓名');
        }
        if (!$sex) {
            return $this->fail('请选择性别');
        }
        if (!$age) {
            return $this->fail('请填写年龄');
        }
        if (!$mobile) {
            return $this->fail('请填写手机号');
        }

        $order = DoctorOrder::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('请刷新订单列表');
        }

        $order->name = $name;
        $order->sex = $sex;
        $order->age = $age;
        $order->mobile = $mobile;
        $order->save();

        $pay_amount = $order->pay_amount;
        if ($pay_amount <= 0) {
            $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'doctor']);
            $res = (new NotifyController())->balance($request);;
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            return $this->success('支付成功');
        } else {
            if ($pay_type == 1) {
                $result = Pay::pay($pay_type, $pay_amount, $ordersn, '挂号费', 'doctor');
                return $this->success('唤醒微信', $result);
            } else {
                $user = $request->user();
                if ($user->money < $pay_amount) {
                    return $this->fail('余额不足');
                }
                $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'doctor']);
                $res = (new NotifyController())->balance($request);;
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    return $this->fail($res->msg);
                }
                User::changeMoney(-$pay_amount, $user->id, '挂号费');
                return $this->success('支付成功');
            }
        }
    }


    function getMyOrderList(Request $request)
    {
        $status = $request->input('status');#状态：0全部 1待确认 2已预约 3已完成 4过号未诊
        $rows = DoctorOrder::with(['doctor', 'schedule'])
            ->where('user_id', $request->user_id)
            ->where(function ($query) use ($status) {
                if (in_array($status, [1, 2, 3, 4])) {
                    $query->where('status', $status);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    function getOrderDetail(Request $request)
    {
        $id = $request->input('id');
        $order = DoctorOrder::with(['doctor', 'schedule'])->find($id);
        return $this->success('成功', $order);
    }

    /**
     * 获取健康档案
     * @param Request $request
     * @return \support\Response
     */
    function getMyHealthList(Request $request)
    {
        $status = $request->input('status');#状态：0全部  1待付款 2已付款
        $rows = DoctorOrderRecord::with(['order','medicine'])
            ->where('user_id', $request->user_id)
            ->where(function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('status', 0);
                }
                if ($status == 2) {
                    $query->where('status', 1);
                }
            })
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

    function getHealthDetail(Request $request)
    {
        $id = $request->input('id');
        $order = DoctorOrderRecord::with(['order','medicine'])->find($id);
        return $this->success('成功', $order);
    }

    function payHealth(Request $request)
    {
        $ordersn = $request->input('ordersn');
        $pay_type = $request->input('pay_type');#支付方式:1=微信,2=余额

        $order = DoctorOrderRecord::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('请刷新订单列表');
        }


        $pay_amount = $order->pay_amount;
        if ($pay_amount <= 0) {
            $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'health']);
            $res = (new NotifyController())->balance($request);;
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            return $this->success('支付成功');
        } else {
            if ($pay_type == 1) {
                $result = Pay::pay($pay_type, $pay_amount, $ordersn, '挂号费', 'health');
                return $this->success('唤醒微信', $result);
            } else {
                $user = $request->user();
                if ($user->money < $pay_amount) {
                    return $this->fail('余额不足');
                }
                $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'health']);
                $res = (new NotifyController())->balance($request);;
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    return $this->fail($res->msg);
                }
                User::changeMoney(-$pay_amount, $user->id, '处方费用');
                return $this->success('支付成功');
            }
        }
    }

}
