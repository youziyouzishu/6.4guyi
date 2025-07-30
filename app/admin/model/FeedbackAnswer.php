<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 关联用户
 * @property int $order_id 关联订单
 * @property int $question_id 关联问题
 * @property string|null $answer 答案
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackAnswer query()
 * @mixin \Eloquent
 */
class FeedbackAnswer extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_feedback_answer';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'question_id',
        'answer',
        'order_id',
        'user_id',
        'id',
    ];



    
    
    
}
