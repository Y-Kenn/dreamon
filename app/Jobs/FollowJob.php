<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TwitterAccount;
use App\Models\FollowTarget;
use App\Models\FollowedAccount;
use Illuminate\Support\Facades\Log;
use App\Library\TwitterApi;

class FollowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record_id;
    protected $user_twitter_id;
    protected $target_twitter_id;
    // protected $last_active_time;

    /**
     * Create a new job instance.
     */
    public function __construct($record_id, $user_twitter_id, $target_twitter_id)//, $last_active_time
    {
        $this->record_id = $record_id;
        $this->user_twitter_id = $user_twitter_id;
        $this->target_twitter_id = $target_twitter_id;
        // $this->last_active_time = $last_active_time;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Twitter API の自動化検出対策
        sleep(env('FOLLOW_INTERVAL'));

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));

        $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);

        // $twitter_account_info = TwitterAccount::find($this->user_twitter_id);
        // $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);

        $follow_target_builder = FollowTarget::find($this->record_id);


        // $last_active_time = $TwitterApi->checkLastActiveTime($this->target_twitter_id);
        // $now = time();
        // //非アクティブ期間が指定期間以上であれば、フォローせずにジョブ終了
        // if($now - $last_active_time > env('INACTIVE_BASELINE_UNFOLLOW')){
        //     $follow_target_builder->forceDelete();
        //     Log::debug('DELETE FOLLOW TARGET RECORD [ INACTIVE ] : ' . print_r($this->record_id, true));
        //     return;
        // }

        $result = $TwitterApi->follow($this->user_twitter_id, $this->target_twitter_id);
        //Log::debug('FOLLOW JOB RESULT : ' . print_r($result, true));
        if(isset($result['data'])){
            //フォロー済テーブルに登録
            FollowedAccount::create([
                'user_twitter_id' => $this->user_twitter_id,
                'target_twitter_id' => $this->target_twitter_id,
                'followed_at' => date("Y/m/d H:i:s"),
                // 'last_active_at' => date("Y/m/d H:i:s", $this->last_active_time),
            ]);
            Log::debug('FOLLOW JOB : SUCCESS--');
        }else{
            Log::debug('FOLLOW JOB : FAILED-- : ' .print_r($result, true));
        }
    }
}
