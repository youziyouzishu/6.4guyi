<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property int $service_id 项目
 * @property int $user_id 用户
 * @property string $pay_amount 支付金额
 * @property string $price 单价
 * @property string $discount_amount 减免金额
 * @property int $pay_type 支付方式:0=无,1=微信,2=余额
 * @property int $status 订单状态:0=待付款,1=待确认,2=已预约,3=已完成,4=到店超时,5=取消
 * @property int $pay_status 支付状态:0=未付款,1=已付款,2=已退款
 * @property int|null $schedule_id 预约时间
 * @property string $ordersn 订单编号
 * @property \Illuminate\Support\Carbon|null $pay_time 支付时间
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read mixed $pay_status_text
 * @property-read mixed $pay_type_text
 * @property-read mixed $status_text
 * @property-read \app\admin\model\ServiceSchedule|null $schedule
 * @property-read \app\admin\model\Service|null $service
 * @property-read \app\admin\model\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceOrder query()
 * @property int $is_assess 是否评价:0=否,1=是
 * @mixin \Eloquent
 */
class ServiceOrder extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_service_order';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'service_id',
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

    function schedule()
    {
        return $this->belongsTo(ServiceSchedule::class, 'schedule_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }


    function getStatusTextAttribute($value)
    {
        $value = $value ? $value : $this->status;
        $list = ['待付款', '待确认', '已预约', '已完成', '到店超时', '取消'];
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
