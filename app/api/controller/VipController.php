<?php

namespace app\api\controller;

use app\api\basic\Base;
use plugin\admin\app\model\User;
use support\Request;

class VipController extends Base
{

    function getVipList(Request $request)
    {
        $user = User::find($request->user_id);

    }

}
