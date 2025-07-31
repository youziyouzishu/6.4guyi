<?php

namespace app\admin\controller;

use app\admin\model\UserRecharge;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 充值记录 
 */
class UserRechargeController extends Crud
{
    
    /**
     * @var UserRecharge
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new UserRecharge;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('user-recharge/index');
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
        return view('user-recharge/insert');
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
        return view('user-recharge/update');
    }

}
