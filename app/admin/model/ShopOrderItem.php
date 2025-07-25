<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $order_id 订单
 * @property int $goods_id 商品
 * @property int $sku_id 规格
 * @property string $pay_amount 支付金额
 * @property string $goods_amount 商品金额
 * @property string $freight 运费
 * @property int $num 数量
 * @property int $refunded_quantity 已退数量
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderItem query()
 * @property string $price 单价
 * @property-read \app\admin\model\ShopGoods|null $goods
 * @property int $status 状态:0=无,1=申请售后,2=通过,3=拒绝,4=待评价,5=评价完成
 * @mixin \Eloquent
 */
class ShopOrderItem extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_order_item';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'goods_id',
        'sku_id',
        'pay_amount',
        'goods_amount',
        'freight',
        'num',
        'status',
        'refunded_quantity',
        'price',
    ];

    function goods()
    {
        return $this->belongsTo(ShopGoods::class, 'goods_id', 'id');
    }
    
    
}
