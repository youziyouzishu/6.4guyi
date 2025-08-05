<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $service_id 项目
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceSchedule query()
 * @property int $num 余量
 * @property \Illuminate\Support\Carbon $date 日期
 * @property \Illuminate\Support\Carbon $start_time 上班时间
 * @property \Illuminate\Support\Carbon $end_time 结束时间
 * @property int $status 状态:1=可预约,2=已预约,3=休息
 * @mixin \Eloquent
 */
class ServiceSchedule extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_service_schedule';

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
        'id',
        'service_id',
        'date',
        'start_time',
        'end_time',
        'num',
        'status',
        'created_at',
        'updated_at',
    ];






}
