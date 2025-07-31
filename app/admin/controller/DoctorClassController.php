<?php

namespace app\admin\controller;

use app\admin\model\DoctorClass;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

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
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        $level = $request->input('level');
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        if ($level == 1){
            $query->whereNull('pid');
        }elseif ($level == 2){
            $query->whereNotNull('pid');
        }
        return $this->doFormat($query, $format, $limit);
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
