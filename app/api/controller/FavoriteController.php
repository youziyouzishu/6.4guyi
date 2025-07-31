<?php

namespace app\api\controller;

use app\admin\model\Doctor;
use app\admin\model\Service;
use app\admin\model\ShopGoods;
use app\api\basic\Base;
use support\Request;

class FavoriteController extends Base
{

    /**
     * 收藏
     * @param Request $request
     * @return \support\Response
     */
    function doFavorite(Request $request)
    {
        $id = $request->input('id');
        $type = (int)$request->input('type'); // 1: 医师, 2: 项目, 3: 商品
        $user = $request->user();
        $map = [
            1 => Doctor::class,
            2 => Service::class,
            3 => ShopGoods::class,
        ];
        $modelClass = $map[$type] ?? null;
        if (!$modelClass) {
            return $this->fail('类型错误');
        }

        $exists = $user->favorites()
            ->where('favoritable_id', $id)
            ->where('favoritable_type', $modelClass)
            ->exists();

        if ($exists) {
            // 已收藏 -> 取消收藏
            $user->favorites()
                ->where('favoritable_id', $id)
                ->where('favoritable_type', $type)
                ->delete();
            $result = false;
        } else {
            // 未收藏 -> 添加收藏
            $user->favorites()->create([
                'favoritable_id' => $id,
                'favoritable_type' => $type
            ]);
            $result = true;
        }
        return $this->success('成功',$result);
    }

    /**
     * 获取收藏列表
     * @param Request $request
     * @return \support\Response
     */
    function getFavoriteList(Request $request)
    {
        $type = (int)$request->input('type'); // 1: 医师, 2: 项目, 3: 商品
        $user = $request->user();

        $map = [
            1 => Doctor::class,
            2 => Service::class,
            3 => ShopGoods::class,
        ];
        $modelClass = $map[$type] ?? null;
        if (!$modelClass) {
            return $this->fail('类型错误');
        }

        $favorites = $user->favorites()
            ->where('favoritable_type', $modelClass)
            ->with('favoritable')
            ->orderByDesc('id')
            ->paginate();
        // 只返回实际收藏的对象（比如商品/服务/医师）
        $items = $favorites->map(function ($fav) {
            return $fav->favoritable;
        });

        return $this->success('获取成功', $items);
    }

}
