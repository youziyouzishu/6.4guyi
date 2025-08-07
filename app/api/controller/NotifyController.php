<?php

namespace app\api\controller;

use app\admin\model\DoctorOrder;
use app\admin\model\DoctorOrderRecord;
use app\admin\model\ServiceOrder;
use app\admin\model\ShopOrder;
use app\admin\model\User;
use app\admin\model\UserRecharge;
use app\admin\model\VipLog;
use app\admin\model\VipOrder;
use app\api\basic\Base;
use Carbon\Carbon;
use support\Db;
use support\Log;
use support\Request;
use Yansongda\Pay\Pay;

class NotifyController extends Base
{

    protected array $noNeedLogin = ['*'];

    function alipay(Request $request)
    {
        $request->setParams('get', ['paytype' => 'alipay']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return response('success');
    }

    function wechat(Request $request)
    {
        $request->setParams('get', ['paytype' => 'wechat']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return json(['code' => 'FAIL', 'message' => $e->getMessage()]);
        }
        return json(['code' => 'SUCCESS', 'message' => '成功']);
    }

    function balance(Request $request)
    {
        $request->setParams('get', ['paytype' => 'balance']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success();
    }

    function transfer(Request $request)
    {
        $request->setParams('get', ['paytype' => 'transfer']);
        try {
            $this->pay($request);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
        return $this->success();
    }

    /**
     * 接受回调
     * @throws \Throwable
     */
    private function pay(Request $request)
    {
        Log::info('支付回调', $request->all());
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $paytype = $request->input('paytype');
            $config = config('payment');
            switch ($paytype) {
                case 'wechat':
                    $pay = Pay::wechat($config);
                    $res = $pay->callback($request->post());
                    $res = $res->resource;
                    $res = $res['ciphertext'];
                    $trade_state = $res['trade_state'];
                    if ($trade_state !== 'SUCCESS') {
                        throw new \Exception('支付失败');
                    }
                    $out_trade_no = $res['out_trade_no'];
                    $attach = $res['attach'];
                    $mchid = $res['mchid'];
                    $transaction_id = $res['transaction_id'];
                    $openid = $res['payer']['openid'] ?? '';


//                    $app = new Application(config('wechat'));
//                    $api = $app->getClient();
//                    $date = new DateTime(date('Y-m-d H:i:s'), new DateTimeZone('Asia/Shanghai'));
//                    $formatted_date = $date->format('c');
//                    $api->postJson('/wxa/sec/order/upload_shipping_info', [
//                        'order_key' => ['order_number_type' => 1, 'mchid' => $mchid, 'out_trade_no' => $out_trade_no],
//                        'logistics_type' => 3,
//                        'delivery_mode' => 1,
//                        'shipping_list' => [[
//                            'item_desc' => '发货'
//                        ]],
//                        'upload_time' => $formatted_date,
//                        'payer' => ['openid' => $openid]
//                    ]);
                    $paytype = 1;
                    break;
                case 'alipay':
                    $pay = Pay::alipay($config);
                    $res = $pay->callback($request->post());
                    $trade_status = $res->trade_status;
                    if ($trade_status !== 'TRADE_SUCCESS') {
                        throw new \Exception('支付失败');
                    }
                    $out_trade_no = $res->out_trade_no;
                    $attach = $res->passback_params;
                    $paytype = 3;
                    break;
                case 'balance':
                    $out_trade_no = $request->input('out_trade_no');
                    $attach = $request->input('attach');
                    $paytype = 2;
                    break;
                case 'transfer':
                    $pay = Pay::wechat($config);
                    $res = $pay->callback($request->post());
                    $res = $res->resource;
                    $res = $res['ciphertext'];
                    Log::info('转账回调', $res);
                    $trade_state = $res['state'];
                    if ($trade_state !== 'SUCCESS') {
                        throw new \Exception('支付失败');
                    }
                    $attach = 'transfer';
                    $out_trade_no = $res['out_bill_no'];
                    break;
                default:
                    throw new \Exception('支付类型错误');
            }

            switch ($attach) {
                case 'doctor':
                    $order = DoctorOrder::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    if ($order->user->vip_level >= 3) {
                        $order->status = 1;
                    } else {
                        $order->status = 2;
                    }
                    $order->pay_status = 1;
                    $order->pay_time = Carbon::now();
                    $order->pay_type = $paytype;
                    $order->save();
                    $order->doctor->sales += 1;
                    $order->doctor->save();
                    #增加累计消费
                    #消费进行升级
                    $this->updateVipLevel($order);
                    break;
                case 'health':
                    $order = DoctorOrderRecord::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 1;
                    $order->pay_time = Carbon::now();
                    $order->pay_type = $paytype;
                    $order->save();
                    #增加累计消费
                    #消费进行升级
                    $this->updateVipLevel($order);
                    break;
                case 'service':
                    $order = ServiceOrder::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 2;
                    $order->pay_status = 1;
                    $order->pay_time = Carbon::now();
                    $order->pay_type = $paytype;
                    $order->save();
                    #增加累计消费
                    #消费进行升级
                    $this->updateVipLevel($order);
                    break;
                case 'goods':
                    $order = ShopOrder::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 1;
                    $order->pay_time = Carbon::now();
                    $order->pay_type = $paytype;
                    $order->pay_status = 1;
                    $order->save();
                    break;
                case 'vip':
                    $order = VipOrder::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->status = 1;
                    $order->pay_type = $paytype;
                    $order->save();
                    // 会员升级
                    VipLog::create([
                        'user_id' => $order->user_id,
                        'vip_level' => $order->vip_id,
                        'type' => 4,
                        'vip_expired_at' => Carbon::now()->addYear(),
                    ]);
                    break;
                case 'recharge':
                    $order = UserRecharge::where(['ordersn' => $out_trade_no, 'status' => 0])->first();
                    if (!$order) {
                        throw new \Exception('订单不存在');
                    }
                    $order->pay_type = $paytype;
                    $order->status = 1;
                    $order->save();
                    #增加余额
                    User::changeMoney($order->amount, $order->user_id, '充值');
                    #升级会员
                    $amount = $order->amount;
                    $vip_level = $order->user->vip_level;
                    $vip = [
                        2 => 1000,
                        3 => 3000,
                        4 => 10000,
                        5 => 30000,
                    ];
                    $filteredVip = array_filter($vip, function ($threshold, $level) use ($amount, $vip_level) {
                        return $level > $vip_level && $threshold <= $amount;
                    }, ARRAY_FILTER_USE_BOTH);


                    if (!empty($filteredVip)) {
                        foreach ($filteredVip as $level => $threshold) {
                            VipLog::create([
                                'user_id' => $order->user_id,
                                'vip_level' => $level,
                                'type' => 2,
                            ]);
                        }
                    }
                    break;
                default:
                    throw new \Exception('回调错误');
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollBack();
            Log::error('支付回调失败');
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 消费进行升级
     * @param $order
     * @return void
     */
    protected function updateVipLevel($order)
    {
        $order->user->total_consume += $order->pay_amount;
        $order->user->last_consume_at = Carbon::now();
        $order->user->save();
        if ($order->pay_amount > 0) {
            //进行升级
            $current = $order->user->lastVipLog;
            if ($order->pay_type == 1){
                $balance = $order->user->money;
            }else{
                $balance = $order->user->moneyLog->first()->before;
            }
            #获取上一级
            $previous_info = VipLog::withTrashed()
                ->where('user_id', $order->user_id)
                ->orderByDesc('id')
                ->offset(1)
                ->limit(1)
                ->first();


            #如果有上一级并且上一级的level大于当前等级
            if ($previous_info && $current->vip_level < $previous_info->vip_level) {
                //判断是否可以恢复
                if ($previous_info->type == 2) { #充值福利
                    if ($previous_info->vip_level == 2) {
                        $with_amount = $balance * 0.1;
                    }
                    if ($previous_info->vip_level == 3) {
                        $with_amount = $balance * 0.05;
                    }
                    if ($previous_info->vip_level == 4) {
                        $with_amount = $balance * 0.02;
                    }
                    if ($previous_info->vip_level == 5) {
                        $with_amount = $balance * 0.01;
                    }

                }
                if ($previous_info->type == 3) {#消费福利
                    if ($previous_info->vip_level == 2) {
                        $with_amount = 10;
                    }
                    if ($previous_info->vip_level == 3) {
                        $with_amount = 50;
                    }
                    if ($previous_info->vip_level == 4) {
                        $with_amount = 100;
                    }
                    if ($previous_info->vip_level == 5) {
                        $with_amount = 200;
                    }
                }

                if ($order->pay_amount >= $with_amount){
                    //符合条件 升级会员
                    VipLog::create([
                        'user_id' => $order->user_id,
                        'vip_level' => $previous_info->vip_level,
                        'type' => $previous_info->type,
                    ]);
                }

            }
            $order->refresh()->load('user');
            $amount = $order->user->total_consume;
            $vip_level = $order->user->vip_level;
            $vip = [
                2 => 2000,
                3 => 6000,
                4 => 20000,
                5 => 60000,
            ];
            $filteredVip = array_filter($vip, function ($threshold, $level) use ($amount, $vip_level) {
                return $level > $vip_level && $threshold <= $amount;
            }, ARRAY_FILTER_USE_BOTH);


            if (!empty($filteredVip)) {
                foreach ($filteredVip as $level => $threshold) {
                    VipLog::create([
                        'user_id' => $order->user_id,
                        'vip_level' => $level,
                        'type' => 3,
                    ]);
                }
            }
        }
    }


}
