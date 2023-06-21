<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowTarget extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'target_base_id',
        'user_twitter_id',
        'target_twitter_id',
        'thrown_at',
    ];

    public function targetBaseAccount()
    {
        return $this->belongsTo('App\Models\TargetBaseAccount');
    }
}
