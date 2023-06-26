<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Library\TwitterApi;
use App\Models\TwitterAccount;
use App\Models\FollowedAccount;
use Illuminate\Support\Facades\Log;

class UpdateFollowedAccountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_twitter_id;
    /**
     * Create a new job instance.
     */
    public function __construct($user_twitter_id)
    {
        $this->user_twitter_id = $user_twitter_id;
    }

    //Twitter APIで取得したフォローアカウントとDBを比較し、DBに未登録のアカウントのリストを返却
    protected function filterUnregisteredFollowings($followings_from_api, $followed_accounts)
    {
        $unregistered_followings = array();
        foreach($followings_from_api as $following){
            $allready_followed_flag = false;
            foreach($followed_accounts as $account){
                if($following['id'] === $account['target_twitter_id']){
                    $allready_followed_flag = true;
                    break;
                }
            }
            if(!$allready_followed_flag){
                array_push($unregistered_followings, $following);
            }
        }
        
        return $unregistered_followings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                        env('API_SECRET'), 
                                        env('BEARER'), 
                                        env('CLIENT_ID'), 
                                        env('CLIENT_SECRET'), 
                                        env('REDIRECT_URI'));
        
        $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        //更新日時が古い順にソートしてDBからフォロー済アカウントを取得
        $followed_accounts_builder = FollowedAccount::where('user_twitter_id', $this->user_twitter_id)
                                                        ->whereNull('unfollowed_at')
                                                        ->orderBy('updated_at', 'asc');
        $followed_accounts = $followed_accounts_builder->get();
        // Log::debug('FOLLOWED ACCOUNTS: ' .print_r($followed_accounts->toArray(), true));
        
        #####################################################################
        #手動登録されたアカウントを検出し、DB(followed_accounts_table)へ登録する処理
        #####################################################################

        $unregistered_followings = array();
        //APIでフォローアカウント取得
        $followings = $TwitterApi->getFollowings($this->user_twitter_id);
        //アカウント凍結を検出
        $TwitterApi->checkAccountLocked($followings, $this->user_twitter_id);

        if(isset($followings['data'])){
            
            if($followed_accounts_builder->exists()){
                $followed_accounts = $followed_accounts_builder->get();
                //Twitter APIで取得したフォローアカウントとDBを比較し、DBに未登録のアカウントのリストを抽出
                $unregistered_followings = $this->filterUnregisteredFollowings($followings['data'], $followed_accounts);
            }else{
                $unregistered_followings = $followings['data'];
            }
        }
        
        if(!empty($unregistered_followings)){
            // foreach($unregistered_followings as $following){
            //     //非アクティブ期間のチェック
            //     $last_active_at = $TwitterApi->checkLastActiveTime($following['id']);
            //     //checkLastActiveTime()が取得失敗した場合、$followingsのループ終了
            //     if($last_active_at === strtotime("1980-01-01")){
            //         Log::debug('API LIMIT - REGIST NEW FOLLOWING');
            //         break;
            //     //checkLastActiveTime()が取得できた場合
            //     }else{
            //         FollowedAccount::create([
            //         'user_twitter_id' => $this->user_twitter_id,
            //         'target_twitter_id' => $following['id'],
            //         'followed_at' => date("Y/m/d H:i:s"),
            //         'last_active_at' => date("Y/m/d H:i:s", $last_active_at),
            //         'manual_followed_flag' => true,
            //         ]);
            //         Log::debug('REGIST : ' . print_r($following['id'], true));
            //     }
            // }
            foreach($unregistered_followings as $following){
                FollowedAccount::create([
                            'user_twitter_id' => $this->user_twitter_id,
                            'target_twitter_id' => $following['id'],
                            'followed_at' => date("Y/m/d H:i:s"),
                            'manual_followed_flag' => true,
                            ]);
            }
        }

        #####################################################################
        #followed_accounts_tableの全ターゲットのlast_active_atを更新する処理
        #####################################################################
        
        if($followed_accounts_builder->exists()){
            foreach($followed_accounts as $account){
                $last_active_at = $TwitterApi->checkLastActiveTime($account['target_twitter_id']);
                //checkLastActiveTime()が取得失敗した場合、$followingsのループ終了
                //TwitterのBasicプランで最大連続15回
                if($last_active_at === strtotime("1980-01-01")){
                    Log::debug('API LIMIT - UPDATE LAST ACTIVE');
                    break;
                //checkLastActiveTime()が取得できた場合
                }else{
                    Log::debug('UPDADTED : ' .print_r($account['id'], true));
                    FollowedAccount::find($account['id'])
                    ->update(['last_active_at' => date("Y/m/d H:i:s", $last_active_at)]);
                }
            }
        }
    }
}
