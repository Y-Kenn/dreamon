<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TwitterAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $primaryKey = 'twitter_id';

    protected $fillable = [
        'twitter_id',
        'user_id',
        'access_token',
        'refresh_token',
        'token_generated_time',
        'following_flag',
        'unfollowing_flag',
        'liking_flag',
        'last_chain_at',
        'waiting_chain_flag',
        'locked_flag',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function followKeywords()
    {
        return $this->hasMany('App\Models\FollowKeyword', 'twitter_id');
    }

    public function targetBaseAccounts()
    {
        return $this->hasMany('App\Models\TargetBaseAccount', 'user_twitter_id');
    }

    public function reservedTweets()
    {
        return $this->hasMany('App\Models\ReservedTweet', 'twitter_id');
    }
}
