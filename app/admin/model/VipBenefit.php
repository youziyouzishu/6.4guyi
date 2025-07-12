<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $vip_id VIP
 * @property string $name 名称
 * @property string $icon 图标
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipBenefit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipBenefit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VipBenefit query()
 * @mixin \Eloquent
 */
class VipBenefit extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_vip_benefit';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
