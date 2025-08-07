<?php

namespace app\api\controller;

use app\admin\model\Advice;
use app\admin\model\AdviceClass;
use app\admin\model\Help;
use app\admin\model\User;
use app\admin\model\UserMoneyLog;
use app\admin\model\UserRecharge;
use app\api\basic\Base;
use app\api\service\Pay;
use Carbon\Carbon;
use support\Log;
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
            if ($key == 'sex') {
                $value = strval($value);
            }
            if (array_key_exists($key, $userAttributes) && (!empty($value) || $value == 0)) {
                $row->setAttribute($key, $value);
            }
        }
        $row->save();
        return $this->success('成功');
    }

    /**
     * 获取手机号
     * @param Request $request
     * @return \support\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
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

        return $this->success('成功', [
            'mobile' => $mobile
        ]);
    }

    /**
     * 获取邀请海报
     * @param Request $request
     * @return \support\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    function getPoster(Request $request)
    {
        $user = User::find($request->user_id);

        $config = config('wechat.UserMiniApp');
        $app = new \EasyWeChat\MiniApp\Application($config);
        $data = [
            'scene' => strval($user->invitecode),
            'page' => 'pages/home',
            'width' => 280,
            'check_path' => !config('app.debug'),
        ];
        $response = $app->getClient()->postJson('/wxa/getwxacodeunlimit', $data);
        $base64 = "data:image/png;base64," . base64_encode($response->getContent());

        return $this->success('获取成功', [
            'base64' => $base64,
            'invitecode' => $user->invitecode,
        ]);
    }

    /**
     * 我的团队
     * @param Request $request
     * @return \support\Response
     */
    function getTeamList(Request $request)
    {
        $rows = User::where('pid', $request->user_id)->paginate()->items();
        return $this->success('获取成功', $rows);
    }

    /**
     * 帮助列表
     * @param Request $request
     * @return \support\Response
     */
    function getHelpList(Request $request)
    {
        $rows = Help::all();
        return $this->success('获取成功', $rows);
    }

    /**
     * 获取意见反馈分类列表
     * @param Request $request
     * @return \support\Response
     */
    function getAdviceClassList(Request $request)
    {
        $rows = AdviceClass::all();
        return $this->success('获取成功', $rows);
    }

    /**
     * 提交意见反馈
     * @param Request $request
     * @return \support\Response
     */
    function postAdvice(Request $request)
    {
        $class_name = $request->post('class_name');
        $content = $request->post('content');
        $images = $request->post('images');
        $mobile = $request->post('mobile');
        $truename = $request->post('truename');
        $advice = Advice::create([
            'user_id' => $request->user_id,
            'class_name' => $class_name,
            'content' => $content,
            'images' => $images,
            'mobile' => $mobile,
            'truename' => $truename,
        ]);
        return $this->success('成功', $advice);
    }

    /**
     * 充值余额
     * @param Request $request
     * @return \support\Response
     */
    function recharge(Request $request)
    {
        $amount = $request->post('amount');
        $pay_type = $request->post('pay_type');# 1微信
        if ($pay_type == 1) {
            try {
                $ordersn = Pay::generateOrderSn();
                UserRecharge::create([
                    'user_id' => $request->user_id,
                    'amount' => $amount,
                    'ordersn' => $ordersn,
                    'pay_type' => $pay_type,
                ]);
                $result = Pay::pay($pay_type, $amount, $ordersn, '余额充值', 'recharge');
            } catch (\Throwable $e) {
                Log::error('支付失败');
                Log::error($e->getMessage());
                return $this->fail('支付失败');
            }
        } else {
            return $this->fail('支付类型错误');
        }
        return $this->success('成功', $result);
    }

    /**
     * 获取帐变记录
     * @param Request $request
     * @return \support\Response
     */
    function getMoneyLog(Request $request)
    {
        $date = $request->input('date');
        $status = $request->input('status'); #0=全部 1=支出，2=收入
        $date = Carbon::parse($date);
        // 提取年份和月份
        $year = $date->year;
        $month = $date->month;
        $rows = UserMoneyLog::where('user_id', $request->user_id)
            ->when(!empty($status), function ($query) use ($status) {
                if ($status == 1) {
                    $query->where('money', '<', 0);
                } else {
                    $query->where('money', '>', 0);
                }
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->get()
            ->each(function ($item) {
                if ($item->money > 0) {
                    $item->money = '+' . $item->money;
                }
            });
        return $this->success('获取成功', $rows);
    }


}
