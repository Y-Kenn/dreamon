<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservedTweet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'twitter_id',
        'text',
        'reserved_date',
        'thrown_at',
        'tweeted_at',
        'tweet_id',
    ];

    public function twitterAccount(){
        return $this->belongsTo('App\Models\TwitterAccount');
    }
}
