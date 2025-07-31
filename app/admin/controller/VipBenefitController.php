<?php

namespace app\admin\controller;

use app\admin\model\VipBenefit;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * VIP权益配置 
 */
class VipBenefitController extends Crud
{
    
    /**
     * @var VipBenefit
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new VipBenefit;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('vip-benefit/index');
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
        return view('vip-benefit/insert');
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
        return view('vip-benefit/update');
    }

}
