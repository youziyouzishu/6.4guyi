<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $goods_id 关联商品
 * @property string $image 图片
 * @property integer $weight 权重
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopBanner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopBanner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopBanner query()
 * @mixin \Eloquent
 */
class ShopBanner extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_banner';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
