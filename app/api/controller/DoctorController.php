<?php

namespace app\api\controller;

use app\admin\model\Doctor;
use app\admin\model\DoctorClass;
use app\api\basic\Base;
use support\Request;

class DoctorController extends Base
{
    /**
     * 获取门店列表
     * @param Request $request
     * @return \support\Response
     */
    function getFirstClassList(Request $request)
    {
        $rows = DoctorClass::whereNull('pid')->orderByDesc('weigh')->get();
        return $this->success('成功', $rows);
    }

    /**
     * 获取科室列表
     * @param Request $request
     * @return \support\Response
     */
    function getSecClassList(Request $request)
    {
        $pid = $request->post('pid');
        $rows = DoctorClass::where('pid', $pid)->orderByDesc('weigh')->get();
        return $this->success('成功', $rows);
    }

    /**
     * 获取医生列表
     * @param Request $request
     * @return \support\Response
     */
    function getDoctorList(Request $request)
    {
        $class_first_id = $request->post('class_first_id');
        $class_sec_id = $request->post('class_sec_id');
        $rows = Doctor::normal()
            ->when(!empty($class_first_id), function ($query) use ($class_first_id) {
                $query->where('class_first_id', $class_first_id);
            })
            ->when(!empty($class_sec_id), function ($query) use ($class_sec_id) {
                $query->where('class_sec_id', $class_sec_id);
            })
            ->paginate()
            ->items();
        return $this->success('成功', $rows);
    }

}
