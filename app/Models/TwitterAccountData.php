<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TwitterAccountData extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'twitter_id',
        'following',
        'followers',
    ];

    public function twitterAccount()
    {
        return $this->belongsTo('App\Model\TwitterAccount');
    }

}
