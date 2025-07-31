<?php

namespace app\api\controller;

use app\admin\model\User;
use app\admin\model\VipLog;
use app\api\basic\Base;
use EasyWeChat\MiniApp\Application;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AccountController extends Base
{

    protected array $noNeedLogin = ['login', 'register', 'changePassword', 'refreshToken'];

    function login(Request $request)
    {
        $code = $request->post('code');
        $invitecode = $request->post('invitecode');
        $config = config('wechat.UserMiniApp');
        $app = new Application($config);
        $ret = $app->getUtils()->codeToSession($code);
        $openid = $ret['openid'];

        $user = User::where('openid', $openid)->first();
        if (!$user) {
            if (!empty($invitecode)){
                $invite_user = User::where('invitecode', $invitecode)->first();
            }

            $user = User::create([
                'pid' => isset($invite_user) ? $invite_user->id : null,
                'invitecode'=>User::generateInvitecode(),
                'openid' => $openid,
            ])->refresh();

            #注册赠送vip
            VipLog::create([
                'user_id' => $user->id,
                'vip_level' => 1,
                'type' => 1
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
