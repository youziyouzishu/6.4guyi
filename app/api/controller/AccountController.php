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




    function register(Request $request)
    {
        $truename = $request->post('truename');
        $idcard = $request->post('idcard');
        $trade_password = $request->post('trade_password');
        $confirm_trade_password = $request->post('confirm_trade_password');
        $code = $request->post('code');
        $eid_token = $request->post('eid_token');

        if ($trade_password != $confirm_trade_password) {
            return $this->fail('两次交易密码不一致');
        }
        if (strlen($trade_password) != 6) {
            return $this->fail('交易密码长度必须是6位');
        }

        $exists = User::where('idcard', $idcard)->first();
        if ($exists) {
            return $this->fail('用户已存在');
        }

        $cred = new Credential('AKIDoVGvRlurcAqTXSBj5FDzZyEKH6kCVijY', 'gTF043sX1JPKl6NZaP2a1JXo5OdhbKrC');
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('faceid.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new FaceidClient($cred, '', $clientProfile);
        $req = new GetEidResultRequest();
        $params = ['EidToken' => $eid_token];
        $req->fromJsonString(json_encode($params));
        $resp = $client->GetEidResult($req);
        $ErrCode = $resp->Text->ErrCode;
        if($ErrCode !== 0){
            return $this->fail('人脸核身失败');
        }

        $privateKey = '86568be9cd782b9434d744d45ff3acb94532d386e734bf0f449cec8b8160b8f1';
        $desKey = $resp->EidInfo->DesKey;
        $userInfo = $resp->EidInfo->UserInfo;
        $sm2 = new RtSm2();
        $key = $sm2->doDecrypt(bin2hex(base64_decode($desKey)), $privateKey, $trim = true, $model = C1C3C2);
        $sm4 = new RtSm4($key);
        $userInfo = $sm4->decrypt(bin2hex(base64_decode($userInfo)), 'sm4-ecb', '', 'hex');
        $userInfo = json_decode($userInfo);
        if ($userInfo->name !== $truename || $userInfo->idnum !== $idcard){
            return $this->fail('人脸核身信息不匹配');
        }


        $config = config('wechat.UserMiniApp');
        $app = new Application($config);
        $ret = $app->getUtils()->codeToSession($code);
        $openid = $ret['openid'];

        $user = User::create([
            'nickname' => '用户' . mt_rand(100000, 999999),
            'avatar' => '/app/admin/avatar.png',
            'join_time' => Carbon::now(),
            'join_ip' => $request->getRealIp(),
            'last_time' => Carbon::now(),
            'last_ip' => $request->getRealIp(),
            'trade_password' => Util::passwordHash($trade_password),
            'truename' => $truename,
            'idcard' => $idcard,
            'openid' => $openid,
        ]);

        $token = JwtToken::generateToken([
            'id' => $user->id,
            'client' => JwtToken::TOKEN_CLIENT_MOBILE,
            'openid' => $user->openid,
        ]);
        return $this->success('注册成功', ['user' => $user, 'token' => $token]);
    }

    #更改密码
    function changePassword(Request $request)
    {
        $truename = $request->post('truename');
        $idcard = $request->post('idcard');
        $trade_password = $request->post('trade_password');
        $confirm_trade_password = $request->post('confirm_trade_password');
        $eid_token = $request->post('eid_token');
        if ($trade_password != $confirm_trade_password) {
            return $this->fail('两次交易密码不一致');
        }
        if (strlen($trade_password) != 6) {
            return $this->fail('交易密码长度必须是6位');
        }

        $user = User::where('idcard', $idcard)->where('truename', $truename)->first();
        if (!$user) {
            return $this->fail('用户不存在');
        }

        $cred = new Credential('AKIDoVGvRlurcAqTXSBj5FDzZyEKH6kCVijY', 'gTF043sX1JPKl6NZaP2a1JXo5OdhbKrC');
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('faceid.tencentcloudapi.com');
        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $client = new FaceidClient($cred, '', $clientProfile);
        $req = new GetEidResultRequest();
        $params = ['EidToken' => $eid_token];
        $req->fromJsonString(json_encode($params));
        $resp = $client->GetEidResult($req);
        $ErrCode = $resp->Text->ErrCode;
        if($ErrCode !== 0){
            return $this->fail('人脸核身失败');
        }

        $privateKey = '86568be9cd782b9434d744d45ff3acb94532d386e734bf0f449cec8b8160b8f1';
        $desKey = $resp->EidInfo->DesKey;
        $userInfo = $resp->EidInfo->UserInfo;
        $sm2 = new RtSm2();
        $key = $sm2->doDecrypt(bin2hex(base64_decode($desKey)), $privateKey, $trim = true, $model = C1C3C2);
        $sm4 = new RtSm4($key);
        $userInfo = $sm4->decrypt(bin2hex(base64_decode($userInfo)), 'sm4-ecb', '', 'hex');
        $userInfo = json_decode($userInfo);
        if ($userInfo->name !== $truename || $userInfo->idnum !== $idcard){
            return $this->fail('人脸核身信息不匹配');
        }

        $user->trade_password = Util::passwordHash($trade_password);
        $user->save();
        return $this->success('修改成功');
    }


    function refreshToken(Request $request)
    {
        $res = JwtToken::refreshToken();
        return $this->success('刷新成功', $res);
    }

}
