<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;




/**
 * 
 *
 * @property int $id 主键
 * @property int $question_id 关联问题
 * @property string $label 选项内容
 * @property int $weight 权重
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackOption query()
 * @mixin \Eloquent
 */
class FeedbackOption extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_feedback_option';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'question_id',
        'title',
        'weight',
        'label',

    ];



    
    
    
}
