<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $medicine_id 中药
 * @property integer $weight_id 克重
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property int $record_id 档案
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderRecordMedicine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderRecordMedicine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderRecordMedicine query()
 * @property string $price 克价
 * @mixin \Eloquent
 */
class DoctorOrderRecordMedicine extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_order_record_medicine';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'medicine_id',
        'weight_id',
        'created_at',
        'updated_at',
        'record_id',
        'price',
    ];
    
    
    
}
