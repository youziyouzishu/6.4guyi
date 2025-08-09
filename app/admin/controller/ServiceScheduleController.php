<?php

namespace app\admin\controller;

use Carbon\Carbon;
use support\Request;
use support\Response;
use app\admin\model\ServiceSchedule;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 理疗排班 
 */
class ServiceScheduleController extends Crud
{
    
    /**
     * @var ServiceSchedule
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new ServiceSchedule;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('service-schedule/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $date = $request->post('date');
            $start_time = $request->post('start_time');
            $end_time = $request->post('end_time');
            $slot_duration = $request->post('slot_duration');
            $rest_duration = $request->post('rest_duration');
            $service_id = $request->post('service_id');
            $num = $request->post('num');
            $date = Carbon::parse($date);
            $start_time = $date->copy()->setTimeFromTimeString($start_time);
            $end_time = $date->copy()->setTimeFromTimeString($end_time);

            if ($date < Carbon::today()){
                return $this->fail('只能排今天及以后的时间');
            }
            if ($start_time >= $end_time) {
                return $this->fail('开始时间不能大于结束时间');
            }
            $exist = ServiceSchedule::where('service_id', $service_id)
                ->where('date', $date)
                ->exists();
            if ($exist) {
                return $this->fail('不能重复日期排班');
            }


            // 主循环排班 + 休息
            $slots = [];

            $current = $start_time->copy();
            while (true) {
                $slot_start = $current->copy();
                $slot_end = $current->copy()->addMinutes((int)$slot_duration);

                if ($slot_end > $end_time) {
                    break;
                }

                $slots[] = [
                    'start_time' => $slot_start->toDateTimeString(),
                    'end_time' => $slot_end->toDateTimeString(),
                ];

                // 下一个 slot 开始时间：工作结束后再加上休息时间
                $current = $slot_end->copy()->addMinutes((int)$rest_duration);
            }

            // 批量插入
            foreach ($slots as $slot) {
                ServiceSchedule::create([
                    'service_id' => $service_id,
                    'date' => $date->toDateString(),
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'num' => $num,
                    'status' => 1,
                ]);
            }

            return $this->success();
        }
        return view('service-schedule/insert');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('service-schedule/update');
    }

}
