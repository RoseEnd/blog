<?php
namespace App\Models;

/**
 * App\Models\FriendlyLinksModel
 *
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendlyLinksModel onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendlyLinksModel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\FriendlyLinksModel withoutTrashed()
 * @mixin \Eloquent
 */
class FriendlyLinksModel extends OwnModel
{


    protected $table = 'friendly_links';

    protected $guarded = ['id'];


    protected $appends = ['isEdit'];


    public function getIsEditAttribute()
    {
        return false;
    }

}