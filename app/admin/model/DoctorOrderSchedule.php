<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $order_id 订单
 * @property int $schedule_id 预约时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderSchedule query()
 * @property-read \app\admin\model\DoctorOrder|null $order
 * @property-read \app\admin\model\DoctorSchedule|null $schedule
 * @mixin \Eloquent
 */
class DoctorOrderSchedule extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_order_schedule';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'schedule_id',
        'created_at',
        'updated_at',
    ];


    function order()
    {
        return $this->belongsTo(DoctorOrder::class, 'order_id', 'id');
    }

    function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id', 'id');
    }
    
    
}
