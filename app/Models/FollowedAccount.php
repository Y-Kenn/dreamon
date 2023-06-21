<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowedAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_twitter_id',
        'target_twitter_id',
        'followed_at',
        'last_active_at',
        'unfollowed_at',
        'manual_followed_flag',
    ];

    public function twitterAccount(){
        return $this->belongsTo('App\Models\TwitterAccount');
    }
}
