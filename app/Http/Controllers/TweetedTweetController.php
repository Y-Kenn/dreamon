<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservedTweet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

//投稿済みのツイート一覧を返却
//ツイート削除はReservedTweetControllerで実施
class TweetedTweetController extends Controller
{
    public function index(){
        $data = ReservedTweet::where('twitter_id', Session::get('twitter_id'))
                                ->whereNotNull('tweeted_at')
                                ->orderBy('reserved_date')
                                ->select('id', 'text', 'reserved_date')
                                ->get()->toArray();

        Log::debug('TWEETED TWEET - INDEX : ' .print_r($data, true));
        return $data;
    }


}
