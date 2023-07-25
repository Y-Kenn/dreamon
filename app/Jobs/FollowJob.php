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

    /**
     * Create a new job instance.
     */
    public function __construct($record_id, $user_twitter_id, $target_twitter_id)//, $last_active_time
    {
        $this->record_id = $record_id;
        $this->user_twitter_id = $user_twitter_id;
        $this->target_twitter_id = $target_twitter_id;
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

        $TwitterApi->setTokenToHeader($access_token);

        $follow_target_builder = FollowTarget::find($this->record_id);

        $result = $TwitterApi->follow($this->user_twitter_id, $this->target_twitter_id);
        //Log::debug('FOLLOW JOB RESULT : ' . print_r($result, true));
        if(isset($result['data'])){
            //フォロー済テーブルに登録
            FollowedAccount::create([
                'user_twitter_id' => $this->user_twitter_id,
                'target_twitter_id' => $this->target_twitter_id,
                'followed_at' => date("Y/m/d H:i:s"),
            ]);
            Log::debug('FOLLOW JOB : SUCCESS--');
        }else{
            Log::debug('FOLLOW JOB : FAILED-- : ' .print_r($result, true));
        }
    }
}
