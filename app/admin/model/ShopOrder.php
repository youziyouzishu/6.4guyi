<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property string $ordersn 订单编号
 * @property int $status 状态:0=待支付,1=已支付,2=交易关闭,3=待收货,4=待评价,5=已完成
 * @property string $total_pay_amount 支付金额
 * @property int $pay_type 支付方式:1=微信,2=余额
 * @property string $total_goods_amount 商品金额
 * @property string $total_freight 运费
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrder query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\ShopOrderItem> $items
 * @property string|null $mark 备注
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @property string|null $deleted_at 删除时间
 * @property int $pay_status 支付状态:0=未付款,1=已付款,2=已退款
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrder withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrder withoutTrashed()
 * @mixin \Eloquent
 */
class ShopOrder extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    protected $fillable = [
        'user_id',
        'ordersn',
        'status',
        'total_pay_amount',
        'pay_type',
        'total_goods_amount',
        'total_freight',
        'mark'
    ];
    protected $casts = [
        'pay_time' => 'datetime',
    ];

    function items()
    {
        return $this->hasMany(ShopOrderItem::class, 'order_id', 'id');
    }
    
    
    
}
