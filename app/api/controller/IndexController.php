<?php

namespace app\api\controller;

use app\admin\model\UserMoneyLog;
use app\api\basic\Base;
use Carbon\Carbon;
use Illuminate\Support\Str;
use support\Request;
use Webman\RedisQueue\Client;

class IndexController extends Base
{

    protected array $noNeedLogin = ['*'];

    public function index(Request $request)
    {
        Client::send('job', ['id' =>1, 'event' => 'goods_order_expire']);
        Client::send('job', ['id' =>2, 'event' => 'goods_order_expire']);
        return $this->success();
    }

}
