<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\TwitterAccount;
use App\Models\TargetBaseAccount;
use App\Models\FollowKeyword;
use App\Models\FollowTarget;
use App\Models\LikeKeyword;
use App\Models\LikeTarget;
use App\Models\FollowedAccount;
use App\Models\ProtectedFollowedAccount;
use App\Models\UnfollowTarget;
use App\Models\TwitterAccountData;
use App\Library\TwitterApi;
use App\Jobs\ReadyChinjob;
use App\Jobs\FollowJob;
use App\Jobs\LikeJob;
use App\Jobs\UnfollowJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\FinishFollowMail;




class GenerateChainJob implements ShouldQueue
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

    //DBレコードからキーワード配列へ整形
    protected function makeKeywordsList($keywords_records)
    {
        $keywords_list = array();
        foreach($keywords_records as $one_record){
            array_push($keywords_list, mb_split(' ', $one_record['keywords']));
        }

        return $keywords_list;
    }

    ////////////////////////////////
    //フォローターゲット用メソッド
    ////////////////////////////////

    //24時間以内にフォローした数を計算
    protected function countFollowedNum($span){
        $followed_accounts_builder = FollowedAccount::where('user_twitter_id', $this->user_twitter_id)
                                                    ->whereNotNull('followed_at');
        $followed_accounts = $followed_accounts_builder->get();

        $now = time();
        $followed_num = 0;
        if($followed_accounts_builder->exists()){
            foreach($followed_accounts as $followed_account){
                //24時間以内にアンフォローされている場合
                if($now - strtotime($followed_account['followed_at']) < $span){
                    $followed_num++;
                }
            }
        }

        return $followed_num;
    }

    //検索キーワードにマッチするアカウントのリストを作成
    protected function makeSearchedFollowTarget($search_keyword_list, $account_list)
    {
        $searched_follow_targets = array();
        foreach($account_list as $account){
            //各キーワードセット適用
            //いずれかのキーワードセットにヒットすれば抽出(OR)
            foreach($search_keyword_list as $keywords){
                $unmatch_flg = false;//AND判定用
                //キーワードセットの各キーワード適用
                foreach($keywords as $keyword){
                    //キーワードにアンマッチの場合
                    if(mb_strpos($account['description'], $keyword) === false){
                        $unmatch_flg = true;
                        break;
                    }
                }
                //AND判定(一つのキーワードセット内で一度もアンマッチが発生しなかった場合)
                if(!$unmatch_flg){
                    array_push($searched_follow_targets, [
                        'target_twitter_id' => $account['id'],
                        'description' => $account['description'],
                    ]);
                    break;
                }
            }
        }

        return $searched_follow_targets;
    }

    //除外キーワードにマッチするアカウントをリストから削除
    protected function keywordExcludeTarget($exclude_keyword_list, $searched_follow_targets)
    {
        $excluded_follow_targets = array();
        foreach($searched_follow_targets as $follow_target){
            $unmatch_keywords_list_flg = true;//各キーワードセット(１レコード分のキーワード)のいずれかでも完全マッチするとfalseとなる(除外)
            foreach($exclude_keyword_list as $keywords){
                $unmatch_keywords_set_flg = false;//キーワードセット(１レコード分のキーワード)内で１回でもアンマッチが発生すればtrueになる
                foreach($keywords as $keyword){
                    //除外キーワードとアンマッチだった場合
                    if(mb_strpos($follow_target['description'], $keyword) === false){
                        $unmatch_keywords_set_flg = true;
                        break;
                    }
                }
                $unmatch_keywords_list_flg &= $unmatch_keywords_set_flg;
            }
            //除外キーワードにアンマッチだったターゲットアカウントはフォローターゲットリストに追加
            if($unmatch_keywords_list_flg){
                array_push($excluded_follow_targets, [
                    'target_twitter_id' => $follow_target['target_twitter_id'],
                    'description' => $follow_target['description'],
                ]);
            }
        }

        return $excluded_follow_targets;
    }

    //過去にフォローしたことがあるターゲットを除外(フォロー中、アンフォロー済を除外)
    protected function excludeFollowedTarget($keyword_excluded_targets)
    {
        $followed_accounts = FollowedAccount::where('user_twitter_account', $this->user_twitter_id)->get();

        $followed_excluded_target = array();

        foreach($keyword_excluded_targets as $target){
            $match_flag = false;
            foreach($followed_accounts as $followed_account){
                if($target['id'] === $followed_account['target_twitter_id']){
                    $match_flag = true;
                    break;
                }
            }
            if(!$match_flag){
                array_push($followed_excluded_target, $target);
            }
        }

        return $followed_excluded_target;
    }

    //フォローターゲットをTwitter API から生成
    protected function searchFollowTarget($target_base_id)
    {
        //フォロー検索用のキーワードを取得
        $search_keywords_builder = FollowKeyword::where('twitter_id', $this->user_twitter_id)
                                                ->where('not_flag', false);

        //フォロー用の検索キーワードがDBにあればフォローターゲット生成
        if($search_keywords_builder->exists()){

            $search_keywords_list = $this->makeKeywordsList($search_keywords_builder->get());
            //フォローターゲットから除外するためのキーワードを取得
            $exclude_keywords_builder = FollowKeyword::where('twitter_id', $this->user_twitter_id)
                                                ->where('not_flag', true);
            $exclude_keywords_list = $this->makeKeywordsList($exclude_keywords_builder->get());

            $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));

            $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
            $TwitterApi->setTokenToHeader($access_token);
            //ターゲットベースアカウントのフォロワーを取得
            $account_list = $TwitterApi->getFollowers($target_base_id);
            //Twitter APIのレスポンスが正常だった場合、フォローターゲットDB登録
            if(isset($account_list['data'])){
                //検索キーワードにマッチするアカウントを絞り込む
                $searched_follow_targets = $this->makeSearchedFollowTarget($search_keywords_list, $account_list['data']);
                //除外キーワードにマッチするアカウントを除外
                $keyword_excluded_target = $this->keywordExcludeTarget($exclude_keywords_list, $searched_follow_targets);

                if(!empty($keyword_excluded_target)){

                    return $keyword_excluded_target;
                }
            }
        }else{
            Log::debug('DOESNT EXIST');
        }

        return array();
    }

    //フォロワーターゲット生成メソッド
    protected function generateFollowTarget()
    {
        $follow_targets_builder = FollowTarget::where('user_twitter_id', $this->user_twitter_id)
                                                ->whereNull('thrown_at');

        //フォローターゲットの残数が指定値を下回っている場合
        if($follow_targets_builder->count() < env('FOLLOW_TARGET_THRESHOLD')){
            Log::debug('START GENERATE FOLLOW TARGET');
            //現在フォロー中のアカウントのベースアカウントのステータスを完了状態にする
            TargetBaseAccount::where('user_twitter_id', $this->user_twitter_id)
                                    ->whereNotNull('started_at')
                                    ->update(['completed_at' => date("Y/m/d H:i:s")]);
            $target_base_builder = TargetBaseAccount::where('user_twitter_id', $this->user_twitter_id)
                                                            ->whereNull('started_at');
            //ターゲットベース(フォローターゲット生成元のアカウント)がDB登録されている場合
            if($target_base_builder->exists()){

                $target_base = $target_base_builder->first();

                //ターゲットのリストを生成
                $new_targets = $this->searchFollowTarget($target_base['base_twitter_id']);
                //フォローターゲットを生成したターゲットベースをフォロー処理中のステータスへ更新
                $target_base->update(['started_at' => date("Y/m/d H:i:s")]);
                Log::debug('NEW FOLLOW TARGETS : ' . print_r($new_targets, true));
                if(!empty($new_targets)){

                    foreach($new_targets as $target){
                        FollowTarget::create([
                            'target_base_id' => $target_base['id'],
                            'user_twitter_id' => $this->user_twitter_id,
                            'target_twitter_id' => $target['target_twitter_id'],
                        ]);
                    }
                }
            //ベースアカウントが空の場合は自動フォロー終了、完了通知メール送信
            }elseif(!$target_base_builder->exists() && $follow_targets_builder->count() === 0){
                TwitterAccount::where('twitter_id', $this->user_twitter_id)
                                ->update(['following_flag' => false]);
                Log::debug('AUTO FOLLOW FINISHED : ' . print_r($this->user_twitter_id, true));
                $user = TwitterAccount::find($this->user_twitter_id)->user()->first();
                Log::debug('USER : ' . print_r($user, true));
                Mail::send(new FinishFollowMail($user));
            }
        }
    }

    ////////////////////////////////
    //ライクターゲット用メソッド
    ////////////////////////////////

    //指定時間以内にいいねした数を計算
    protected function countLikedNum($span){
        $liked_targets_builder = LikeTarget::where('user_twitter_id', $this->user_twitter_id)
                                                    ->whereNotNull('liked_at');
        $liked_targets = $liked_targets_builder->get();

        $now = time();
        $liked_num = 0;
        if($liked_targets_builder->exists()){
            foreach($liked_targets as $liked_target){
                //24時間以内にいいねしている場合
                if($now - strtotime($liked_target['liked_at']) < $span){
                    $liked_num++;
                }
            }
        }

        return $liked_num;
    }

    //DBレコードからTwitter API 用のクエリを生成
    protected function makeSearchQuery($search_keywords_list, $exclude_keywords_list)
    {
        $query = '(';
        $keyword_list_length = count($search_keywords_list);
        for($i = 0; $i < $keyword_list_length; $i++){
            $str = '(';
            foreach($search_keywords_list[$i] as $keyword){
                $str = $str .$keyword .' ';
            }
            //検索キーワードセットの間に'OR'挿入、最後のキーワードセットの後ろには挿入しない
            ($i === $keyword_list_length - 1) ? $str = $str .') ' : $str = $str .') OR ';
            $query = $query .$str;
        }
        $query = $query .') ';

        foreach($exclude_keywords_list as $keywords){
            $str = '-(';
            foreach($keywords as $keyword){
                $str = $str .$keyword .' ';
            }
            $str = $str .') ';
            $query = $query .$str;
        }
        $query = $query .'-is:retweet -is:reply lang:ja';//リツイート,リプライ除外、日本語に限定

        return $query;
    }

    protected function searchLikeTarget()
    {
        //ツイート検索用のキーワードを取得
        $search_keywords_builder = LikeKeyword::where('twitter_id', $this->user_twitter_id)
                                                ->where('not_flag', false);

        //検索キーワードがDBにあればフォローターゲット生成
        if($search_keywords_builder->exists()){
            $search_keywords_list = $this->makeKeywordsList($search_keywords_builder->get());
            //ターゲットから除外するためのキーワードを取得
            $exclude_keywords_builder = LikeKeyword::where('twitter_id', $this->user_twitter_id)
                                                ->where('not_flag', true);
            $exclude_keywords_list = $this->makeKeywordsList($exclude_keywords_builder->get());

            $search_query = $this->makeSearchQuery($search_keywords_list, $exclude_keywords_list);
            Log::debug('SEARCH QUERY : ' . print_r($search_query, true));
            $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
            $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
            $TwitterApi->setTokenToHeader($access_token);
            $start_time = date("Y/m/d H:i:s", time() - env('SEARCH_TWEETS_INTERVAL'));
            $start_time_twitter = $TwitterApi->toTwitterTime($start_time);
            $tweet_list = $TwitterApi->searchTweets($search_query, $start_time_twitter, env('GET_LIKE_TARGET_NUM'));
            if(isset($tweet_list['data'])){
                return array_column($tweet_list['data'], 'id');
            }else{
                return array();
            }

        }
    }

    //ライクターゲット生成メソッド
    protected function generateLikeTarget(){
        $like_targets_builder = LikeTarget::where('user_twitter_id', $this->user_twitter_id)
                                            ->whereNull('thrown_at');

        if($like_targets_builder->count() < env('LIKE_TARGET_THRESHOLD')){
            Log::debug('START GENERATE LIKE TARGET');
            $new_targets = $this->searchLikeTarget();
            Log::debug('NEW LIKE TARGETS : ' . print_r($new_targets, true));

            if(!empty($new_targets)){

                foreach($new_targets as $target){
                    LikeTarget::create([
                        'user_twitter_id' => $this->user_twitter_id,
                        'target_tweet_id' => $target,
                    ]);
                }
            }
        }
    }

    ////////////////////////////////
    //アンフォローターゲット用メソッド
    ////////////////////////////////

    //指定時間以内にアンフォローした数を計算
    protected function countUnfollowedNum($span){
        $unfollowed_accounts_builder = FollowedAccount::where('user_twitter_id', $this->user_twitter_id)
                                                    ->whereNotNull('unfollowed_at');
        $unfollowed_accounts = $unfollowed_accounts_builder->get();

        $now = time();
        $unfollowed_num = 0;
        if($unfollowed_accounts_builder->exists()){
            foreach($unfollowed_accounts as $unfollowed_account){
                //24時間以内にアンフォローされている場合
                if($now - strtotime($unfollowed_account['unfollowed_at']) < $span){
                    $unfollowed_num++;
                }
            }
        }

        return $unfollowed_num;
    }

    //フォロー保護アカウントか確認(protected_followed_accounts_tableに登録済のアカウント)
    protected function checkProtectedAccount($target_twitter_id, $protected_account_list){

        foreach($protected_account_list as $protected_account){
            if($target_twitter_id === $protected_account['protected_twitter_id']){
                //保護アカウント
                return true;
            }
        }
        //保護アカウントでない
        return false;
    }

    //相手にフォローされているかチェック、されている場合は true、ない場合は false を返す
    protected function checkFriendship($target_twitter_id){

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                        env('API_SECRET'),
                                        env('BEARER'),
                                        env('CLIENT_ID'),
                                        env('CLIENT_SECRET'),
                                        env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        $target_followings = $TwitterApi->getFollowings($target_twitter_id, false);

        //Twitter APIから正常なレスポンスがあった場合
        if(isset($target_followings['data'])){
            foreach($target_followings['data'] as $target_following){
                if($target_following['id'] === $this->user_twitter_id){
                    //フォローされていた場合
                    return 'friend';
                }
            }
            //フォローされていなかった場合
            return 'stranger';

        //Twitter APIからの異常のレスポンスがあった場合
        }elseif(isset($target_followings['status'])){
            //回数制限エラーの場合
            if($target_followings['status'] === 429){
                return 'limit';
            }
        }
        //その他の異常はフォローされているとして扱う
        return 'friend';
    }

    //アンフォローターゲット生成メソッド
    protected function generateUnfollowTarget(){

        //フォロー中のアカウントをDBから取得
        //->inRandomOrder()でランダムに結果をシャッフル
        //...API制限で$this->checkFriendship()の使用可能回数が少ないため、確率的に満遍なくチェックする
        $followed_accounts_builder = FollowedAccount::where('user_twitter_id', $this->user_twitter_id)
                                                        ->whereNull('unfollowed_at')
                                                        ->inRandomOrder();

        //アンフォロー開始基準のフォロー数より少ない場合、終了
        if($followed_accounts_builder->count() < env('START_BASELINE_UNFOLLOW')){
            Log::debug('UNDER BASELINE UNFOLLOW : ' .print_r($followed_accounts_builder->count(), true));
            return;
        }
        Log::debug('START GENERATE UNFOLLOW TARGET : ' .print_r($this->user_twitter_id, true));

        $now = time();
        //フォロー中のアカウント
        $followed_accounts = $followed_accounts_builder->get();
        //アンフォロー除外登録されたアカウント
        $protected_accounts = ProtectedFollowedAccount::where('user_twitter_id', $this->user_twitter_id)->get();

        $unfollow_targets = array();
        $for_check_friendship = array();

        foreach($followed_accounts as $followed_account){
            ////保護アカウントは除外
            if($this->checkProtectedAccount($followed_account['target_twitter_id'], $protected_accounts)){
                Log::debug('PROTECTED ACCOUNT : ' .print_r($followed_account['target_twitter_id'], true));
                continue;
            }
            //フォローしてから指定期間より短いアカウントは除外
            if($now - strtotime($followed_account['followed_at']) < env('TIME_BASELINE_UNFOLLOW')){
                Log::debug('UNDER FOLLOWING TIME BASELINE ACCOUNT : ' .print_r($followed_account['target_twitter_id'], true));
                continue;
            }
            //非アクティブ期間が指定期間より長い場合はアンフォローターゲットに追加
            if($followed_account['last_active_at'] && strtotime($followed_account['updated_at']) - strtotime($followed_account['last_active_at']) > env('INACTIVE_BASELINE_UNFOLLOW')){
                $target = ['followed_accounts_id' => $followed_account['id'],
                        'target_twitter_id' => $followed_account['target_twitter_id']];
                array_push($unfollow_targets, $target);
                continue;
            //指定期間以外の場合はフォローバック済か確認する用の配列に格納
            }else{
                array_push($for_check_friendship, $followed_account);
            }
            //非アクティブ期間が指定期間より短い場合
            // if($now - strtotime($followed_account['last_active_at']) < env('INACTIVE_BASELINE_UNFOLLOW')){

            //     $relation = $this->checkFriendship($followed_account['target_twitter_id']);

            //     //相手からフォローされていれば除外
            //     if($relation === 'friend'){
            //         Log::debug('FRIEND ACCOUNT : ' .print_r($followed_account['target_twitter_id'], true));
            //         continue;
            //     //API制限エラーでループ終了
            //     }elseif($relation === 'limit'){
            //         Log::debug('API LIMIT - GET FOLLOWING');
            //         break;
            //     }
            // }
            //ここまで除外されなかったアカウントはアンフォローターゲットへ追加
            // $target = ['followed_accounts_id' => $followed_account['id'],
            //             'target_twitter_id' => $followed_account['target_twitter_id']];
            // array_push($unfollow_targets, $target);
        }

        foreach($for_check_friendship as $followed_account){
            //アンフォローターゲットは生成数を制限
            if(count($unfollow_targets) >= env('UNFOLLOW_TARGET_GENERATE_NUM')){
                break;
            }

            $relation = $this->checkFriendship($followed_account['target_twitter_id']);
            //相手からフォローされていれば除外
            if($relation === 'friend'){
                Log::debug('FRIEND ACCOUNT : ' .print_r($followed_account['target_twitter_id'], true));
                continue;
            //API制限エラーでループ終了
            }elseif($relation === 'limit'){
                Log::debug('API LIMIT - GET FOLLOWING');
                break;
            //その他アンフォロー
            }else{
                $target = ['followed_accounts_id' => $followed_account['id'],
                    'target_twitter_id' => $followed_account['target_twitter_id']];
                array_push($unfollow_targets, $target);
                Log::debug('NOT FRIEND ACCOUNT : ' .print_r($followed_account['target_twitter_id'], true));
            }
        }

        //アンフォローターゲットは生成数を制限
        if(count($unfollow_targets) >= env('UNFOLLOW_TARGET_GENERATE_NUM')){
            $unfollow_targets = array_slice($unfollow_targets, 0, env('UNFOLLOW_TARGET_GENERATE_NUM'));
        }
        Log::debug('UNFOLLOW TARGETS : ' .print_r($unfollow_targets, true));
        //ターゲットのDB登録
        foreach($unfollow_targets as $unfollow_target){
            UnfollowTarget::create([
                'followed_accounts_id' => $unfollow_target['followed_accounts_id'],
                'user_twitter_id' => $this->user_twitter_id,
                'target_twitter_id' => $unfollow_target['target_twitter_id'],
            ]);
        }
    }

    ////////////////////////////////
    //ジョブ生成用メソッド
    ////////////////////////////////

    //フォロージョブリスト生成
    protected function makeFollowJobs()
    {
        $follow_targets_builder = FollowTarget::where('user_twitter_id', $this->user_twitter_id)
                                                ->whereNull('thrown_at')
                                                ->limit(env('CONSECUTIV_FOLLOW_LIMIT'));
        $jobs = array();
        if($follow_targets_builder->exists()){
            $follow_targets = $follow_targets_builder->get();
            foreach($follow_targets as $target){

                //API制限対策とフォロー効率のため機能OFF
                // $TwitterApi = new TwitterApi(env('API_KEY'),
                //                         env('API_SECRET'),
                //                         env('BEARER'),
                //                         env('CLIENT_ID'),
                //                         env('CLIENT_SECRET'),
                //                         env('REDIRECT_URI'));

                // $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
                // $TwitterApi->setTokenToHeader($access_token);


                // $last_active_time = $TwitterApi->checkLastActiveTime($target['target_twitter_id']);
                // //API制限で取得できていなければジョブ生成終了
                // if($last_active_time === strtotime("1980-01-01")){
                //     Log::debug('API LIMIT - CHECK LAST ACTIVE TIME - IN GENERATE CHAIN JOB');
                //     break;
                // }
                // $now = time();
                // //非アクティブ期間が指定期間以上であれば、ジョブ生成せずにターゲットのレコード削除
                // if($now - $last_active_time > env('INACTIVE_BASELINE_UNFOLLOW')){
                //     //$follow_target_builder->forceDelete();
                //     FollowTarget::find($target['id'])->forceDelete();
                //     Log::debug('DELETE FOLLOW TARGET RECORD : ' . print_r($target['id'], true));
                //     continue;
                // }

                // $job = new FollowJob($target['id'], $this->user_twitter_id, $target['target_twitter_id'], $last_active_time);
                $job = new FollowJob($target['id'], $this->user_twitter_id, $target['target_twitter_id']);
                array_push($jobs, $job);
            }
            $follow_targets_builder->update(['thrown_at' => date("Y/m/d H:i:s")]);
        }

        return $jobs;
    }

    //ライクジョブリスト生成
    protected function makeLikeJobs()
    {
        $like_targets_builder = LikeTarget::where('user_twitter_id', $this->user_twitter_id)
                                                ->whereNull('thrown_at')
                                                ->limit(env('CONSECUTIV_LIKE_LIMIT'));
        $jobs = array();
        if($like_targets_builder->exists()){
            $like_targets = $like_targets_builder->get();
            foreach($like_targets as $target){
                $job = new LikeJob($target['id'], $this->user_twitter_id, $target['target_tweet_id']);
                array_push($jobs, $job);
            }
            $like_targets_builder->update(['thrown_at' => date("Y/m/d H:i:s")]);
        }

        return $jobs;
    }

    //アンフォロージョブリスト生成
    protected function makeUnfollowJobs()
    {
        $unfollow_targets_builder = UnfollowTarget::where('user_twitter_id', $this->user_twitter_id)
                                                ->whereNull('thrown_at')
                                                ->limit(env('CONSECUTIV_UNFOLLOW_LIMIT'));
        $jobs = array();
        if($unfollow_targets_builder->exists()){
            $unfollow_targets = $unfollow_targets_builder->get();
            foreach($unfollow_targets as $target){
                $job = new UnfollowJob($target['id'], $target['followed_accounts_id'], $this->user_twitter_id, $target['target_twitter_id']);
                array_push($jobs, $job);
            }
            $unfollow_targets_builder->update(['thrown_at' => date("Y/m/d H:i:s")]);
        }

        return $jobs;
    }

    //各アカウントごとのフォローの24時間上限を計算
    protected function calcFolloLimit24h()
    {
        $data_builder = TwitterAccountData::where('twitter_id', $this->user_twitter_id)
                                    ->latest()
                                    ->select('following', 'followers');

        if(!$data_builder->exists()){
            $follow_limit = 50;
            Log::debug('FOLLOW LIMIT NUM - NO TWITTER DATA : ' .print_r($follow_limit, true));

            return $follow_limit;
        }

        $data = $data_builder->first();
        Log::debug('TWITTER DATA LATEST : ' .print_r($data, true));

        if($data['following'] < 5000 ){
            //各アカウントのフォロワー数から計算したフォロー上限か、Kamitter規定の上限の小さい方を取得
            $follow_limit = (int)min([$data['followers'] * 0.1 + 50, env('DAILY_FOLLOW_LIMIT')]);
        }else{
            //フォローが5000人を超えた場合、フォロー総数の上限をフォロワー総数の1.1倍までとして計算
            $follow_limit = (int)min([$data['followers'] * 1.1 - $data['following'], env('DAILY_FOLLOW_LIMIT')]);
        }
        Log::debug('FOLLOW LIMIT NUM : ' .print_r($follow_limit, true));

        return $follow_limit;
    }

    //ジョブチェーンを生成、ジョブテーブルに投入
    protected function throwJobChain($liking_flag, $following_flag, $unfollowing_flag)
    {
        $liked_num_24h = $this->countlikedNum(60*60*24);
        $folloewd_num_24h = $this->countFollowedNum(60*60*24);
        $unfolloewd_num_24h = $this->countUnfollowedNum(60*60*24);

        $redady_chain_job = [new ReadyChainJob($this->user_twitter_id)];
        $like_jobs = array();
        $follow_jobs = array();
        $unfollow_jobs = array();

        //いいね稼働設定　かつ　今回のいいねを含めて１時間のいいねが指定値を超えない場合 　かつ　1日のいいねが指定値を超えない場合
        if($liking_flag
            && $this->countLikedNum(60*60*1) <= env('HOURLY_LIKE_LIMIT') - env('CONSECUTIV_LIKE_LIMIT')
            && $liked_num_24h <= env('DAILY_LIKE_LIMIT') - env('CONSECUTIV_LIKE_LIMIT'))
        {
            $like_jobs = $this->makeLikeJobs();
        }
        //自動フォロー稼働設定　かつ　今回のフォロー含めて１時間のフォローが指定値を超えない場合　かつ　1日のフォローが指定値が上限を超えない場合
        if($following_flag
            && $this->countFollowedNum(60*60*1) <= env('HOURLY_FOLLOW_LIMIT') - env('CONSECUTIV_FOLLOW_LIMIT')
            && $folloewd_num_24h <= $this->calcFolloLimit24h() - env('CONSECUTIV_FOLLOW_LIMIT'))
        {
            $follow_jobs = $this->makeFollowJobs();
        }
        //自動アンフォロー稼働設定　かつ　今回のアンフォロー含めて１時間のアンフォローが指定値を超えない場合　かつ　1日のアンフォローが指定値が上限を超えない場合
        if($unfollowing_flag
            && $this->countUNFollowedNum(60*60*1) <= env('HOURLY_UNFOLLOW_LIMIT') - env('CONSECUTIV_UNFOLLOW_LIMIT')
            //1日上限算出メソッドはフォロー用のメソッドを流用
            && $unfolloewd_num_24h <= $this->calcFolloLimit24h() - env('CONSECUTIV_UNFOLLOW_LIMIT'))
        {
            $unfollow_jobs = $this->makeUnfollowJobs();
        }

        //全てのジョブが空だった場合はジョブチェーンを発行せず終了
        if(empty($follow_jobs) && empty($like_jobs) && empty($unfollow_jobs)){
            Log::debug('EMPTY ALL JOBS');
            return false;
        }

        $all_jobs = array_merge($redady_chain_job, $like_jobs, $follow_jobs, $unfollow_jobs);

        Bus::dispatchChain($all_jobs);
        TwitterAccount::find($this->user_twitter_id)
                        //ジョブチェーンの実行失敗・異常終了に備えてここでもlast_chain_atを更新しておく
                        ->update(['last_chain_at' => date("Y/m/d H:i:s"),
                                'waiting_chain_flag' => true]);
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $account_status = TwitterAccount::where('twitter_id', $this->user_twitter_id)->first();

        //ライクターゲット生成
        //自動いいねが稼働設定の場合に実施
        if($account_status['liking_flag']){
            $this->generateLikeTarget();
        }else {
            Log::debug('LIKING FLAG : FALSE');
        }

        /*******************************
         *TwitterApi一部削除により機能停止中*
         ********************************/
        //フォローターゲット生成
        //自動フォローが稼働設定の場合に実施
//        if($account_status['following_flag']){
//            $this->generateFollowTarget();
//        }else {
//            Log::debug('FOLLOWING FLAG : FALSE');
//        }

        /*******************************
         *TwitterApi一部削除により機能停止中*
         ********************************/
        //アンフォローターゲット生成
        //自動アンフォローが稼働設定の場合に実施
//        if($account_status['unfollowing_flag']){
//            $this->generateUnfollowTarget();
//        }else {
//            Log::debug('UNFOLLOWING FLAG : FALSE : ' .print_r($this->user_twitter_id, true));
//        }


        //全ての自動機能がオフの場合はジョブチェーンを発行しない
        if($account_status['following_flag'] || $account_status['liking_flag'] || $account_status['unfollowing_flag']){
            Log::debug('THROW JOB CHAIN : ' .print_r($this->user_twitter_id, true));
            $this->throwJobChain($account_status['liking_flag'], $account_status['following_flag'], $account_status['unfollowing_flag']);
        }else{
            Log::debug('ALL WORKING FLAG : FALSE : ' .print_r($this->user_twitter_id, true));
        }

    }
}
