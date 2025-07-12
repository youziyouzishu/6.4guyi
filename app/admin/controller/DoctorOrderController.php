<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\Doctor;
use app\admin\model\DoctorOrderSchedule;
use support\Request;
use support\Response;
use app\admin\model\DoctorOrder;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 医师订单
 */
class DoctorOrderController extends Crud
{

    /**
     * @var DoctorOrder
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DoctorOrder;
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
        $query = $this->doSelect($where, $field, $order)->with(['schedule','record']);
        if (in_array(3, admin('roles'))) {
            $doctor = Doctor::where('admin_id',admin_id())->first();
            $query->where('doctor_id', $doctor->id);
        }
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('doctor-order/index');
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
            return parent::insert($request);
        }
        return view('doctor-order/insert');
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
            $id = $request->input('id');
            $status = $request->input('status');
            $order_schedule_id = $request->input('order_schedule_id');
            $order = DoctorOrder::find($id);
            if ($order && $order->status == 1 && $status == 2 && $order->schedule_id == null) {
                $order->scheduleItem->each(function (DoctorOrderSchedule $item) use ($order_schedule_id, $request) {
                    #释放其他多余的时间状态
                    if ($item->id != $order_schedule_id && $item->schedule->status == 2) {
                        $item->schedule->status = 1;
                        $item->schedule->save();
                    }
                    if ($item->id == $order_schedule_id) {
                        $request->setParams('post', ['schedule_id' => $item->schedule_id]);
                    }
                });
            }
            return parent::update($request);
        }
        return view('doctor-order/update');
    }

}
