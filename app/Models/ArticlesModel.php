<?php
namespace App\Models;

/**
 * App\Models\ArticlesModel
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommentsModel[] $comments
 * @property-read mixed $article_support_desc
 * @property-read mixed $pull_top_desc
 * @property-read mixed $status_desc
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RepliesModel[] $replies
 * @mixin \Eloquent
 */
class ArticlesModel extends OwnModel
{

    protected $table = 'articles';
    protected $guarded = ['id'];

    protected $dates = ['created_at', 'updated_at'];
    protected $appends = ['status_desc', 'pull_top_desc', 'article_support_desc'];

    protected $fillable = ['title', 'content', 'image', 'pull_top', 'article_support', 'status', 'click_number', 'key_words', 'render'];
    /**
     * @Author roseEnd
     * @date 2017/9/22 11:18
     * @desc 文章与评论表的一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(CommentsModel::class, 'article_id');
    }

    /**
     * @Author roseEnd
     * @date 2017/9/22 16:03
     * @desc 与回复的一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(RepliesModel::class, 'article_id');
    }

    public function getStatusDescAttribute()
    {
        return $this->attributes['status'] == 'public' ? '公开' : '私有';
    }

    public function getPullTopDescAttribute()
    {
        return $this->attributes['pull_top'] == 'yes' ? '是' : '否';
    }

    public function getArticleSupportDescAttribute()
    {
        return $this->attributes['article_support'] == 'yes' ? '是' : '否';
    }
}