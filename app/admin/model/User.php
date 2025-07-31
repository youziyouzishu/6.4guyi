<?php

namespace app\admin\model;

use Illuminate\Support\Str;
use plugin\admin\app\model\Base;
use support\Db;


/**
 * 
 *
 * @property int $id 主键
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string $sex 性别
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property string|null $mobile 手机
 * @property int $level 等级
 * @property string|null $birthday 生日
 * @property string $money 余额(元)
 * @property int $score 积分
 * @property string|null $last_time 登录时间
 * @property string|null $last_ip 登录ip
 * @property string|null $join_time 注册时间
 * @property string|null $join_ip 注册ip
 * @property string|null $token token
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $role 角色
 * @property int $status 禁用
 * @property string|null $openid 微信标识
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @property string $invitecode 邀请码
 * @property int|null $pid 上级
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $children
 * @property-read User|null $parent
 * @property int $vip_log_id vip变更日志id
 * @property-read \app\admin\model\VipLog|null $lastVipLog
 * @property-read mixed $vip_level VIP等级
 * @property string $total_consume 累计消费
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\VipLog> $vipLog
 * @property \Illuminate\Support\Carbon|null $last_consume_at 最后消费时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserMoneyLog> $moneyLog
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\UserFavorite> $favorites
 * @mixin \Eloquent
 */
class User extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_users';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'last_consume_at' => 'datetime'
    ];

    protected $appends = [
        'vip_level'
    ];

    protected $fillable = [
        'id',
        'openid',
        'invitecode',
        'pid',
        'nickname',
        'created_at',
        'updated_at',
    ];

    public static function changeMoney($money, $user_id, $memo)
    {
        Db::connection('plugin.admin.mysql')->beginTransaction();
        try {
            $user = self::lockForUpdate()->find($user_id);
            if ($user && $money != 0) {
                $before = $user->money;
                $after = function_exists('bcadd') ? bcadd($user->money, $money, 2) : $user->money + $money;
                //更新会员信息
                $user->money = $after;
                $user->save();
                //写入日志
                UserMoneyLog::create(['user_id' => $user_id, 'money' => $money, 'before' => $before, 'after' => $after, 'memo' => $memo]);
            }
            Db::connection('plugin.admin.mysql')->commit();
        } catch (\Throwable $e) {
            Db::connection('plugin.admin.mysql')->rollback();
            throw $e;
        }
    }

    /**
     * 生成邀请码
     * @return string
     */
    public static function generateInvitecode()
    {
        do {
            $invitecode = Str::random(8);
        } while (self::where(['invitecode' => $invitecode])->exists());
        return $invitecode;
    }

    function parent()
    {
        return $this->belongsTo(self::class, 'pid', 'id');
    }

    function children()
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    function lastVipLog()
    {
        return $this->hasOne(VipLog::class, 'user_id', 'id')->orderByDesc('id');
    }

    function getVipLevelAttribute($value)
    {
        return $this->lastVipLog->vip_level;
    }

    function vipLog()
    {
        return $this->hasMany(VipLog::class, 'user_id', 'id')->orderByDesc('id');
    }

    function moneyLog()
    {
        return $this->hasMany(UserMoneyLog::class, 'user_id', 'id')->orderByDesc('id');
    }

    public function favorites()
    {
        return $this->hasMany(UserFavorite::class, 'user_id', 'id');
    }


    
}
