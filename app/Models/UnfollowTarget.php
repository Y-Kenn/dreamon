<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnfollowTarget extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'followed_accounts_id',
        'user_twitter_id',
        'target_twitter_id',
        'thrown_at',
        'unfollowed_at',
    ];

    public function twitterAccount(){
        return $this->belongsTo('App\Models\TwitterAccount');
    }
}
