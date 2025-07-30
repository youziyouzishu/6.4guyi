<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;



/**
 * 
 *
 * @property int $id 主键
 * @property string $title 标题
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Help newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Help newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Help query()
 * @property string $content 内容
 * @mixin \Eloquent
 */
class Help extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_help';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';



    
    
    
}
