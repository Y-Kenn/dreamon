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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


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
    public function destroy(string $id)
    {
        $twitter_ids = array_column(Auth::user()->twitterAccounts()->get()->toArray(), 'twitter_id');

        foreach ($twitter_ids as $user_twitter_id){
            try{
                FollowKeyword::where('twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-FOLLOW KEYWORD ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                LikeKeyword::where('twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-LIKE KEYWORD ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                FollowedAccount::where('user_twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-FOLLOWED ACCOUNT ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                FollowTarget::where('user_twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-FOLLOW TARGET ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                LikeTarget::where('user_twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-LIKE TARGET ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                UnfollowTarget::where('user_twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-UNFOLLOW TARGET ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                TargetBaseAccount::where('user_twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-TARGET BASE ACCOUNT ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                ProtectedFollowedAccount::where('user_twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-PROTECTED FOLLOWED ACCOUNT ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                ReservedTweet::where('twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-RESERVED TWEET ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }
            try{
                TwitterAccountData::where('twitter_id', $user_twitter_id)->forceDelete();
            } catch(\Exception $e){
                Log::notice('WITHDRAW-TWITTER ACCOUNT DATA ' .(string)$user_twitter_id .' : ' .$e->getMessage());
            }


        }
        try{
            Auth::user()->twitterAccounts()->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-TWITTER ACCOUNT ' .(string)$user_twitter_id .' : ' .$e->getMessage());
        }
        try{
            Auth::user()->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-USER ' .(string)$user_twitter_id .' : ' .$e->getMessage());
        }

        Session::flush();

    }
}
