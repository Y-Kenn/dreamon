<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwitterAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Library\TwitterApi;



class ChangeAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::debug('USER : ' .print_r(Auth::id(), true));

        $data_builder = Auth::user()->twitterAccounts()
                                    ->select('twitter_id', 'active_flag');

        if(!$data_builder->exists()){
            return array();
        }

        $data = $data_builder->get();

        $ids = array_column($data->toArray(), 'twitter_id');

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken(Session::get('twitter_id'));
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->getUserInfoByIds($ids);
        $result['user_id'] = Auth::id();

        //リクエスト失敗時
        if(!isset($result['data'])){
            $result['data'] = array();
            return false;
        }

        foreach($result['data'] as $account){
            TwitterAccount::where('twitter_id', $account['id'])->update([
                'twitter_username' => $account['username'],
            ]);
        }

        for($i = 0; $i < count($result['data']); $i++){
            foreach($data as $account){
                if($result['data'][$i]['id'] == (string)$account['twitter_id']){
                    $result['data'][$i]['record_id'] = (string)$account['twitter_id'];
                    $result['data'][$i]['active_flag'] = $account['active_flag'];
                }
            }
        }



        //Log::debug('CHANGE ACCOUNT - INDEX' .print_r($result['data'], true));
        return $result;
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
        $data = Auth::user()->twitterAccounts()
                            ->where('twitter_id', $id)
                            ->select('twitter_id')
                            ->get();

        // $ids = array_column($data->toArray(), 'twitter_id');
        $ids = [$id];

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken(Session::get('twitter_id'));
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->getUserInfoByIds($ids);

        //リクエスト失敗時
        if(!isset($result['data'])){
            return false;
        }

        return $result['data'][0];
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'active_flag' => 'required|boolean'
        ]);

        Log::debug('PUT - ID: ' .print_r($id, true));
        Log::debug('PUT - REQUEST : ' .print_r($request->all(), true));

        Auth::user()->twitterAccounts()
                    ->where('active_flag', true)
                    ->update(['active_flag' => false]);
        TwitterAccount::where('twitter_id', $id)->update(['active_flag' => true]);
        Log::debug('PUT - ACTIVE : ' .print_r(TwitterAccount::find($id)->get(), true));
        Session::put('twitter_id', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
