<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;


/**
 * 
 *
 * @property int $id 主键
 * @property string $name 名称
 * @property string $tags 标签
 * @property string $price 价格
 * @property string $image 封面
 * @property string $images 轮播图
 * @property int $minute 分钟
 * @property string $content 详情
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service query()
 * @property int $status 状态:1=正常,2=隐藏
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Service normal()
 * @property string $address 地址
 * @property string $lat 纬度
 * @property string $lng 经度
 * @property string $open 营业时间
 * @property string $mobile 手机号
 * @mixin \Eloquent
 */
class Service extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_service';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function scopeNormal($query)
    {
        return $query->where('status', 1);
    }


    
    
    
}
