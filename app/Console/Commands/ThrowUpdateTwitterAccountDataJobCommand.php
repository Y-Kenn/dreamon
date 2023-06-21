<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TwitterAccount;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use App\Jobs\UpdateTwitterAccountDataJob;

class ThrowUpdateTwitterAccountDataJobCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'throw-update-twitter-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate update twitter account data jobs for each Twitter account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $twitter_accounts_builder = TwitterAccount::whereNull('deleted_at')
                                                    ->where('locked_flag', false);
        //アカウントがない場合は終了
        if(!$twitter_accounts_builder->exists()){
            Log::debug('NO ACCOUNT');
            return;
        }

        $twitter_accounts = $twitter_accounts_builder->get();
        foreach($twitter_accounts as $account){
            UpdateTwitterAccountDataJob::dispatch($account['twitter_id']);
            Log::debug('Throw UpdateTwitterAccountDataJob : ' . print_r($account['twitter_id'], true));
        }
    }
}
