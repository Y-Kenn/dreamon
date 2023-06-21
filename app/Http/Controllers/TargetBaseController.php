<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\TwitterAccount;
use App\Models\TargetBaseAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Library\TwitterApi;

class TargetBaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::debug('INDEX');
        $data_builder = TargetBaseAccount::where('user_twitter_id', Session::get('twitter_id'))
                                    ->whereNull('completed_at')
                                    ->limit('100')
                                    ->select('id', 'base_twitter_id', 'started_at');
        if(!$data_builder->exists()){
            return array();
        }
        
        $data = $data_builder->get();

        $ids = array_column($data->toArray(), 'base_twitter_id');

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

        for($i = 0; $i < count($result['data']); $i++){
            foreach($data as $base_account){
                if($result['data'][$i]['id'] === $base_account['base_twitter_id']){
                    $result['data'][$i]['record_id'] = $base_account['id'];
                }
            }
        }

        return $result['data'];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Log::debug('CREATE');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'twitter_name' => 'required|string|max:255',
        ]);

        $user_twitter_id = Session::get('twitter_id');
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $access_token = $TwitterApi->checkRefreshToken($user_twitter_id);
        //TwitterのユーザーネームからTwitter IDを取得
        $result = $TwitterApi->getUserInfoByName($request->twitter_name);
        Log::debug('TARGET BASE : ' .print_r($result, true));
        //正常取得できた場合
        if(isset($result['data'])){

            $exist_flag = TargetBaseAccount::where('base_twitter_id', $result['data']['id'])
                                            ->where('user_twitter_id', $user_twitter_id)
                                            ->whereNull('completed_at')
                                            ->exists();
            //未処理のターゲットベースアカウントと重複していない場合、DB登録
            if(!$exist_flag){
                TargetBaseAccount::create([
                    'user_twitter_id' => $user_twitter_id,
                    'base_twitter_id' => $result['data']['id'],
                ]);
            }else{
                Log::debug('EXIST');
            }
            
            
            return true;
        }else{

            return false;
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
        Log::debug('DELETE : ' .print_r($id, true));
        TargetBaseAccount::find($id)->forceDelete();
    }
}
