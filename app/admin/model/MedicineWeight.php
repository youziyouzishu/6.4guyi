<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $weight 克重
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineWeight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineWeight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MedicineWeight query()
 * @mixin \Eloquent
 */
class MedicineWeight extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_medicine_weight';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
