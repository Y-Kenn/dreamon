<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiHist extends Model
{
    use HasFactory;

    protected $fillable = [
        'twitter_id',
        'func',
        'limited',
    ];
}
