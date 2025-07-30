<?php

namespace app\queue\redis;

use app\admin\model\DoctorOrder;
use app\admin\model\ServiceOrder;
use app\admin\model\ShopOrder;
use Carbon\Carbon;
use Webman\RedisQueue\Consumer;

class Job implements Consumer
{
    // 要消费的队列名
    public $queue = 'job';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($data)
    {
        $event = $data['event'];

        if ($event == 'doctor_order_expire') {
            $id = $data['id'];
            $order = DoctorOrder::find($id);
            if ($order && $order->status == 0) {
                $order->status = 5;
                $order->save();
            }
        }
        if ($event == 'service_order_expire') {
            $id = $data['id'];
            $order = ServiceOrder::find($id);
            if ($order && $order->status == 0) {
                $order->status = 5;
                $order->save();
                //过期归还库存
                $order->schedule->num += 1;
                $order->schedule->save();
            }
        }
        if ($event == 'goods_order_expire') {
            $id = $data['id'];
            $order = ShopOrder::find($id);
            if ($order && $order->status == 0) {
                $order->status = 2;
                $order->save();
            }
        }
    }

}
