<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use support\Request;
use Tinywan\Jwt\Exception\JwtRefreshTokenExpiredException;

class UserController extends Base
{
    protected array $noNeedLogin = ['getMobile'];
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

    function getMobile(Request $request)
    {
        $code = $request->post('code');
        //小程序
        $config = config('wechat.UserMiniApp');
        $app = new \EasyWeChat\MiniApp\Application($config);
        $api = $app->getClient();
        $ret = $api->postJson('/wxa/business/getuserphonenumber', [
            'code' => $code
        ]);
        $ret = json_decode($ret);
        if ($ret->errcode != 0) {
            return $this->fail('获取手机号失败');
        }
        $mobile = $ret->phone_info->phoneNumber;

        return $this->success('成功',[
            'mobile' => $mobile
        ]);
    }

}
