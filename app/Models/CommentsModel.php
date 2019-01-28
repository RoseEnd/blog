<?php
namespace App\Models;

/**
 * App\Models\CommentsModel
 *
 * @property-read mixed $created_at
 * @property-read mixed $show_reply
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RepliesModel[] $replies
 * @property-read \App\Models\UsersModel $users
 * @mixin \Eloquent
 */
class CommentsModel extends OwnModel
{

    protected $table = 'comments';

    protected $guarded = ['id'];

    protected $appends = ['showReply'];


    /**
     * @Author roseEnd
     * @date 2017/9/22 11:31
     * @desc 评论与回复的一对多关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(RepliesModel::class, 'comment_id');
    }

    public function users()
    {
        return $this->belongsTo(UsersModel::class, 'user_id');
    }

    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'])->diffForHumans();
    }

    public function getShowReplyAttribute()
    {
        return false;
    }
}