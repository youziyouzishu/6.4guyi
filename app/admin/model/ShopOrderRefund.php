<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $order_id 订单
 * @property int $item_id 子订单
 * @property int $user_id 用户
 * @property int $refund_type 类型:1=退货退款,2=换货
 * @property int $status 状态:0=申请中,1=审核中,2=成功,3=拒绝
 * @property string $reason 原因
 * @property string|null $images 凭证
 * @property string|null $content 描述
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderRefund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderRefund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderRefund query()
 * @mixin \Eloquent
 */
class ShopOrderRefund extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_order_refund';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'item_id',
        'user_id',
        'refund_type',
        'status',
        'reason',
        'images',
        'content',
        'created_at',
        'updated_at',
    ];


    
    
}
