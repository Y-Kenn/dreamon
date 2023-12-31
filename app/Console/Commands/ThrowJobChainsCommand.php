<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TwitterAccount;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use App\Jobs\GenerateChainJob;
use mysql_xdevapi\Exception;

class ThrowJobChainsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw-chains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate job chains for each Twitter account';

    /**
     * Execute the console command.
     */
    //各Twitterアカウントのジョブチェーンを発行
    public function handle()
    {
        try {
            $twitter_accounts = TwitterAccount::whereNull('deleted_at')
                                                ->where('waiting_chain_flag', false)
                                                ->where('locked_flag', false)
                                                ->get()->toArray();
        } catch (\Throwable $e) {
            Log::error('[ERROR] THROW JOB CHAINS COMMAND - READ : ' . print_r($e->getMessage(), true));

            return false;
        }

        //ジョブチェーン発行可能なアカウントがない場合は終了
        if(empty($twitter_accounts)){
            Log::debug('ALL TWITTER ACCOUNTS BUSY');
            return;
        }

        foreach($twitter_accounts as $account){
            $last_chain_generated_time = strtotime($account['last_chain_at']);
            $now = time();
            //前回のジョブチェーン発行から指定時間経過している場合、ジョブチェーン発行
            if($now - $last_chain_generated_time > env('JOB_CHAIN_INTERVAL')){
                GenerateChainJob::dispatch($account['twitter_id']);
                Log::debug('GENERATE CHAINS : ' . print_r($account['twitter_id'], true));
            }else{
                Log::debug('UNDER JOB_CHAIN_INTERVAL : ' . print_r($account['twitter_id'], true));
            }
        }
    }
}
