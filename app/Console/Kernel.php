<?php

namespace App\Console;

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
        // $schedule->call(function () {
        //     Log::debug('SCHEDULER*********************');
        // })->everyMinute();

        //ジョブチェーンのキュー投入(フォロー・ライク・アンフォロー)
        $schedule->command('throw-chains')->everyMinute();//everyFiveMinutes();
        //予約ツイートジョブのキュー投入(予約ツイートのジョブは最優先にする)
        $schedule->command('throw-tweets')->everyMinute();
        //followed_accounts table の更新
        $schedule->command('throw-update-following')->hourly();//->everyThreeMinutes();
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
