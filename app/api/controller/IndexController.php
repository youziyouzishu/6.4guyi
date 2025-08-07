<?php

namespace app\api\controller;

use app\admin\model\UserMoneyLog;
use app\api\basic\Base;
use Carbon\Carbon;
use Illuminate\Support\Str;
use support\Request;

class IndexController extends Base
{

    protected array $noNeedLogin = ['*'];

    public function index(Request $request)
    {
        $date = $request->input('date');
        $status = $request->input('status'); #0=全部 1=支出，2=收入
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $rows = UserMoneyLog::where('user_id', $request->user_id)
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('money', '<', 0);
                } else {
                    $query->where('money', '>', 0);
                }
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->get()
            ->each(function ($item) {
                if ($item->money > 0) {
                    $item->money = '+' . $item->money;
                }
            });
        return $this->success('获取成功', $rows);
    }

}
