<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Library\TwitterApi;

class MentionTweetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $twitter_id = Session::get('twitter_id');

        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken($twitter_id);
        $TwitterApi->setTokenToHeader($access_token);
        $result = $TwitterApi->getMentions($twitter_id);
        //リクエスト失敗時
        if(!isset($result['data'])){
            return array();
        }

        for($i = 0; $i < count($result['data']); $i++){
            $result['data'][$i]['created_at'] = $TwitterApi->toJapanTime($result['data'][$i]['created_at']);
        }

        $result_shaped = $TwitterApi->mergeUserData($result);
        
        Log::debug('MENTIONS : ' .print_r($result_shaped, true));
        return $result_shaped['data'];
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
        //
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
        //
    }
}
