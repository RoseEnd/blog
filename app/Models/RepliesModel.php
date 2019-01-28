<?php
namespace App\Models;

/**
 * App\Models\RepliesModel
 *
 * @property-read mixed $created_at
 * @property-read \App\Models\UsersModel $users
 * @mixin \Eloquent
 */
class RepliesModel extends OwnModel
{
    protected $table = 'replies';

    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsTo(UsersModel::class, 'user_id');
    }


    public function getCreatedAtAttribute()
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->attributes['created_at'])->diffForHumans();
    }
}