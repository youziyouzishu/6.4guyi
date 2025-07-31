<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $order_id 订单
 * @property int $goods_id 商品
 * @property int $item_id 子订单
 * @property int $score 评分（1-5）
 * @property string $images 图片
 * @property string $content 内容
 * @property int $anonymity 匿名:0=否,1=是
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderItemComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderItemComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopOrderItemComment query()
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class ShopOrderItemComment extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_order_item_comment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'order_id',
        'goods_id',
        'item_id',
        'score',
        'images',
        'content',
        'anonymity',
        'created_at',
        'updated_at',
    ];

    function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }


    
    
    
}
