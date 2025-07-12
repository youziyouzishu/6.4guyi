<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id ID
 * @property string $username 用户名
 * @property string $nickname 昵称
 * @property string $password 密码
 * @property string|null $avatar 头像
 * @property string|null $email 邮箱
 * @property string|null $mobile 手机
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string|null $login_at 登录时间
 * @property int|null $status 禁用
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @mixin \Eloquent
 */
class Admin extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_admins';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'password',
        'nickname',
        'avatar',
        'status',
        'last_login_time',
        'last_login_ip',
        'created_at',
        'updated_at',
    ];
    
    
}
