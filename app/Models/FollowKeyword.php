<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowKeyword extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'twitter_id',
        'keywords',
        'not_flag',
    ];

    public function twitterAccount()
    {
        return $this->belongsTo('App\Model\TwitterAccount');
    }

}
