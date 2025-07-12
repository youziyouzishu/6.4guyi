<?php

namespace app\queue\redis;

use app\admin\model\DoctorOrder;
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

        if ($event == 'order_expire') {
            $id = $data['id'];
            $order = DoctorOrder::find($id);
            if ($order && $order->status == 0) {
                $order->status = 5;
                $order->save();
            }
        }
    }

}
