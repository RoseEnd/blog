<?php
namespace App\Models;

/**
 * App\Models\TagsModel
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ArticleTagModel[] $articleTag
 * @mixin \Eloquent
 */
class TagsModel extends OwnModel
{
    protected  $table = 'tags';

    protected $fillable = ['name', 'description', 'img_path'];

    /**
     * @Author roseEnd
     * @date 2017/9/25 15:54
     * @desc 标签与文章的一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articleTag()
    {
        return $this->hasMany(ArticleTagModel::class, 'tag_id');
    }
}