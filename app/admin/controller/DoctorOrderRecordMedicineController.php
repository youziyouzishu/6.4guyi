<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\DoctorOrderRecordMedicine;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 处方中药 
 */
class DoctorOrderRecordMedicineController extends Crud
{
    
    /**
     * @var DoctorOrderRecordMedicine
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DoctorOrderRecordMedicine;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('doctor-order-record-medicine/index');
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
        return view('doctor-order-record-medicine/insert');
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
        return view('doctor-order-record-medicine/update');
    }

}
