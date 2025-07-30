<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 物流公司
 * @property string $code 公司编码
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Express newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Express newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Express query()
 * @mixin \Eloquent
 */
class Express extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_express';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';



    
    
    
}
