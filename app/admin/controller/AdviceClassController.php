<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\admin\model\AdviceClass;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 意见反馈分类 
 */
class AdviceClassController extends Crud
{
    
    /**
     * @var AdviceClass
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new AdviceClass;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('advice-class/index');
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
        return view('advice-class/insert');
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
        return view('advice-class/update');
    }

}
