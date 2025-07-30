<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property string $title 问题内容
 * @property int $is_required 必填:0=否,1=是
 * @property string $type 问题类型:radio=单选,checkbox=多选,text=单行文本,textarea=多行文本
 * @property int $weight 权重
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \app\admin\model\FeedbackOption> $option
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackQuestion query()
 * @mixin \Eloquent
 */
class FeedbackQuestion extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_feedback_question';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'type',
        'is_required',
        'weight',
        'id'
    ];

    public function option()
    {
        return $this->hasMany(FeedbackOption::class, 'question_id', 'id');
    }



    
    
    
}
