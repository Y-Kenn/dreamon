<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwitterAccountData;
use App\Models\FollowedAccount;
use App\Models\LikeTarget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Library\TwitterApi;

//Twitterアカウントの情報(フォロー・フォロワー等)の処理
class TwitterAccountDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $twitter_id = Session::get('twitter_id');

        //本日フォローした数
        $following_today =  FollowedAccount::where('user_twitter_id', $twitter_id)
                                            ->where('followed_at', '>', date("Y/m/d"))
                                            ->count();
        //過去30日でフォローした数
        $following_30days =  FollowedAccount::where('user_twitter_id', $twitter_id)
                                            ->where('followed_at', '>', date("Y/m/d", time() - 60*60*24*30))
                                            ->count();
        //本日アンフォローした数
        $unfollowing_today =  FollowedAccount::where('user_twitter_id', $twitter_id)
                                            ->where('unfollowed_at', '>', date("Y/m/d"))
                                            ->count();
        //過去30日でフォローした数
        $unfollowing_30days =  FollowedAccount::where('user_twitter_id', $twitter_id)
                                            ->where('unfollowed_at', '>', date("Y/m/d", time() - 60*60*24*50))
                                            ->count();
        //本日いいねした数
        $like_today =  LikeTarget::where('user_twitter_id', $twitter_id)
                                            ->where('liked_at', '>', date("Y/m/d"))
                                            ->count();
        //過去30日でいいねした数
        $like_30days =  LikeTarget::where('user_twitter_id', $twitter_id)
                                            ->where('liked_at', '>', date("Y/m/d", time() - 60*60*24*50))
                                            ->count();


        Log::debug('FOLLOWED ACCOUNTS: ' .print_r($like_today, true));

        //返却データ整形
        $response = [
            'following_today' => $following_today,
            'following_30days' => $following_30days,
            'followers_today' => 0,
            'followers_30days' => 0,
            'unfollowing_today' => $unfollowing_today,
            'unfollowing_30days' => $unfollowing_30days,
            'like_today' => $like_today,
            'like_30days' => $like_30days,
        ];


        //過去30日のフォロワー数の記録を取得
        $twitter_data = TwitterAccountData::where('twitter_id', $twitter_id)
                                            ->latest()
                                            ->limit(30)
                                            ->get()->toArray();

        if(empty($twitter_data)){
            return $response;
        }

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken($twitter_id);
        $TwitterApi->setTokenToHeader($access_token);
        //現在のフォロワー数を取得
        $result = $TwitterApi->getUserInfoByIds([$twitter_id]);
        //リクエスト失敗時
        if(!isset($result['data'])){
            return $response;
        }

        //現在のフォロワー数と比較して増加フォロワーを計算
        $response['followers_today'] = $result['data'][0]['public_metrics']['followers_count'] - $twitter_data[0]['followers'];
        $response['followers_30days'] = $result['data'][0]['public_metrics']['followers_count'] - $twitter_data[count($twitter_data) - 1]['followers'];

        Log::debug('TWITTER DATA : ' .print_r($response, true));

        return $response;
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
