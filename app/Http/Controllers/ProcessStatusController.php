<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReservedTweet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

//各自動機能の稼働ステータス管理用コントローラ
class ProcessStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Auth::user()->twitterAccounts()
                            ->where('twitter_id', Session::get('twitter_id'))
                            ->select('following_flag', 'unfollowing_flag', 'liking_flag')
                            ->first();

        $tweets_num = ReservedTweet::where('twitter_id', Session::get('twitter_id'))
                                        ->whereNull('thrown_at')
                                        ->count();
        $response = [
            [
                'id' => 0,
                'process_name' => '自動フォロー',
                'status' => ($data['following_flag']) ? true : false,
                'detail' => ($data['following_flag']) ? '稼働中' : '停止中',
            ],
            [
                'id' => 1,
                'process_name' => '自動アンフォロー',
                'status' => ($data['unfollowing_flag']) ? true : false,
                'detail' => ($data['unfollowing_flag']) ? '稼働中' : '停止中',
            ],
            [
                'id' => 2,
                'process_name' => '自動いいね',
                'status' => ($data['liking_flag']) ? true : false,
                'detail' => ($data['liking_flag']) ? '稼働中' : '停止中',
            ],
            [
                'id' => 3,
                'process_name' => 'ツイート予約',
                'status' => ($tweets_num) ? true : false,
                'detail' => ($tweets_num) ? (string)$tweets_num .'件' : '予約なし',
            ],
        ];

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
    //いいね、フォロー、アンフォローの稼働フラグを更新
    public function update(Request $request, string $id)
    {
        $request->validate([
            'flag_name' => 'required|string',
            'status' => 'required|boolean'
        ]);

        Auth::user()->twitterAccounts()
                    ->where('twitter_id', Session::get('twitter_id'))
                    ->update([$request->flag_name => $request->status]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
