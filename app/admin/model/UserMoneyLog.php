<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户
 * @property string $money 变更余额
 * @property string $before 变更前余额
 * @property string $after 变更后余额
 * @property string $memo 备注
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMoneyLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMoneyLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMoneyLog query()
 * @mixin \Eloquent
 */
class UserMoneyLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_money_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'money',
        'memo',
        'before',
        'after',
        'created_at',
        'updated_at',
    ];
    
    
    
}
