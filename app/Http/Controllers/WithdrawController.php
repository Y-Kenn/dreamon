<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TwitterAccount;
use App\Models\FollowKeyword;
use App\Models\LikeKeyword;
use App\Models\FollowedAccount;
use App\Models\FollowTarget;
use App\Models\UnfollowTarget;
use App\Models\LikeTarget;
use App\Models\TargetBaseAccount;
use App\Models\ProtectedFollowedAccount;
use App\Models\ReservedTweet;
use App\Models\TwitterAccountData;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use mysql_xdevapi\Exception;

//退会処理
class WithdrawController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

    //退会ユーザに紐づく全レコード削除
    public function destroy(string $id)
    {
        try{
            $twitter_ids = array_column(Auth::user()->twitterAccounts()->get()->toArray(), 'twitter_id');
        }catch (\Throwable $e){
            Log::error('[ERROR] WITHDRAW CONTROLLER - DESTROY - FIND : ' . print_r($e->getMessage(), true));

            return response()->json('', \Illuminate\Http\Response::HTTP_NOT_IMPLEMENTED);
        }

        try{
            DB::transaction(function () use($twitter_ids){
                foreach ($twitter_ids as $user_twitter_id){
                    FollowKeyword::where('twitter_id', $user_twitter_id)->forceDelete();
                    LikeKeyword::where('twitter_id', $user_twitter_id)->forceDelete();
                    FollowedAccount::where('user_twitter_id', $user_twitter_id)->forceDelete();
                    FollowTarget::where('user_twitter_id', $user_twitter_id)->forceDelete();
                    LikeTarget::where('user_twitter_id', $user_twitter_id)->forceDelete();
                    UnfollowTarget::where('user_twitter_id', $user_twitter_id)->forceDelete();
                    TargetBaseAccount::where('user_twitter_id', $user_twitter_id)->forceDelete();
                    ProtectedFollowedAccount::where('user_twitter_id', $user_twitter_id)->forceDelete();
                    ReservedTweet::where('twitter_id', $user_twitter_id)->forceDelete();
                    TwitterAccountData::where('twitter_id', $user_twitter_id)->forceDelete();
                }
                Auth::user()->twitterAccounts()->forceDelete();
                Auth::user()->forceDelete();
                Session::flush();
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] TWITTER REGISTER CONTROLLER - DESTROY - DELETE : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }
        Log::debug('WITHDRAWN');
    }
}
