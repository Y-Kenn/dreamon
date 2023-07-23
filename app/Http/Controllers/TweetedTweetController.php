<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservedTweet;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use mysql_xdevapi\Exception;

//投稿済みのツイート一覧を返却
//ツイート削除はReservedTweetControllerで実施
class TweetedTweetController extends Controller
{
    public function index(){
        try {
            $data = ReservedTweet::where('twitter_id', Session::get('twitter_id'))
                                    ->whereNotNull('tweeted_at')
                                    ->orderBy('reserved_date','desc')
                                    ->select('id', 'text', 'reserved_date')
                                    ->paginate(5)
                                    ->toArray();
            return $data;
        } catch (\Throwable $e) {
            Log::error('[ERROR] TWEETED TWEET CONTROLLER - INDEX : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }
    }


}
