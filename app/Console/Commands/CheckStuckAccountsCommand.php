<?php

namespace App\Console\Commands;

use App\Library\DBErrorHandler;
use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TwitterAccount;
use mysql_xdevapi\Exception;

//何かしらの不具合でジョブチェーンが異常終了、または未実行で、twitter_accounsテーブルの
//waiting_chain_flagがtrueのままとなり、次のジョブチェーンを発行できないアカウントを
//ジョブチェーン発行から一定時間経過している場合はwaiting_chain_flagをfalseに戻し救出する
class CheckStuckAccountsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-stuck-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rescue accounts stuck due to abnormal termination of the job chain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::debug('START CHECK SUCK ACCOUNTS');
        try{
            $waiting_chain_accounts = TwitterAccount::where('waiting_chain_flag', true)
                                                    ->get()->toArray();
        } catch (\Throwable $e) {
            Log::error('[ERROR] CHECK STUCK ACCOUNTS COMMAND - READ : ' . print_r($e->getMessage(), true));

            return false;
        }

        $now = time();
        foreach ($waiting_chain_accounts as $account){
            if($now - strtotime($account['last_chain_at']) > 60*45 ){
                try {
                    DB::transaction(function () use($account){
                        $result = TwitterAccount::find($account['twitter_id'])->update([
                            'waiting_chain_flag' => false,
                        ]);
                        DBErrorHandler::checkUpdated($result);
                        Log::notice('RESCUE STUCK ACCOUNT : ' .print_r($account['twitter_id'], true));
                    });
                }catch (\Throwable $e){
                    Log::error('[ERROR] CHECK STUCK ACCOUNTS COMMAND - UPDATE : ' .print_r($e->getMessage(), true));

                    return false;
                }
            }
        }
    }
}
