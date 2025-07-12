<?php

namespace app\api\controller;

use app\admin\model\User;
use app\api\basic\Base;
use Carbon\Carbon;
use EasyWeChat\MiniApp\Application;
use plugin\admin\app\common\Util;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{

    protected array $noNeedLogin = ['login', 'register', 'changePassword', 'refreshToken'];

    function login(Request $request)
    {
        $code = $request->post('code');
        $config = config('wechat.UserMiniApp');
        $app = new Application($config);
        $ret = $app->getUtils()->codeToSession($code);
        $openid = $ret['openid'];

        $user = User::where('openid', $openid)->first();
        if (!$user) {
            $user = User::create([
                'openid' => $openid,
            ]);
        }
        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE,
            'openid' => $user->openid,
        ]);
        return $this->success('成功',[
            'token' => $token,
            'user' => $user,
        ]);

    }


    function refreshToken(Request $request)
    {
        $res = JwtToken::refreshToken();
        return $this->success('刷新成功', $res);
    }

}
