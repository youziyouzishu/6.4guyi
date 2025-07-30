<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $doctor_id 医师
 * @property int $user_id 用户
 * @property string $pay_amount 支付金额
 * @property string $price 单次价格
 * @property string $discount_amount 减免金额
 * @property int $pay_type 支付方式:0=无,1=微信,2=余额
 * @property int $status 订单状态:0=待付款,1=待确认,2=已预约,3=已完成,4=过号未诊,5=取消
 * @property int $pay_status 支付状态:0=未付款,1=已付款,2=已退款
 * @property int|null $schedule_id 预约时间
 * @property string $ordersn 订单编号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrder query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\DoctorOrderSchedule> $scheduleItem
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @property-read \app\admin\model\Doctor|null $doctor
 * @property-read \app\admin\model\User|null $user
 * @property-read \app\admin\model\DoctorSchedule|null $schedule
 * @property int|null $sex 性别
 * @property string|null $name 姓名
 * @property int|null $age 年龄
 * @property string|null $mobile 手机号
 * @property-read mixed $pay_status_text
 * @property-read mixed $pay_type_text
 * @property-read mixed $status_text
 * @property-read \app\admin\model\DoctorOrderRecord|null $record
 * @property-read \app\admin\model\DoctorOrderComment|null $comment
 * @mixin \Eloquent
 */
class DoctorOrder extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'doctor_id',
        'user_id',
        'pay_amount',
        'price',
        'discount_amount',
        'pay_status',
        'ordersn',
        'pay_type',
        'status',
        'pay_time',
        'create_time',
        'update_time',
        'schedule_id',
        'pay_time'
    ];

    protected $casts = [
        'pay_time' => 'datetime',
    ];

    protected $appends = [
        'status_text',
        'pay_status_text',
        'pay_type_text',
    ];

    function scheduleItem()
    {
        return $this->hasMany(DoctorOrderSchedule::class, 'order_id', 'id');
    }

    function schedule()
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    function record()
    {
        return $this->hasOne(DoctorOrderRecord::class, 'order_id', 'id');
    }

    function comment()
    {
        return $this->hasOne(DoctorOrderComment::class, 'order_id', 'id');
    }

    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = ['待付款', '待确认', '已预约', '已完成', '过号未诊', '取消'];
        return $list[$value]??'';
    }

    function getPayStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->pay_status;
        $list = ['未付款', '已付款', '已退款'];
        return $list[$value]??'';
    }

    function getPayTypeTextAttribute($value)
    {
        $value = $value ? $value : $this->pay_type;
        $list = ['无', '微信', '余额'];
        return $list[$value]??'';
    }


}
