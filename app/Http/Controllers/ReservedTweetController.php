<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use App\Library\TwitterApi;
use Illuminate\Http\Request;
use App\Models\ReservedTweet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Response;

//ツイート予約用コントローラ
class ReservedTweetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = ReservedTweet::where('twitter_id', Session::get('twitter_id'))
                                ->whereNull('thrown_at')
                                ->orderBy('reserved_date')
                                ->select('id', 'text', 'reserved_date')
                                ->paginate(5)
                                ->toArray();

            return $data;
        } catch (\Throwable $e) {
            Log::error('[ERROR] RESERVED TWEET CONTROLLER - INDEX : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::debug('RESERVED TWEET - STORE : ' .print_r($request->all(), true));
        $request->validate([
            'text' => 'required|string|max:' .env('TWEET_CHAR_NUM'),
            'reserved_date' => 'required',
        ]);

        try{
            DB::transaction(function() use($request){
                $result = ReservedTweet::create([
                    'twitter_id' => Session::get('twitter_id'),
                    'text' => $request->text,
                    'reserved_date' => $request->reserved_date
                ]);

                DBErrorHandler::checkCreated($result);
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] RESERVED TWEET CONTROLLER - STORE : ' .print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }



    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {


        $tweet = ReservedTweet::find($id)->toArray();

        //ツイート済みの場合
        if($tweet['tweeted_at']){
            Log::debug('TWEETED');

            $tweet_id = ReservedTweet::find($id)->toArray()['tweet_id'];

            $TwitterApi = new TwitterApi(env('API_KEY'),
                                        env('API_SECRET'),
                                        env('BEARER'),
                                        env('CLIENT_ID'),
                                        env('CLIENT_SECRET'),
                                        env('REDIRECT_URI'));

            $access_token = $TwitterApi->checkRefreshToken(Session::get('twitter_id'));
            $TwitterApi->setTokenToHeader($access_token);
            Log::debug('DELETE ID : ' .print_r($tweet_id, true));
            //ツイートの削除
            $result = $TwitterApi->deleteTweet($tweet_id);
            //アカウント凍結を検出
            $TwitterApi->checkAccountLocked($result, Session::get('twitter_id'));

            //ツイートを削除出来た場合、DBレコードの削除
            if(isset($result['data'])){
                try {
                    DB::transaction(function() use($id){
                        $result = ReservedTweet::find($id)->delete();

                        DBErrorHandler::checkDeleted($result);
                    });
                }catch (\Throwable $e){
                    Log::error('[ERROR] ESERVED TWEET CONTROLLER - DESTROY - TWEETED : ' .print_r($e->getMessage(), true));

                    return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
                }
            }

        //ツイート前の場合
        }else {
            try {
                DB::transaction(function () use ($id) {
                    $result = ReservedTweet::find($id)->delete();

                    DBErrorHandler::checkDeleted($result);
                });
            } catch (\Throwable $e) {
                Log::error('[ERROR] RESERVED TWEET CONTROLLER - DESTROY - RESERVING : ' . print_r($e->getMessage(), true));

                return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
            }
        }
    }
}
