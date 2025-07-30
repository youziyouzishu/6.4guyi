<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $ordersn 订单编号
 * @property int $vip_id vip
 * @property string $pay_amount 支付金额
 * @property int $status 状态:0=未支付,1=已支付
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $pay_type 支付方式:0=无,1=微信,2=余额
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipOrder query()
 * @mixin \Eloquent
 */
class VipOrder extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_vip_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'vip_id',
        'user_id',
        'status',
        'pay_type',
        'pay_amount',
        'ordersn',
        'created_at',
        'updated_at',
    ];


    
    
}
