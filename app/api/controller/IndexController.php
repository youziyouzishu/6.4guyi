<?php

namespace app\api\controller;

use app\admin\model\Doctor;
use app\admin\model\DoctorOrder;
use app\admin\model\DoctorSchedule;
use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use Illuminate\Support\Str;
use support\Request;

class IndexController extends Base
{

    protected array $noNeedLogin = ['*'];

    public function index(Request $request)
    {
//        $order = DoctorOrder::where(['ordersn' => '20250725688365075417F', 'status' => 0])->first();
//        dump($order->scheduleItem->first()->schedule_id);
        dump(Str::random(8));
    }

}
