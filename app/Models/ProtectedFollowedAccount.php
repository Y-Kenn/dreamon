<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProtectedFollowedAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_twitter_id',
        'protected_twitter_id',
    ];

    public function twitterAccount(){
        return $this->belongsTo('App\Models\TwitterAccount');
    }
}
