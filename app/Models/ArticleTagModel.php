<?php
namespace App\Models;

/**
 * App\Models\ArticleTagModel
 *
 * @property-read \App\Models\TagsModel $tag
 * @mixin \Eloquent
 */
class ArticleTagModel extends OwnModel
{

    protected $table = 'article_tag';
    protected $guarded = ['id'];


    public function tag()
    {
        return $this->belongsTo(\App\Models\TagsModel::class, 'tag_id');
    }
}