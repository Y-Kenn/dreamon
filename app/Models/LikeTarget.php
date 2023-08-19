<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LikeTarget extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_twitter_id',
        'target_tweet_id',
        'target_twitter_id',
        'thrown_at',
        'liked_at',
    ];

    public function twitterAccount(){
        return $this->belongsTo('App\Models\TwitterAccount');
    }
}
