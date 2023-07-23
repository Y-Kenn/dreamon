<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TwitterAccount;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use App\Jobs\UpdateFollowedAccountsJob;

class ThrowUpdateFollowingJobsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw-update-following';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate update followed accounts table jobs for each Twitter account';

    /**
     * Execute the console command.
     */

    //各Twitterアカウントのフォロー中のアカウント(followed_accountテーブル)の情報更新ジョブを発行
    public function handle()
    {
        try {
            $twitter_accounts = TwitterAccount::whereNull('deleted_at')
                                                        ->where('locked_flag', false)
                                                        ->get()->toArray();
        } catch (\Throwable $e) {
            Log::error('[ERROR] THROW UPDATE FOLLOWING JOBS COMMAND - READ : ' . print_r($e->getMessage(), true));

            return false;
        }

        //アカウントがない場合は終了
        if(empty($twitter_accounts)){
            Log::debug('NO ACCOUNT');
            return;
        }

        foreach($twitter_accounts as $account){
            UpdateFollowedAccountsJob::dispatch($account['twitter_id']);
            Log::debug('GENERATE UPDATE JOB : ' . print_r($account['twitter_id'], true));
        }
    }
}
