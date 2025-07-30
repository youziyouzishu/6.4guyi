<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;

/**
 * 
 *
 * @property int $id 主键
 * @property int $type 类型:1=退货退款,2=换货
 * @property string $text 内容
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefundReason newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefundReason newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RefundReason query()
 * @mixin \Eloquent
 */
class RefundReason extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_refund_reason';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';


    
    
    
}
