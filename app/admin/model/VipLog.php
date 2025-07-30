<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $vip_level VIP等级
 * @property int $type 类型:1=初始会员,2=充值福利,3=消费福利,4=购买会员身份
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property string|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipLog withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipLog withoutTrashed()
 * @property \Illuminate\Support\Carbon|null $vip_expired_at 会员过期时间
 * @mixin \Eloquent
 */
class VipLog extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_vip_log';



    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'vip_expired_at' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'vip_level',
        'created_at',
        'updated_at',
    ];
    
    
    
}
