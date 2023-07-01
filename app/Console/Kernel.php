<?php

namespace App\Console;

use App\Library\TwitterApi;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Console\Commands\ThrowJobChainsCommand;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        //ジョブチェーンのキュー投入(フォロー・ライク・アンフォロー)
        $schedule->command('throw-chains')->everyMinute();

        //予約ツイートジョブのキュー投入(予約ツイートのジョブは最優先にする)
        $schedule->command('throw-tweets')->everyMinute();

        /*******************************
        *TwitterApi一部削除により機能停止中*
        ********************************/
        //followed_accounts table の更新
        //$schedule->command('throw-update-following')->hourly();

        //ジョブチェーン処理失敗でジョブチェーン処理中のフラグが経ち続けるスタックを解消
        $schedule->command('check-stuck-accounts')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

}
