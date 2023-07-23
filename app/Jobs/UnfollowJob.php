<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TwitterAccount;
use App\Models\FollowedAccount;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use App\Models\UnfollowTarget;

class UnfollowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record_id;
    protected $followed_accounts_id;
    protected $user_twitter_id;
    protected $target_twitter_id;

    /**
     * Create a new job instance.
     */
    public function __construct($record_id, $followed_accounts_id, $user_twitter_id, $target_twitter_id)
    {
        $this->record_id = $record_id;
        $this->followed_accounts_id = $followed_accounts_id;
        $this->user_twitter_id = $user_twitter_id;
        $this->target_twitter_id = $target_twitter_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Twitter API の自動化検出対策
        sleep(env('UNFOLLOW_INTERVAL'));

        $TwitterApi = new TwitterApi(env('API_KEY'),
                                    env('API_SECRET'),
                                    env('BEARER'),
                                    env('CLIENT_ID'),
                                    env('CLIENT_SECRET'),
                                    env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken($this->user_twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->unfollow($this->user_twitter_id, $this->target_twitter_id);
        if(isset($result['data']['following'])){
            //アンフォローに成功した場合
            if($result['data']['following'] === false){
                UnfollowTarget::find($this->record_id)
                                ->update(['unfollowed_at' => date("Y/m/d H:i:s")]);
                FollowedAccount::find($this->followed_accounts_id)
                                ->update(['unfollowed_at' => date("Y/m/d H:i:s")]);
                Log::debug('UNFOLLOWED : ' . print_r($this->target_twitter_id, true));

            }
        }else{
            Log::debug('FAILED UNFOLLOW : ' . print_r($this->target_twitter_id, true));
        }
    }
}
