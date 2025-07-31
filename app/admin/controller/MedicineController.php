<?php

namespace app\admin\controller;

use app\admin\model\Medicine;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 中药管理 
 */
class MedicineController extends Crud
{
    
    /**
     * @var Medicine
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Medicine;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('medicine/index');
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
        return view('medicine/insert');
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
        return view('medicine/update');
    }

    /**
     * 变更克重
     * @param Request $request
     * @return Response
     */
    function changeWeight(Request $request)
    {
        $id = $request->input('id');
        $weight = $request->input('weight');
        $row = $this->model->find($id);
        if ($row) {
            $row->weight += $weight;
            $row->save();
            return $this->success();
        } else {
            return $this->fail();
        }
    }

}
