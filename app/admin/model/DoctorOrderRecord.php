<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property integer $id 主键(主键)
 * @property integer $user_id 用户
 * @property integer $order_id 订单
 * @property string $shen_se 神色
 * @property string $she_zhi 舌质
 * @property string $she_tai 舌苔
 * @property string $ti_xing 体型
 * @property string $qi_wei 气味
 * @property string $han 汗
 * @property string $yin_shi 饮食
 * @property string $shui_mian 睡眠
 * @property string $er_bian 二便
 * @property string $yue_jing 月经
 * @property string $mai_xiang 脉象
 * @property string $bu_chu_zhen 局部触诊
 * @property string $constitution_types 体质类型
 * @property string $bianzheng_keypoint 辨证要点
 * @property string $main_symptom 主要症状
 * @property string $additional_symptoms 伴随症状
 * @property string $aggravating_relieving_factors 加重缓解因素
 * @property string $diagnosis_result 诊断结果
 * @property string $decoction_method 煎服法
 * @property string $prescription_notes 服药注意事项
 * @property string $therapy_plan 理疗方案内容
 * @property string $therapy_notes 理疗注意事项
 * @property string $warning_reaction 治疗反应预警
 * @property integer $followup_days 建议复诊天数
 * @property string $emergency_guidelines 紧急情况处理说明
 * @property string $doctor_name 诊疗医师姓名
 * @property string $assistant_name 助理医师姓名
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DoctorOrderRecord query()
 * @property int $status 状态:0=待支付,1=已支付
 * @property string $pay_amount 支付金额
 * @property string $ordersn 订单编号
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\DoctorOrderRecordMedicine> $medicine
 * @property-read \app\admin\model\DoctorOrder|null $order
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class DoctorOrderRecord extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_doctor_order_record';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    function order()
    {
        return $this->belongsTo(DoctorOrder::class, 'order_id', 'id');
    }

    function medicine()
    {
        return $this->hasMany(DoctorOrderRecordMedicine::class, 'record_id', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    
    
}
