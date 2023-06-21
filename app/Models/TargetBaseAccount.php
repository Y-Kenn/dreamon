<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetBaseAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_twitter_id',
        'base_twitter_id',
        'started_at',
        'completed_at',
    ];

    public function twitterAccount()
    {
        return $this->belongsTo('App\Model\TwitterAccount');
    }

    public function followTargets()
    {
        return $this->hasMany('App\Models\FollowTarget');
    }
}
