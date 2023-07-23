<?php

namespace App\Jobs;

use App\Library\DBErrorHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TwitterAccount;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\LikeTarget;

class LikeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_twitter_id;
    protected $record_id;
    protected $target_tweet_id;

    /**
     * Create a new job instance.
     */
    public function __construct($record_id, $user_twitter_id, $target_tweet_id)
    {
        $this->record_id = $record_id;
        $this->user_twitter_id = $user_twitter_id;
        $this->target_tweet_id = $target_tweet_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Twitter API の自動化検出対策
        sleep(env('LIKE_INTERVAL'));

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->like($this->user_twitter_id, $this->target_tweet_id);
        if(isset($result['data'])){
            try{
                DB::transaction(function () {
                    $result = LikeTarget::find($this->record_id)->update(['liked_at' => date("Y/m/d H:i:s")]);
                    Log::debug('LIKE JOB : SUCCESS--');
                    DBErrorHandler::checkUpdated($result);
                });
            } catch (\Throwable $e) {
                Log::error('[ERROR] LIKE JOB : ' . print_r($e->getMessage(), true));
            }
        }else{
            Log::debug('LIKE JOB : FAILED-- : ' .print_r($result, true));
        }
    }
}
