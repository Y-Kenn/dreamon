<?php

namespace App\Console\Commands;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Log;
use App\Models\TwitterAccount;
use App\Models\ReservedTweet;
use App\Jobs\TweetJob;
use DateTime;

use Illuminate\Console\Command;

class ThrowTweetJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw-tweets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate reserved tweet jobs for each Twitter account';

    /**
     * Execute the console command.
     */
    //各Twitterアカウントの予約ツイートをツイートするジョブの発行
    public function handle()
    {

        $reserved_tweets_builder = TwitterAccount::whereNull('twitter_accounts.deleted_at')
                                                    ->where('locked_flag', false)
                                                    ->join('reserved_tweets', function (JoinClause $join){
                                                        $join->on('twitter_accounts.twitter_id', '=', 'reserved_tweets.twitter_id')
                                                            ->where('reserved_tweets.deleted_at', '=', null);
                                                    })->whereNull('thrown_at')
                                                    ->inRandomOrder();//特定のアカウントのツイートが毎回遅延することを回避;
        //予約ツイートが無い場合は終了
        if(!$reserved_tweets_builder->exists()){
            Log::debug('NO RESERVED TWEET');
            return;
        }


        $reserved_tweets = $reserved_tweets_builder->get();
        foreach($reserved_tweets as $tweet){
            $reserved_datetime = new DateTime($tweet['reserved_date']);
            $now = new DateTime();
            $diff = $now->diff($reserved_datetime);
            //結果がマイナスの場合(投稿予定時間になっている場合)ツイートジョブ発行
            if($diff->invert){
                TweetJob::dispatch($tweet['id'], $tweet['twitter_id'], $tweet['text'])
                            ->onQueue('reserved_tweets');
                ReservedTweet::find($tweet['id'])->update(['thrown_at' => date("Y/m/d H:i:s")]);
            }
        }
    }
}
