<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\DoctorClass;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 医师分类 
 */
class DoctorClassController extends Crud
{
    
    /**
     * @var DoctorClass
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new DoctorClass;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('doctor-class/index');
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
        return view('doctor-class/insert');
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
        return view('doctor-class/update');
    }

}
