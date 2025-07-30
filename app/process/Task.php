<?php
namespace app\process;

use app\admin\model\User;
use app\admin\model\VipLog;
use Carbon\Carbon;
use Workerman\Crontab\Crontab;

class Task
{
    public function onWorkerStart()
    {
        // 每10分钟执行一次
        new Crontab('0 */10 * * * *', function(){
            $users = User::all();
            foreach ($users as $user) {
                if ($vipinfo = $user->lastVipLog){
                    if ($vipinfo->type == 4 && $vipinfo->vip_expired_at->isPast()){
                        //如果是充值会员，且到期时间已过
                        $user->vipLog()->delete();#删除所有会员记录(删不删除都无所谓)
                        #重新退回到1级
                        VipLog::create([
                            'user_id' => $user->id,
                            'vip_level' => 1,
                            'type' => 1,
                        ]);
                    }
                    if ($vipinfo->type == 3 && $user->last_consume_at < Carbon::now()->subMonths(3)){
                        //如果是消费福利 超过最后消费三个月  降级
                        $vipinfo->delete();
                    }
                    if ($vipinfo->type == 2 && $user->last_consume_at < Carbon::now()->subMonths(3) && $user->money > 0){
                        //如果是消费福利 超过最后消费三个月 切账户有余额  降级
                        $vipinfo->delete();
                    }
                }
            }
        });

    }
}