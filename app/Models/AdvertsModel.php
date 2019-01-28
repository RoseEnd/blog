<?php
namespace App\Models;

/**
 * App\Models\TagsModel
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ArticleTagModel[] $articleTag
 * @mixin \Eloquent
 */
class AdvertsModel extends OwnModel
{
    protected  $table = 'adverts';

    public static $positionDesc =  [
            'top' => '右侧上方',
            'middle' => '右侧中部',
            'bottom' => '右侧底部',
            'banner' => '横条展示'
        ];

    protected $fillable = ['code', 'description', 'display', 'position'];

    protected $appends = ['position_desc'];


    /**
     * @Author superman
     * @date 2017/11/17 13:31
     * @desc 获取广告摆放位置描述信息
     * @return mixed
     */
    public function getPositionDescAttribute()
    {
        $positionDesc =  [
            'top' => '右侧上方',
            'middle' => '右侧中部',
            'bottom' => '右侧底部',
            'banner' => '横条展示'
        ];
        return $positionDesc[$this->attributes['position']];
    }
}