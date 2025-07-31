<?php

namespace app\admin\model;

use plugin\admin\app\model\Base;



/**
 * 
 *
 * @property int $id 主键
 * @property int $user_id 用户
 * @property int $favoritable_id 被收藏对象的ID
 * @property string $favoritable_type 被收藏对象的类型
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFavorite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFavorite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserFavorite query()
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $favoritable
 * @property-read \app\admin\model\User|null $user
 * @mixin \Eloquent
 */
class UserFavorite extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'wa_user_favorite';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'favoritable_id', 'favoritable_type'];

    public function favoritable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }



    
    
    
}
