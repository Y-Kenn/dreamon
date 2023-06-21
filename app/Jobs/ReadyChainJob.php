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

class ReadyChainJob implements ShouldQueue
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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        TwitterAccount::find($this->user_twitter_id)
                        ->update(['last_chain_at' => date("Y/m/d H:i:s"),
                                    'waiting_chain_flag' => false]);
        Log::debug('READY CHAIN JOB');
    }
}
