<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use App\Models\FollowedAccount;
use App\Models\FollowKeyword;
use App\Models\FollowTarget;
use App\Models\LikeKeyword;
use App\Models\LikeTarget;
use App\Models\ProtectedFollowedAccount;
use App\Models\ReservedTweet;
use App\Models\TargetBaseAccount;
use App\Models\TwitterAccountData;
use App\Models\UnfollowTarget;
use App\Models\User;
use App\Models\TwitterAccount;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

//Twitter OAuth 2.0 によるユーザ登録
class TwitterRegisterController extends Controller
{

    public function create(): View
    {
        return view('loading');
    }

    public function store(Request $request): RedirectResponse
    {
        Log::debug('TWITTER REGISTER');
        Log::debug('CODE : ' .print_r($request->all(), true));
        Log::debug('CODE_VERIFIER : ' .print_r($request->code_verifier, true));
        Log::debug('CLIENT_ID : ' .print_r(env('CLIENT_ID'), true));
        Log::debug('CLIENT_SECLET : ' .print_r(env('CLIENT_SECRET'), true));
        $TwitterApi = new TwitterApi(env('API_KEY'), env('API_SECRET'), env('BEARER'), env('CLIENT_ID'), env('CLIENT_SECRET'), env('REDIRECT_URI'));
        $request->validate([
            'code' => 'required',
            'code_verifier' =>'required'
        ]);

        $tokens = $TwitterApi->getAccessToken($request->code, $request->code_verifier);
        Log::debug('TOKEN : ' . print_r($tokens, true));

        //アクセストークンを正常取得できた場合
        if(isset($tokens['access_token']) && isset($tokens['refresh_token'])){


            $TwitterApi->setTokenToHeader($tokens['access_token']);
            $account_info = $TwitterApi->getMyInfo();

            //Twitterアカウント情報を正常取得できた場合
            if(isset($account_info['data']['id'])){
                $twitter_id = $account_info['data']['id'];

                $user = TwitterAccount::where('twitter_id', $twitter_id);

                Log::debug('ACCOUNT : ' .print_r($user->get(), true));
                Log::debug('SESSION : ' .print_r(Session::get('twitter_id') ,true));

                try {
                    $user_exist_flag = $user->exists();
                } catch (\Throwable $e) {
                    Log::error('[ERROR] TWITTER REGISTER CONTROLLER - STORE - READ : ' .print_r($e->getMessage(), true));
                    Session::flush();
                    return redirect('/login');
                }

                //登録済Twitterアカウントの場合はログイン
                if($user_exist_flag){
                    try {
                        DB::transaction(function () use($user, $tokens){
                            $result = $user->update([
                                'access_token' => $tokens['access_token'],
                                'refresh_token' => $tokens['refresh_token'],
                                'token_generated_time' => date("Y/m/d H:i:s"),
                            ]);
                            DBErrorHandler::checkUpdated($result);
                        });
                    }catch (\Throwable $e){
                        Log::error('[ERROR] TWITTER REGISTER CONTROLLER - STORE - UPDATE TOKENS : ' .print_r($e->getMessage(), true));
                        Session::flush();
                        return redirect('/login');
                    }

                    Auth::loginUsingId($user->first()['user_id']);

                    try {
                        DB::transaction(function () use($twitter_id){
                    //ユーザが操作するTwitterアカウントを認証するアカウントに切り替え
                            $result = Auth::user()->twitterAccounts()
                                            ->where('active_flag', true)
                                            ->update(['active_flag' => false]);
                            DBErrorHandler::checkUpdated($result);
                            $result = TwitterAccount::where('twitter_id', $twitter_id)->update(['active_flag' => true]);
                            DBErrorHandler::checkUpdated($result);
                        });
                    }catch (\Throwable $e) {
                        Log::error('[ERROR] TWITTER REGISTER CONTROLLER - STORE - UPDATE ACCTIVE FLAG : ' . print_r($e->getMessage(), true));
                        Session::flush();
                        return redirect('/login');
                    }

                    Session::put('twitter_id', $twitter_id);
                    return redirect('/home');
                //未登録アカウントの場合
                }else{
                    //他のTwitterアカウントでログイン中の場合
                    if(Session::get('twitter_id')){

                        Log::debug('SESSION EXISTS');

                        try {
                            $registerd_twitter_accounts_num = Auth::user()->twitterAccounts()->count();
                            //正常に取得できなかった場合は処理せずホーム画面へ遷移
                            if($registerd_twitter_accounts_num === 0){
                                return redirect(RouteServiceProvider::HOME);
                            }
                        //正常に取得できなかった場合は処理せずホーム画面へ遷移
                        }catch (\Throwable $e){
                            return redirect(RouteServiceProvider::HOME);
                        }
                        //ユーザの登録Twitterアカウント数が上限に達している場合、登録せずHOMEに遷移
                        if($registerd_twitter_accounts_num >= env('MAX_ACCOUNTS')){
                            Log::debug('ACCOUNTS NUM OVER');
                            return redirect(RouteServiceProvider::HOME);
                        }

                        try{
                            DB::transaction(function () use($twitter_id, $account_info, $tokens){
                                $result = Auth::user()->twitterAccounts()
                                            ->where('active_flag', true)
                                            ->update(['active_flag' => false]);
                                DBErrorHandler::checkUpdated($result);

                                $result = $twitter_account = TwitterAccount::create([
                                    'twitter_id' => $twitter_id,
                                    'user_id' => Auth::id(),
                                    'twitter_username' => $account_info['data']['username'],
                                    'active_flag' => true,
                                    'access_token' => $tokens['access_token'],
                                    'refresh_token' => $tokens['refresh_token'],
                                    'token_generated_time' => date("Y/m/d H:i:s"),
                                    'following_flg' => false,
                                    'unfollowing_flg' => false,
                                    'liking_flg' => false,
                                ]);
                                DBErrorHandler::checkCreated($result);
                            });
                        }catch (\Throwable $e) {
                            Log::error('[ERROR] TWITTER REGISTER CONTROLLER - STORE - CREATE - ADD ACCOUNT : ' . print_r($e->getMessage(), true));
                            Session::flush();
                            return redirect('/login');
                        }
                        Session::put('twitter_id', $twitter_id);

                    //Twitterアカウント初登録のユーザの場合（ユーザ登録されていない場合）
                    }else{
                        try{
                            DB::transaction(function () use($twitter_id, $account_info, $tokens){
                                // //usersレコードを生成
                                $user = User::create();
                                DBErrorHandler::checkUpdated($user);

                                Log::debug('ACCOUNT INFO : ' .print_r($account_info['data']['id'], true));
                                // //Twitterアカウントレコードを生成
                                $twitter_account = TwitterAccount::create([
                                    'twitter_id' => $twitter_id,
                                    'user_id' => $user->id,
                                    'twitter_username' => $account_info['data']['username'],
                                    'active_flag' => true,
                                    'access_token' => $tokens['access_token'],
                                    'refresh_token' => $tokens['refresh_token'],
                                    'token_generated_time' => date("Y/m/d H:i:s"),
                                    'following_flg' => false,
                                    'unfollowing_flg' => false,
                                    'liking_flg' => false,
                                ]);
                                DBErrorHandler::checkUpdated($twitter_account);

                                event(new Registered($user));
                                Auth::login($user);
                                Session::put('twitter_id', $twitter_id);
                            });
                        }catch (\Throwable $e) {
                            Log::error('[ERROR] TWITTER REGISTER CONTROLLER - STORE - CREATE - FIRST ACCOUNT : ' . print_r($e->getMessage(), true));
                            Session::flush();
                            return redirect('/login');
                        }
                    }
                }

                return redirect('/home');
            }else{
                Session::flush();
                return redirect('/login');
            }
        }else{
            Session::flush();
            return redirect('/login');
        }
    }

    //Twitterアカウントのデータを削除
    public function destroy(string $id)
    {
        Log::debug('DELETE MY ACCOUT');

        try{
            $user = Auth::user()->twitterAccounts()->find($id)->toArray();
            DBErrorHandler::checkFound($user);
        }catch (\Throwable $e){
            Log::error('[ERROR] TWITTER REGISTER CONTROLLER - DESTROY - FIND : ' . print_r($e->getMessage(), true));

            return response()->json('', \Illuminate\Http\Response::HTTP_NOT_IMPLEMENTED);
        }

        //idが自身のTwitterアカウントのものでなければ終了
        if(!$user){
            return false;
        }

        try {
            DB::transaction(function () use($id){
                //Twitterアカウントに紐づく全テーブル(usersテーブル以外)の全レコード削除
                FollowKeyword::where('twitter_id', $id)->forceDelete();
                LikeKeyword::where('twitter_id', $id)->forceDelete();
                FollowedAccount::where('user_twitter_id', $id)->forceDelete();
                FollowTarget::where('user_twitter_id', $id)->forceDelete();
                LikeTarget::where('user_twitter_id', $id)->forceDelete();
                UnfollowTarget::where('user_twitter_id', $id)->forceDelete();
                TargetBaseAccount::where('user_twitter_id', $id)->forceDelete();
                ProtectedFollowedAccount::where('user_twitter_id', $id)->forceDelete();
                ReservedTweet::where('twitter_id', $id)->forceDelete();
                TwitterAccountData::where('twitter_id', $id)->forceDelete();
                $result = Auth::user()->twitterAccounts()->find($id)->forceDelete();
                DBErrorHandler::checkDeleted($result);
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] TWITTER REGISTER CONTROLLER - DESTROY - DELETE : ' . print_r($e->getMessage(), true));

            return response()->json('', \Illuminate\Http\Response::HTTP_NOT_IMPLEMENTED);
        }
    }
}
