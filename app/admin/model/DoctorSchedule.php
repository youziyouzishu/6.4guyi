<?php

namespace app\admin\model;

use Illuminate\Support\Carbon;
use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $doctor_id 医生
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $status 状态:1=可预约,2=已预约,3=休息
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorSchedule query()
 * @property Carbon $date 日期
 * @property Carbon $start_time 上班时间
 * @property Carbon $end_time 结束时间
 * @property-read Doctor|null $doctor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorSchedule normal()
 * @mixin \Eloquent
 */
class DoctorSchedule extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_schedule';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'created_at',
        'updated_at',
    ];

    public function scopeNormal($query)
    {
        return $query->where('status', 1);
    }

    function doctor()
    {
        return $this->belongsTo(Doctor::class,'doctor_id','id');
    }
    
    
    
}
