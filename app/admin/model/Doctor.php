<?php

namespace app\admin\model;

use Illuminate\Database\Eloquent\SoftDeletes;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $class_first_id 一级分类
 * @property integer $class_sec_id 二级分类
 * @property string $avatar 头像
 * @property string $name 名称
 * @property string $tags 标签
 * @property string $level 职称
 * @property string $skilled 擅长
 * @property string $resume 个人简介
 * @property integer $status 状态:1=正常,2=隐藏
 * @property integer $weigh 权重
 * @property integer $sales 销量
 * @property float $assess_rate 好评率
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor normal()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor query()
 * @property int $admin_id 后台
 * @property int $vip_level 限制vip
 * @property string $price 单次诊费
 * @property-read \app\admin\model\DoctorClass|null $classFirst
 * @property-read \app\admin\model\DoctorClass|null $classSecond
 * @property-read \app\admin\model\Vip|null $vip
 * @property string|null $deleted_at 删除时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Doctor withoutTrashed()
 * @mixin \Eloquent
 */
class Doctor extends Base
{

    use SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor';

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

    function classFirst()
    {
        return $this->belongsTo(DoctorClass::class, 'class_first_id', 'id');
    }

    function classSecond()
    {
        return $this->belongsTo(DoctorClass::class, 'class_sec_id', 'id');
    }

    function vip()
    {
        return $this->belongsTo(Vip::class, 'vip_level', 'id');
    }
    
    
    
}
