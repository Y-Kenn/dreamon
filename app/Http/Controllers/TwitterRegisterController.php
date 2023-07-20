<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
                //登録済Twitterアカウントの場合はログイン
                if($user->exists()){
                    $user->update([
                        'access_token' => $tokens['access_token'],
                        'refresh_token' => $tokens['refresh_token'],
                        'token_generated_time' => date("Y/m/d H:i:s"),
                    ]);

                    Auth::loginUsingId($user->first()['user_id']);

                    //ユーザが操作するTwitterアカウントを認証するアカウントに切り替え
                    Auth::user()->twitterAccounts()
                                    ->where('active_flag', true)
                                    ->update(['active_flag' => false]);
                    TwitterAccount::where('twitter_id', $twitter_id)->update(['active_flag' => true]);


                    Session::put('twitter_id', $twitter_id);
                    return redirect('/home');
                //未登録アカウントの場合
                }else{
                    //他のTwitterアカウントでログイン中の場合
                    if(Session::get('twitter_id')){

                        Log::debug('SESSION EXISTS');

                        //ユーザの登録Twitterアカウント数が上限に達している場合、登録せずHOMEに遷移
                        if(Auth::user()->twitterAccounts()->count() >= env('MAX_ACCOUNTS')){
                            Log::debug('ACCOUNTS NUM OVER');
                            return redirect(RouteServiceProvider::HOME);
                        }

                        Auth::user()->twitterAccounts()
                                    ->where('active_flag', true)
                                    ->update(['active_flag' => false]);

                        $twitter_account = TwitterAccount::create([
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

                        Session::put('twitter_id', $twitter_id);
                    //Twitterアカウント初登録のユーザの場合（ユーザ登録されていない場合）
                    }else{
                        // //usersレコードを生成
                        //$user = User::create(['active_twitter_id' => $twitter_id]);
                        $user = User::create();
                        event(new Registered($user));

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
                        Auth::login($user);
                        Session::put('twitter_id', $twitter_id);
                    }

                }


                return redirect('/home');
            }else{
                return redirect('/login');
            }



        }else{
            return redirect('/login');
        }


    }

    //Twitterアカウントのデータを削除
    public function destroy(string $id)
    {
        Log::debug('DELETE MY ACCOUT');

        $user = Auth::user()->twitterAccounts()->find($id)->toArray();
        //idが自身のTwitterアカウントのものでなければ終了
        if(!$user){
            return false;
        }

        //Twitterアカウントに紐づく全テーブル(usersテーブル以外)の全レコード削除
        try{
            FollowKeyword::where('twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-FOLLOW KEYWORD ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            LikeKeyword::where('twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-LIKE KEYWORD ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            FollowedAccount::where('user_twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-FOLLOWED ACCOUNT ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            FollowTarget::where('user_twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-FOLLOW TARGET ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            LikeTarget::where('user_twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-LIKE TARGET ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            UnfollowTarget::where('user_twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-UNFOLLOW TARGET ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            TargetBaseAccount::where('user_twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-TARGET BASE ACCOUNT ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            ProtectedFollowedAccount::where('user_twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-PROTECTED FOLLOWED ACCOUNT ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            ReservedTweet::where('twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-RESERVED TWEET ' .(string)$id .' : ' .$e->getMessage());
        }
        try{
            TwitterAccountData::where('twitter_id', $id)->forceDelete();
        } catch(\Exception $e){
            Log::notice('WITHDRAW-TWITTER ACCOUNT DATA ' .(string)$id .' : ' .$e->getMessage());
        }

        $result = Auth::user()->twitterAccounts()->find($id)->forceDelete();
        Log::debug('DELETE MY ACCOUT : ' .print_r($result, true));
    }
}
