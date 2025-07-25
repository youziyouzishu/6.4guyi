<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $class_id 商品分类
 * @property string $image 主图
 * @property string $images 轮播图
 * @property string $name 商品名称
 * @property string $discount_price 折扣价
 * @property string $price 原价
 * @property integer $sales 销量
 * @property string $description 商品详情
 * @property integer $status 状态（0=下架，1=上架）
 * @property integer $weight 权重
 * @property integer $is_recommend 是否推荐(0=不推荐，1=推荐)
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $freight 运费
 * @property-read \app\admin\model\ShopClass|null $class
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoods newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoods newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoods normal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopGoods query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\ShopGoodsSku> $sku
 * @mixin \Eloquent
 */
class ShopGoods extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_goods';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function scopeNormal($query)
    {
        return $query->where('status', 1);
    }


    function class()
    {
        return $this->belongsTo(ShopClass::class, 'class_id', 'id');
    }

    function sku()
    {
        return $this->hasMany(ShopGoodsSku::class, 'goods_id', 'id');
    }

    
    
    
}
