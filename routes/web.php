<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TestController;

use App\Http\Controllers\FollowKeywordsController;
use App\Http\Controllers\LikeKeywordsController;
use App\Http\Controllers\TargetBaseController;
use App\Http\Controllers\AutoFollowController;
use App\Http\Controllers\AutoUnfollowController;
use App\Http\Controllers\AutoLikeController;
use App\Http\Controllers\ProtectedAccountController;
use App\Http\Controllers\ReservedTweetController;
use App\Http\Controllers\TweetedTweetController;
use App\Http\Controllers\ChangeAccountController;
use App\Http\Controllers\TwitterRegisterController;
use App\Http\Controllers\ProcessStatusController;
use App\Http\Controllers\TwitterAccountDataController;
use App\Http\Controllers\MentionTweetController;
use App\Http\Controllers\EmailAddressController;
use App\Http\Controllers\RegistPasswordController;
use App\Http\Controllers\LockedController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::middleware('auth')->group(function () {
    Route::apiResource('/follow-keywords',FollowKeywordsController::class);
    Route::apiResource('/like-keywords',LikeKeywordsController::class);
    Route::apiResource('/target-base',TargetBaseController::class);
    Route::apiResource('/auto-follow',AutoFollowController::class);
    Route::apiResource('/auto-unfollow',AutoUnfollowController::class);
    Route::apiResource('/auto-like',AutoLikeController::class);
    Route::apiResource('/protected-account',ProtectedAccountController::class);
    Route::apiResource('/reserved-tweet',ReservedTweetController::class);
    Route::apiResource('/tweeted-tweet',TweetedTweetController::class);
    Route::apiResource('/change-account',ChangeAccountController::class);
    Route::apiResource('/process-status',ProcessStatusController::class);
    Route::apiResource('/twitter-data',TwitterAccountDataController::class);
    Route::apiResource('/mention',MentionTweetController::class);
    Route::apiResource('/email',EmailAddressController::class);
    Route::apiResource('/regist-password',RegistPasswordController::class);
    Route::apiResource('/locked',LockedController::class);
    Route::apiResource('/withdraw-user',WithdrawController::class);

    Route::get('/home', function () {return view('app');});
    Route::get('/follow', function () { return view('app'); });
    Route::get('/unfollow', function () { return view('app'); });
    Route::get('/like', function () { return view('app'); });
    Route::get('/tweet', function () { return view('app'); });
    Route::get('/twitter-account', function () { return view('app'); });
    Route::get('/setting', function () { return view('app'); });
    Route::get('/withdraw', function () { return view('app'); });
});

Route::get('/', function () {return view('pages/top');})->name('top');
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('terms', function () {return view('pages/terms');})->name('terms');
Route::get('privacy-policy', function () {return view('pages/privacy-policy');})->name('privacy-policy');

Route::get('/loading', function () {return view('loading');});

//Twitter OAuthによる認証
Route::post('/twitter-register', [TwitterRegisterController::class, 'store'])->name('twitter-register.store');

Route::get('/temp/test',[TestController::class, 'test'])
           ->name('test');


require __DIR__.'/auth.php';
