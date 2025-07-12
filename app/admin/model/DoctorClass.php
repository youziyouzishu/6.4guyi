<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $pid 上级
 * @property string $name 名称
 * @property string $icon 图标
 * @property integer $weigh 权重
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorClass newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorClass newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorClass query()
 * @property string|null $deleted_at 删除时间
 * @mixin \Eloquent
 */
class DoctorClass extends Base
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_class';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
}
