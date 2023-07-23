<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use Illuminate\Http\Request;
use App\Models\TwitterAccount;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Library\TwitterApi;


//アカウント切り替え用コントローラ
class ChangeAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::debug('USER : ' .print_r(Auth::id(), true));

        try {
            //ユーザに紐づけられたTwitterアカウントIDを全て取得
            $data = Auth::user()->twitterAccounts()
                ->select('twitter_id', 'active_flag')
                ->get();
        } catch (\Throwable $e) {
            Log::error('[ERROR] CHANGE ACCOUNT CONTROLLER - INDEX - READ : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }



        $ids = array_column($data->toArray(), 'twitter_id');

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken(Session::get('twitter_id'));
        $TwitterApi->setTokenToHeader($access_token);

        //各Twitterアカウントのアカウント情報取得
        $result = $TwitterApi->getUserInfoByIds($ids);
        $result['user_id'] = Auth::id();

        //リクエスト失敗時
        if(!isset($result['data'])){
            $result['data'] = array();
            return false;
        }

        Log::debug('CHANGE ACCOUNT TWITTER API RESPONSE : ' .print_r($result, true));
        //取得した情報でtwitter_accounts_tableのtwitter_usernameを更新
        foreach($result['data'] as $account){
            try{
                DB::transaction(function () use($account){
                    $result = TwitterAccount::where('twitter_id', $account['id'])->update([
                        'twitter_username' => $account['username'],
                    ]);
                    Log::debug('ACCOUNT : ' .print_r($account, true));
                    DBErrorHandler::checkUpdated($result);
                });
            }catch (\Throwable $e){
                Log::error('[ERROR] CHANGE ACCOUNT CONTROLLER - INDEX - UPDATE : ' . print_r($e->getMessage(), true));
            }

        }

        //取得した情報にtwitter_account_tableのレコードID(Twitter ID)と使用中フラグを付加
        for($i = 0; $i < count($result['data']); $i++){
            foreach($data as $account){
                if($result['data'][$i]['id'] == (string)$account['twitter_id']){
                    $result['data'][$i]['record_id'] = (string)$account['twitter_id'];
                    $result['data'][$i]['active_flag'] = $account['active_flag'];
                }
            }
        }

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
        //
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
    //使用アカウントの切り替え処理
    public function update(Request $request, string $id)
    {
        $request->validate([
            'active_flag' => 'required|boolean'
        ]);

        Log::debug('PUT - ID: ' .print_r($id, true));
        Log::debug('PUT - REQUEST : ' .print_r($request->all(), true));

        try {
            DB::transaction(function () use($id){
                $result = Auth::user()->twitterAccounts()
                    ->where('active_flag', true)
                    ->update(['active_flag' => false]);
                DBErrorHandler::checkUpdated($result);

                $result = TwitterAccount::where('twitter_id', $id)->update(['active_flag' => true]);
                DBErrorHandler::checkUpdated($result);
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] CHANGE ACCOUNTS CONTROLLER - UPDATE : ' .print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }

        Log::debug('SESSION PUT - ACTIVE : ' .print_r(TwitterAccount::find($id)->toArray(), true));
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
