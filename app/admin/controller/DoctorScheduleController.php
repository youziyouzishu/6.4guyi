<?php

namespace app\admin\controller;

use app\admin\model\DoctorSchedule;
use Carbon\Carbon;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 排班管理
 */
class DoctorScheduleController extends Crud
{

    /**
     * @var DoctorSchedule
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DoctorSchedule;
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order)->with(['doctor']);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('doctor-schedule/index');
    }

    /**
     * 新增排班
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
            $doctor_id = $request->post('doctor_id');
            $date = Carbon::parse($date);
            $start_time = $date->copy()->setTimeFromTimeString($start_time);
            $end_time = $date->copy()->setTimeFromTimeString($end_time);

            if ($date < Carbon::today()){
                return $this->fail('只能排今天及以后的时间');
            }
            if ($start_time >= $end_time) {
                return $this->fail('开始时间不能大于结束时间');
            }
            $exist = DoctorSchedule::where('doctor_id', $doctor_id)
                ->where('date', $date)
                ->exists();
            if ($exist) {
                return $this->fail('不能重复日期排班');
            }


            $slots = collect();
            while ($start_time <= $end_time) {
                $slots->push($start_time->toDateTimeString());
                $start_time->addMinutes((int)$slot_duration);
            }
            for ($i = 0; $i < $slots->count() - 1; $i++) {
                $data = [
                    'doctor_id' => $doctor_id,
                    'date' => $date->toDateString(),
                    'start_time' => $slots[$i],
                    'end_time' => $slots[$i + 1],
                    'status' => 1,
                ];
                DoctorSchedule::create($data);
            }
            return $this->success();
        }
        return view('doctor-schedule/insert');
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
        return view('doctor-schedule/update');
    }

}
