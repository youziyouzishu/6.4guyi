<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $sku_id 规格
 * @property int $num 数量
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShopCart query()
 * @property-read \app\admin\model\ShopGoodsSku|null $sku
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class ShopCart extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_shop_cart';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'sku_id',
        'num',
    ];

    function sku()
    {
        return $this->belongsTo(ShopGoodsSku::class, 'sku_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    
    
}
