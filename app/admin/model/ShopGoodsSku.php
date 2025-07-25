<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $goods_id 商品
 * @property string $name 名称
 * @property string $image 图片
 * @property integer $stock 库存
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoodsSku newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoodsSku newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoodsSku query()
 * @property-read \app\admin\model\ShopGoods|null $goods
 * @mixin \Eloquent
 */
class ShopGoodsSku extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_goods_sku';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    function goods()
    {
        return $this->belongsTo(ShopGoods::class, 'goods_id','id');
    }
    
    
    
}
