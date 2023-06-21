<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TwitterAccount;
use App\Models\FollowedAccount;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Jobs\GenerateChainJob;
use App\Jobs\UpdateFollowedAccountsJob;
use App\Jobs\UpdateTwitterAccountDataJob;
use Illuminate\Support\Facades\Mail;
use App\Mail\FinishFollowMail;
use App\Mail\LockedNotificationMail;

use DateTime;
use DateTimeZone;

class TestController extends Controller
{
    public function test(){
        // $user = TwitterAccount::find('924353116937392128')
        //                         ->user()->first();
        // Log::debug('TEST : ' . print_r($user->get(), true));
        Mail::send(new LockedNotificationMail('924353116937392128'));
        
        
    }

    public function test14(){
        $user_twitter_id = '1637080537054711808';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $access_token = $TwitterApi->checkRefreshToken($user_twitter_id);
        $TwitterApi->setTokenToHeader($access_token);

        $ids = ['1011955159801344000', '1510243568618983426', '1490694954850865152'];
        $result = $TwitterApi->getUserInfoByIds($ids);
    }

    public function test13(){
        $user_twitter_id = '895207804469772288';
        UpdateFollowedAccountsJob::dispatch($user_twitter_id);
    }
    
    public function test12(){
        $user_twitter_id = '895207804469772288';
        $followed_accounts_builder = FollowedAccount::where('user_twitter_id', $user_twitter_id)
                                                        ->whereNull('unfollowed_at')
                                                        ->orderBy('updated_at', 'desc');
        $followed_accounts = $followed_accounts_builder->get();
        Log::debug('TEST : ' . print_r(array_column($followed_accounts->toArray(), 'updated_at'), true));
    }

    public function test11()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);
        $now = time();

        $result = $TwitterApi->checkLastActiveTime($user_twitter_id);
        Log::debug('TEST LAST ACTIVE : ' . print_r($now - $result, true));
        // Log::debug('NOW : ' . print_r($now, true));
        // Log::debug('TEST LAST ACTIVE : ' . print_r(strtotime($result), true));

    }

    public function test10(){
        UpdateTwitterAccountDataJob::dispatch('1637080537054711808');
        // if(false &&
        // print_r('unko') ) {
        //     //
        // }
    }

    public function test9()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;

        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $TwitterApi->setTokenToHeader($access_token);
        $date1 = new DateTime('2023-05-24 14:31:43');
        $date2 = new DateTime('now');
        $result = $date2->diff($date1)->format('%s');


        Log::debug('DATETIME : ' . print_r($result, true));
    }

    public function test8()
    {
        $user_twitter_id = '1637080537054711808';
        $target_twitter_id = '924353116937392128';

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = 'MlRZTmJJemlCcFk1MWZGbFdtZUZqRXlNSHFvZUg2YmRUSzhVVFdTN3RLNnNYOjE2ODYzMjAzNTI3MzU6MTowOmF0OjE';

        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $TwitterApi->setTokenToHeader($access_token);
        $text = 'test';
        $result = $TwitterApi->tweet($text);

        Log::debug('TWEET : ' . print_r($result, true));
    }

    //使うな
    public function test7Botsu()
    {

        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        //$TwitterApi->setTokenToHeader($access_token);
        $TwitterApi->header = ['Authorization: Bearer ' . $access_token,
                        'Content-Type: multipart/form-data; boundary=p-a-h-o-o---------------'
                    ];

        $img = base64_encode(file_get_contents('https://www.google.com/images/branding/googlelogo/2x/googlelogo_light_color_272x92dp.png'));

        $result = $TwitterApi->uploadImage($img);
        Log::debug('BASE64 IMG: ' . print_r($result, true));

    }
    
    public function test6()
    {

        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $str = '阿伊宇aiueo';
        $TwitterApi->checkJapanese($str);
        Log::debug('JAPANESE : ' . print_r($TwitterApi->checkJapanese($str), true));

    }

    public function test5()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $start_time = $TwitterApi->toTwitterTime('2023-05-28 09:00:00');
        $words_query = 'OR (ガッシュベル) OR (視聴 )';
        $words_query = $words_query .' -is:retweet -is:reply ';
        $words_query = '((PHP バック) OR (JavaScript ) ) -(Ruby Rails ) -is:retweet -is:reply lang:ja';
        //((PHP ) OR (Javascript ) OR (JavaScript ) ) -(Ruby Rails ) -is:retweet -is:reply lang:ja
        $result = $TwitterApi->searchTweets($words_query, $start_time);
        Log::debug('WORDS QUERY : ' . print_r($words_query, true));
        Log::debug('SEARCH TWEETS : ' . print_r($result, true));
    }
    
    public function test4()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);
        $now = time();

        $result = $TwitterApi->checkLastActiveTime($user_twitter_id);
        Log::debug('TEST LAST ACTIVE : ' . print_r($now - $result, true));
        // Log::debug('NOW : ' . print_r($now, true));
        // Log::debug('TEST LAST ACTIVE : ' . print_r(strtotime($result), true));

    }
    
    public function test3()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->getTweets($user_twitter_id, false, false);
        //Log::debug('TWEETS: ' . print_r($result, true));
        foreach($result['data'] as $one_result){
            Log::debug('CREATEDAT: ' . print_r($TwitterApi->toJapanTime($one_result['created_at']), true));
        }
    }

    public function test2()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->getLikingTweets($user_twitter_id, false);
        // $result_column = array_column($result['data'], 'created_at');
        // for($i = 0; $i < count($result_column); $i++){
        //     $result_column[$i] = strtotime($result_column[$i]);
        // }
        Log::debug('LIKING : ' . print_r($result, true));
        //Log::debug('LIKING MAX : ' . print_r(max($result_column), true));
    }
    
    public function test1()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->unfollow($user_twitter_id, $target_twitter_id);
        Log::debug('UNFOLLOW JOB : ' . print_r($result, true));
    }

    public function test0()
    {
        $user_twitter_id = '895207804469772288';
        $target_twitter_id = '924353116937392128';
        $TwitterApi = new TwitterApi(env('API_KEY'), 
                                    env('API_SECRET'), 
                                    env('BEARER'), 
                                    env('CLIENT_ID'), 
                                    env('CLIENT_SECRET'), 
                                    env('REDIRECT_URI'));

        $twitter_account_info = TwitterAccount::find($user_twitter_id);
        $access_token = $twitter_account_info->access_token;
        $TwitterApi->setTokenToHeader($access_token);

        $result = $TwitterApi->follow($user_twitter_id, $target_twitter_id);
        Log::debug('FOLLOW JOB : ' . print_r($result, true));

        // $url = $TwitterApi->makeAuthorizeUrl();
        // Log::debug('URL : ' . print_r($url, true));

    }
}
