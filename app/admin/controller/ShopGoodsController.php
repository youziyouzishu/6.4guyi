<?php

namespace app\admin\controller;

use app\admin\model\ShopGoods;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 商品管理 
 */
class ShopGoodsController extends Crud
{
    
    /**
     * @var ShopGoods
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new ShopGoods;
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
        $query = $this->doSelect($where, $field, $order)->with(['class']);
        return $this->doFormat($query, $format, $limit);
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('shop-goods/index');
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
        return view('shop-goods/insert');
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
        return view('shop-goods/update');
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $this->doDelete($ids);
        return $this->json(0);
    }

    /**
     * 上架
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function up(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $primary_key = $this->model->getKeyName();
        $this->model->whereIn($primary_key, $ids)->each(function ($model) {
            $model->status = 1;
            $model->save();
        });
        return $this->json(0);
    }

    /**
     * 下架
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function down(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $primary_key = $this->model->getKeyName();
        $this->model->whereIn($primary_key, $ids)->each(function ($model) {
            $model->status = 0;
            $model->save();
        });
        return $this->json(0);
    }

}
