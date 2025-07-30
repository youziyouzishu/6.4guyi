<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 *
 * @property int $id 主键
 * @property int $order_id 订单
 * @property int $doctor_id 医师
 * @property string|null $content 内容
 * @property int $cryptonym 匿名:0=否,1=是
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderComment query()
 * @property int $user_id 用户
 * @property int $cure_score 治疗效果:1=非常差,2=差,3=一般,4=好,5=非常好
 * @property int $manner_score 沟通态度:1=非常差,2=差,3=一般,4=好,5=非常好
 * @mixin \Eloquent
 */
class DoctorOrderComment extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_order_comment';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'order_id',
        'doctor_id',
        'cure_score',
        'manner_score',
        'content',
        'cryptonym',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'cure_score_text',
        'manner_score_text',
    ];

    function getCureScoreTextAttribute($value)
    {
        $value = $value ? $value : $this->cure_score;
        $list = [1=>'非常差',2=>'差',3=>'一般',4=>'好',5=>'非常好'];
        return $list[$value]??'';
    }

    function getMannerScoreTextAttribute($value)
    {
        $value = $value ? $value : $this->manner_score;
        $list = [1=>'非常差',2=>'差',3=>'一般',4=>'好',5=>'非常好'];
        return $list[$value]??'';
    }
    
    
    
}
