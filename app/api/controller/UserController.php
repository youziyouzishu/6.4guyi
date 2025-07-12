<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use support\Request;
use Tinywan\Jwt\Exception\JwtRefreshTokenExpiredException;

class UserController extends Base
{

    function getUserInfo(Request $request)
    {
        $user_id = $request->post('user_id');
        if (!empty($user_id)) {
            $request->user_id = $user_id;
        }
        $row = User::find($request->user_id);
        if (empty($row)) {
            throw new JwtRefreshTokenExpiredException();
        }
        return $this->success('成功', $row);
    }

    function editUserInfo(Request $request)
    {
        $data = $request->post();
        $row = User::find($request->user_id);
        if (!$row) {
            return $this->fail('用户不存在');
        }

        $userAttributes = $row->getAttributes();
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $userAttributes) && (!empty($value) || $value === 0)) {
                $row->setAttribute($key, $value);
            }
        }
        $row->save();
        return $this->success('成功');
    }

}
