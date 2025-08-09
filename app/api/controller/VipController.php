<?php

namespace app\api\controller;

use app\admin\model\Vip;
use app\admin\model\VipOrder;
use app\api\basic\Base;
use app\api\service\Pay;
use plugin\admin\app\model\User;
use support\Request;

class VipController extends Base
{

    /**
     * 获取会员价格表
     * @param Request $request
     * @return \support\Response
     */
    function getVipList(Request $request)
    {
        $user = $request->user();
        $rows = Vip::with(['benefit'])->where('id', '>',$user->vip_level)->get();
        return $this->success('成功',$rows);
    }

    /**
     * 创建订单
     * @param Request $request
     * @return \support\Response
     */
    function createOrder(Request $request)
    {
        $id = $request->input('id');
        $vip = Vip::find($id);
        if (!$vip) {
            return $this->fail('会员不存在');
        }
        $user = $request->user();
        if ($user->vip_level >= $vip->id){
            return $this->fail('您等级比此会员高，请勿重复购买');
        }
        $ordersn = Pay::generateOrderSn();
        $order = VipOrder::create([
            'user_id' => $request->user()->id,
            'vip_id' => $id,
            'ordersn' => $ordersn,
            'pay_amount' => $vip->price
        ]);
        return $this->success('成功',$order);
    }

    /**
     * 支付
     * @param Request $request
     * @return \support\Response
     * @throws \Exception
     */
    function pay(Request $request)
    {
        $ordersn = $request->input('ordersn');
        $pay_type = $request->input('pay_type');#支付方式:1=微信,2=余额

        $order = VipOrder::where('ordersn', $ordersn)->first();
        if (!$order) {
            return $this->fail('订单不存在');
        }
        if ($order->status != 0) {
            return $this->fail('请刷新订单列表');
        }
        $pay_amount = $order->pay_amount;
        if ($pay_amount <= 0) {
            $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'vip']);
            $res = (new NotifyController())->balance($request);;
            $res = json_decode($res->rawBody());
            if ($res->code == 1) {
                return $this->fail($res->msg);
            }
            return $this->success('支付成功');
        } else {
            if ($pay_type == 1) {
                $result = Pay::pay($pay_type, $pay_amount, $ordersn, '挂号费', 'vip');
                return $this->success('唤醒微信', $result);
            } else {
                $user = $request->user();
                if ($user->money < $pay_amount) {
                    return $this->fail('余额不足');
                }
                $request->setParams('get', ['out_trade_no' => $ordersn, 'attach' => 'vip']);
                $res = (new NotifyController())->balance($request);;
                $res = json_decode($res->rawBody());
                if ($res->code == 1) {
                    return $this->fail($res->msg);
                }
                User::changeMoney(-$pay_amount, $user->id, '购买VIP服务');
                return $this->success('支付成功');
            }
        }
    }


    /**
     * vip信息
     * @param Request $request
     * @return \support\Response
     */
    function getMyVipInfo(Request $request)
    {
        $user = $request->user();
        $vipinfo = Vip::with(['benefit'])->find($user->vip_level);
        $rows = Vip::with(['benefit'])->where('id', '>',$user->vip_level)->get();
        return $this->success('成功',['vipinfo'=>$vipinfo,'vip_list'=>$rows]);
    }

}
