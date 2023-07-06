<?php
namespace App\Library;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\TwitterAccount;
use Illuminate\Support\Facades\Mail;
use App\Mail\LockedNotificationMail;
use DateTime;
use DateTimeZone;

class TwitterApi
{

    public function __construct($key, $secret, $bearer, $client_id, $client_secret, $redirect_uri){
        $this->key = $key;
        $this->secret = $secret;
        $this->bearer = $bearer;
        $this->header = ['Authorization: Bearer ' . $this->bearer,
                        'Content-Type: application/json'];
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
    }

    //URL末尾にクエリパラメータ追加
    public function makeUrl($base_url, $query){
        return $base_url . '?' . http_build_query($query);
    }

    //URLにパスパラメータを挿入
    public function insertParam($base_url, $param_list){
        $new_url = $base_url;
        foreach($param_list as $key => $value){
            $new_url = str_replace($key, $value, $new_url);
        }
        return $new_url;
    }

    //APIリクエスト実行
    public function request($url, $method, $query=NULL){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if($query){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($query));
        }


        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        return $result;
    }

    //code_verifier生成（認証用URL生成用）
    public function generateCodeVerifier(int $byte_length = 32)
    {
        $random_bytes_string = openssl_random_pseudo_bytes($byte_length);
        $encoded_random_string = base64_encode($random_bytes_string);
        $url_safe_encoding = [
            '=' => '',
            '+' => '-',
            '/' => '_',
        ];
        $code_verifier = strtr($encoded_random_string, $url_safe_encoding);

        return $code_verifier;
    }

    //code_challenge生成（認証用URL生成用）
    public function generateCodeChallenge($code_verifier)
    {
        $hash = hash('sha256', $code_verifier, true);
        $code_challenge = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));

        return $code_challenge;
    }

    //アカウントの凍結を検出
    public function checkAccountLocked($result, $twitter_id){
        if(isset($result['status'])){
            if($result['status'] === 403){
                $data = TwitterAccount::where('twitter_id', $twitter_id)->select('locked_flag')->first()->toArray();
                //凍結後の処理未実施の場合
                if(!$data['locked_flag']){
                    Log::notice('ACCOUNT LOCKED : ' .print_r($result, true));
                    TwitterAccount::where('twitter_id', $twitter_id)->update(['locked_flag' => true]);
                    Mail::send(new LockedNotificationMail($twitter_id));
                    return true;
                }
            }
        }
        return false;
    }

    //アクセストークンをhttpヘッダにセット
    public function setTokenToHeader($access_token){
        $this->header = ['Authorization: Bearer ' . $access_token,
                        'Content-Type: application/json'
                    ];

        return $this->header;
    }

    ////////////////////////////////
    //認証関係
    ////////////////////////////////

    //認証用URL生成//
    public function makeAuthorizeUrl(){
        $base_url = 'https://twitter.com/i/oauth2/authorize';
        $code_verifier = $this->generateCodeVerifier();
        $code_challenge = $this->generateCodeChallenge($code_verifier);
        $query = [
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'users.read tweet.read list.read like.read follows.read follows.write tweet.write like.write offline.access',
            'state' => $code_verifier,
            'code_challenge' => $code_challenge,
            'code_challenge_method' => 's256'
        ];
        $url = $this->makeUrl($base_url, $query);

        return $url;
    }

    //アクセストークン取得//
    public function getAccessToken($code, $verify){
        $base_url = 'https://api.twitter.com/2/oauth2/token';
        $method = 'POST';

        $query = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => env('CLIENT_ID'),
            'redirect_uri' => env('REDIRECT_URI'),
            'code_verifier' => $verify,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf('%s:%s', env('CLIENT_ID'), env('CLIENT_SECRET')));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($query));

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        return $result;
    }

    //トークンのリフレッシュ//
    public function refreshToken($refresh_token){
        $base_url = 'https://api.twitter.com/2/oauth2/token';
        $method = 'POST';

        $query = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf('%s:%s', $this->client_id, $this->client_secret));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($query));

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);


        return json_decode($response, true);
    }

    //アクセストークンの期限切れを判定し、期限切れの場合はリフレッシュ
    public function checkRefreshToken($twitter_id){
        $account_builder = TwitterAccount::where('twitter_id', $twitter_id);
        $account = $account_builder->first();
        Log::debug('TOKEN GENERATED TIME : ' .print_r(strtotime($account['token_generated_time']), true));
        if(time() - strtotime($account['token_generated_time']) > env('TOKEN_LIFETIME')){

            Log::debug('REFRESH : ' .print_r($twitter_id, true));
            $refreshed_tokens = $this->refreshToken($account['refresh_token']);

            //アカウント凍結を検出
            $this->checkAccountLocked($refreshed_tokens, $twitter_id);

            $account_builder->update([
                'access_token' => $refreshed_tokens['access_token'],
                'refresh_token' => $refreshed_tokens['refresh_token'],
                'token_generated_time' => date("Y/m/d H:i:s"),
            ]);

            return $refreshed_tokens['access_token'];
        }else{

            return $account['access_token'];
        }
    }

    ////////////////////////////////
    //
    ////////////////////////////////

    //自身のTwitterアカウント情報を取得
    public function getMyInfo(){
        $base_url = "https://api.twitter.com/2/users/me";
        $query = [
            'user.fields' => 'profile_image_url,public_metrics',
        ];
        $url = $this->makeUrl($base_url, $query);

        $result = $this->request($url, 'GET');

        if(!isset($result['data'])){
            Log::debug('ERROR - GET MY INFO : ' . print_r($result, true));
            return $result;
        }

        Log::debug('GET MY INFO : ' . print_r($result, true));

        return $result;
    }

    //ユーザネーム(＠〜〜)からTwitterアカウント情報を取得
    public function getUserInfoByName($name){
        $base_url = 'https://api.twitter.com/2/users/by/username/:username';
        $data = [
            ':username' => $name,
        ];
        $inserted_url = $this->insertParam($base_url, $data);

        $result = $this->request($inserted_url, 'GET');

        //取得に失敗した場合
        if(!isset($result['data'])){
            Log::debug('ERROR - GET USER INFO BY NAME : ' . print_r($result, true));
            return $result;
        }

        return $result;
    }

    //Twitter id の配列から各要素IDのアカウントの情報を取得
    public function getUserInfoByIds($twitter_id_array){
        $base_url = 'https://api.twitter.com/2/users';
        //配列のIDを1つの文字列に連結
        $ids_connected = '';
        for($i = 0; $i < count($twitter_id_array); $i++){
            $ids_connected .= $twitter_id_array[$i];
            if($i != count($twitter_id_array) - 1){
                $ids_connected .= ',';
            }
        }
        $query = [
            'ids' => $ids_connected,
            'user.fields' => 'description,public_metrics,profile_image_url',
        ];
        $url = $this->makeUrl($base_url, $query);
        $result = $this->request($url, 'GET');

        //取得に失敗した場合
        if(!isset($result['data'])){
            Log::debug('ERROR - GET USER INFO IDS : ' . print_r($result, true));
            return $result;
        }

        return $result;

    }

    ////////////////////////////////
    //フォロー・フォロワー関係
    ////////////////////////////////

    //フォロワーを取得//
    public function getFollowers($twitter_id){
        $base_url = 'https://api.twitter.com/2/users/:id/followers';
        $data = [
            ':id' => $twitter_id
        ];
        //１回目のリクエスト
        $inserted_url = $this->insertParam($base_url, $data);
        $query = [
            'max_results' => 1000,
            'user.fields' => 'description,profile_image_url,protected',
        ];
        $url = $this->makeUrl($inserted_url, $query);
        $result = $this->request($url, 'GET');

        //取得に失敗した場合
        if(!isset($result['data'])){
            Log::debug('ERROR - GET FOLLOWERS : ' . print_r($result, true));
            return $result;
        }

        $followers = array();
        for($i = 0; $i < count($result['data']); $i++){
            $followers['data'][$i] = $result['data'][$i];
        }

        if(isset($result['meta']['next_token'])){
            $next_token = $result['meta']['next_token'];

            //2回目以降のリクエスト
            for($i = 0; $i < 15; $i++){

                if(!$next_token) break;

                $query = [
                    'pagination_token' => $next_token,
                    'max_results' => 1000,
                    'user.fields' => 'description,profile_image_url',
                ];
                $url_with_next_token = $this->makeUrl($inserted_url, $query);
                $result = $this->request($url_with_next_token, 'GET');
                if(isset($result['data'])){
                    foreach($result['data'] as $one_follower){
                        $followers['data'][] = $one_follower;
                    }
                    (isset($result['meta']['next_token'])) ? $next_token = $result['meta']['next_token'] : $next_token = false;
                }else{
                    $next_token = false;
                }
                // for($j = 0; $j < count($result['data']); $j++){
                //     $followers['data'][$j] = $result['data'][$j];
                // }
                // $next_token = $result['meta']['next_token'];
            }
        }

        return $followers;
    }

    //フォローを取得//
    public function getFollowings($twitter_id, $paging = true){
        $base_url = 'https://api.twitter.com/2/users/:id/following';
        $data = [
            ':id' => $twitter_id
        ];
        //１回目のリクエスト
        $inserted_url = $this->insertParam($base_url, $data);
        $query = [
            'max_results' => 1000,
        ];
        $url = $this->makeUrl($inserted_url, $query);
        $result = $this->request($url, 'GET');

        //取得に失敗した場合
        if(!isset($result['data'])){
            Log::debug('ERROR - GET FOLLOWING : ' . print_r($result, true));
            return $result;
        }

        $following = array();
        foreach($result['data'] as $one_following){
            $following['data'][] = $one_following;
        }

        //2回目以降のリクエスト
        if($paging === true && isset($result['meta']['next_token'])){

            $next_token = $result['meta']['next_token'];

            for($i = 0; $i < 15; $i++){
                if(!isset($next_token)) break;

                $query = [
                    'pagination_token' => $next_token,
                    'max_results' => 1000,
                ];
                $url_with_next_token = $this->makeUrl($inserted_url, $query);
                $result = $this->request($url_with_next_token, 'GET');
                foreach($result['data'] as $one_following){
                    $following['data'][] = $one_following;
                }
                //next_tokenがレスポンスになければbreak
                (isset($result['meta']['next_token'])) ? $next_token = $result['meta']['next_token'] : $next_token = null;
            }
        }

        return $following;
    }

    //フォロー実行
    public function follow($twitter_id, $target_id){
        $base_url = 'https://api.twitter.com/2/users/:id/following';
        $data = [
            ':id' => $twitter_id,
        ];
        $inserted_url = $this->insertParam($base_url, $data);
        $method = 'POST';
        $json_body = json_encode(array(
            'target_user_id' => $target_id,
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $inserted_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_body);

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        if(!isset($result['data'])){
            Log::debug('ERROR - FOLLOW : ' . print_r($result, true));
            //アカウント凍結を検出
            $this->checkAccountLocked($result, $twitter_id);
        }

        return $result;
    }

    //アンフォロー実行
    public function unfollow($twitter_id, $target_id){
        $base_url = 'https://api.twitter.com/2/users/:source_user_id/following/:target_user_id';
        $data = [
            ':source_user_id' => $twitter_id,
            ':target_user_id' => $target_id,
        ];
        $inserted_url = $this->insertParam($base_url, $data);
        $method = 'DELETE';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $inserted_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        if(!isset($result['data'])){
            Log::debug('ERROR - UNFOLLOW : ' . print_r($result, true));
            //アカウント凍結を検出
            $this->checkAccountLocked($result, $twitter_id);
        }

        return $result;
    }

    //ツイート検索 //いつのツイートから取得するか引数で指定(YYYY-MM-DDTHH:mm:ssZ (ISO 8601/RFC 3339))
    public function searchTweets($words_query, $start_time, $max_results = 10){ //検索ワードは複数可、スペースで区切る
        $base_url = 'https://api.twitter.com/2/tweets/search/recent';
        $query = [
            'query' => $words_query,
            'expansions' => 'author_id,attachments.media_keys',
            'max_results' => $max_results,//10 ~ 100
            'start_time' => $start_time,
            'tweet.fields' => 'created_at',
        ];
        $url = $this->makeUrl($base_url, $query);

        $result = $this->request($url, 'GET');
        if(!isset($result['data'])){
            Log::debug('ERROR - SEARCH TWEETS : ' . print_r($result, true));
            return $result;
        }

        return $result;
    }

    //いいねを付ける
    public function like($twitter_id, $tweet_id){
        $base_url = 'https://api.twitter.com/2/users/:id/likes';
        $data = [
            ':id' => $twitter_id,
        ];
        $inserted_url = $this->insertParam($base_url,$data);

        $json_body = json_encode(array(
            'tweet_id' => $tweet_id
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $inserted_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_body);

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        if(!isset($result['data'])){
            Log::debug('ERROR - LIKE : ' . print_r($result, true));
            //アカウント凍結を検出
            $this->checkAccountLocked($result, $twitter_id);
        }

        return $result;
    }

    //いいねしたツイートを取得//
    public function getLikingTweets($twitter_id, $paging = true){
        $base_url = 'https://api.twitter.com/2/users/:id/liked_tweets';
        //１回目のリクエスト
        $data = [
            ':id' => $twitter_id
        ];
        $inserted_url = $this->insertParam($base_url, $data);
        $query = [
            'max_results' => 10,
            'tweet.fields'=> 'created_at',
        ];
        $url = $this->makeUrl($inserted_url, $query);
        $result = $this->request($url, 'GET');
        //Log::debug('GET LIKING : ' . print_r($result, true));
        //取得に失敗した場合
        if(!isset($result['data'])){
            Log::debug('ERROR - GET LIKING TWEETS : ' . print_r($result, true));
            return $result;
        }

        $liking = array();
        foreach($result['data'] as $one_tweet){
            $liking['data'][] = $one_tweet;
        }

        if($paging === true && isset($result['meta']['next_token'])){
            $next_token = $result['meta']['next_token'];

            //2回目以降のリクエスト
            for($i = 0; $i < 10; $i++){
                //Log::debug('PAGENATION');
                if(!$next_token) break;

                $query = [
                    'pagination_token' => $next_token,
                    'max_results' => 100,
                    'tweet.fields'=> 'created_at',
                ];
                $url_with_next_token = $this->makeUrl($inserted_url, $query);
                $result = $this->request($url_with_next_token, 'GET');
                //Log::debug('GET LIKING : ' . print_r($result, true));
                if(isset($result['data'])){
                    foreach($result['data'] as $one_tweet){
                        $liking['data'][] = $one_tweet;
                    }
                    (isset($result['meta']['next_token'])) ? $next_token = $result['meta']['next_token'] : $next_token = false;
                }else{
                    $next_token = false;
                }


            }
        }

        return $liking;
    }


    //ツイートを取得//
    public function getTweets($twitter_id, $paging = true, $exclude = true){
        $base_url = 'https://api.twitter.com/2/users/:id/tweets';
        $data = [
            ':id' => $twitter_id,
        ];
        //１回目のリクエスト
        $inserted_url = $this->insertParam($base_url,$data);
        if($exclude === true){
            $query = [
                'max_results' => 5,
                'exclude' => 'retweets,replies', //リツイート、リプライを除外
                'expansions' => 'author_id,attachments.media_keys',
                'user.fields' => 'name,profile_image_url',
                'media.fields' => 'preview_image_url',
                'tweet.fields' => 'created_at,public_metrics',
            ];
        }elseif($exclude === false){
            $query = [
                'max_results' => 5,
                'expansions' => 'author_id,attachments.media_keys',
                'user.fields' => 'name,profile_image_url',
                'media.fields' => 'preview_image_url',
                'tweet.fields' => 'created_at,public_metrics',
            ];
        }


        $url = $this->makeUrl($inserted_url, $query);
        $result = $this->request($url, 'GET');


        //取得に失敗した場合
        if(!isset($result['data'])){
            Log::debug('ERROR - GET TWEETS : ' . print_r($result, true));
            return $result;
        }

        $tweets = array();
        foreach($result['data'] as $one_tweet){
            $tweets['data'][] = $one_tweet;
        }
        if(isset($result['includes']['media'])){
            foreach($result['includes']['media'] as $one_tweet){
                $tweets['includes']['media'][] = $one_tweet;
            }
        }
        if(isset($result['includes']['users'])){
            foreach($result['includes']['users'] as $one_tweet){
                $tweets['includes']['users'][] = $one_tweet;
            }
        }

        //2回目以降のリクエスト
        if($paging === true && isset($result['meta']['next_token'])){
            $next_token = $result['meta']['next_token'];

            for($i = 0; $i < 15; $i++){
                if(!$next_token) break;

                if($exclude === true){
                    $query = [
                        'pagination_token' => $next_token,
                        'max_results' => 100,
                        'exclude' => 'retweets,replies', //リツイート、リプライを除外
                        'expansions' => 'author_id,attachments.media_keys',
                        'user.fields' => 'name,profile_image_url',
                        'media.fields' => 'preview_image_url',
                        'tweet.fields' => 'created_at,public_metrics',
                    ];
                }elseif($exclude === false){
                    $query = [
                        'pagination_token' => $next_token,
                        'max_results' => 100,
                        'expansions' => 'author_id,attachments.media_keys',
                        'user.fields' => 'name,profile_image_url',
                        'media.fields' => 'preview_image_url',
                        'tweet.fields' => 'created_at,public_metrics',
                    ];
                }
                $url_with_next_token = $this->makeUrl($inserted_url, $query);
                $result = $this->request($url_with_next_token, 'GET');
                foreach($result['data'] as $one_tweet){
                    $tweets['data'][] = $one_tweet;
                }
                if(isset($result['includes']['media'])){
                    foreach($result['includes']['media'] as $one_tweet){
                        $tweets['includes']['media'][] = $one_tweet;
                    }
                }
                if(isset($result['includes']['users'])){
                    foreach($result['includes']['users'] as $one_tweet){
                        $tweets['includes']['users'][] = $one_tweet;
                    }
                }
                //next_tokenがレスポンスになければbreak
                (isset($result['meta']['next_token'])) ? $next_token = $result['meta']['next_token'] : $next_token = false;
            }
        }

        return $tweets;
    }

    //メンションを取得//
    public function getMentions($twitter_id, $max='10', $paging = false){//max:5~100,それ以上はページネーショントークン
        $base_url = 'https://api.twitter.com/2/users/:id/mentions';
        $param = [
            ':id' => $twitter_id
        ];
        $inserted_url = $this->insertParam($base_url, $param);
        $query = [
            'max_results' => $max,
            'expansions' => 'author_id,attachments.media_keys',
            'user.fields' => 'name,profile_image_url',
            'media.fields' => 'preview_image_url',
            'tweet.fields' => 'created_at',
        ];
        $url = $this->makeUrl($inserted_url, $query);

        $result = $this->request($url, 'GET');

        if(!isset($result['data'])){
            Log::debug('ERROR - MENTIONS : ' . print_r($result, true));
            return $result;
        }

        $mentions = array();
        foreach($result['data'] as $one_tweet){
            $mentions['data'][] = $one_tweet;
        }
        if(isset($result['includes']['media'])){
            foreach($result['includes']['media'] as $one_tweet){
                $mentions['includes']['media'][] = $one_tweet;
            }
        }
        if(isset($result['includes']['users'])){
            foreach($result['includes']['users'] as $one_tweet){
                $mentions['includes']['users'][] = $one_tweet;
            }
        }

        if($paging === true && isset($result['meta']['next_token'])){
            $next_token = $result['meta']['next_token'];

            //2回目以降のリクエスト
            for($i = 0; $i < 10; $i++){
                //Log::debug('PAGENATION');
                if(!$next_token) break;

                $query = [
                    'pagination_token' => $next_token,
                    'max_results' => $max,
                    'expansions' => 'author_id,attachments.media_keys',
                    'user.fields' => 'name,profile_image_url',
                    'media.fields' => 'preview_image_url',
                    'tweet.fields' => 'created_at',
                ];
                $url_with_next_token = $this->makeUrl($inserted_url, $query);
                $result = $this->request($url_with_next_token, 'GET');
                //Log::debug('GET mentions : ' . print_r($result, true));
                if(isset($result['data'])){
                    foreach($result['data'] as $one_tweet){
                        $mentions['data'][] = $one_tweet;
                    }
                    (isset($result['meta']['next_token'])) ? $next_token = $result['meta']['next_token'] : $next_token = false;
                }else{
                    $next_token = false;
                }
                if(isset($result['includes']['media'])){
                    foreach($result['includes']['media'] as $one_tweet){
                        $mentions['includes']['media'][] = $one_tweet;
                    }
                }
                if(isset($result['includes']['users'])){
                    foreach($result['includes']['users'] as $one_tweet){
                        $mentions['includes']['users'][] = $one_tweet;
                    }
                }

            }
        }

        return $mentions;
    }

    //メンション整形(Tweet取得時点ではTweetとユーザ情報が分離しているので各Tweetにマージ)
    public function mergeUserData($tweets){

        function objectSearch($search, $array, $key){
            return $array[array_search($search, array_column($array, $key))];
        }

        for($i = 0; $i < count($tweets['data']); $i++){
            $author_id = $tweets['data'][$i]['author_id'];
            $prof = objectSearch($author_id, $tweets['includes']['users'], 'id'); //func_common.php
            unset($prof['id']);
            $tweets['data'][$i] = array_merge($tweets['data'][$i], $prof);
        }

        return $tweets;
    }

    //ツイート実行
    public function tweet($text){

        $base_url = 'https://api.twitter.com/2/tweets';
        $method = 'POST';
        $json_body = json_encode(array(
            'text' => $text
        ));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_body);

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        if(!isset($result['data'])){
            Log::debug('ERROR - TWEET : ' . print_r($result, true));
        }

        return $result;
    }

    //最後にツイートorリツイートorリプライorいいねした日時を抽出
    //いいねによる抽出日時はいいね先ツイートの発行日時
    public function checkLastActiveTime($twitter_id){

        $created_at_list = array();

        $tweets = $this->getTweets($twitter_id, false, false);
        if(isset($tweets['data'])){
            foreach($tweets['data'] as $tweet){
                $created_at_list[] = strtotime($tweet['created_at']);
            }
        }

        //API制限の回数がgetTweetより少ないため使用しない方が良い
        // $liking_tweets = $this->getLikingTweets($twitter_id, false);
        // if(isset($liking_tweets['data'])){
        //     foreach($liking_tweets['data'] as $tweet){
        //         $created_at_list[] = strtotime($tweet['created_at']);
        //     }
        // }

        //Log::debug('CREATED AT : ' . print_r($created_at_list, true));

        if(!empty($created_at_list)){
           return max($created_at_list);
        }else{
            return strtotime("1980-01-01");
        }
    }

    ////////////////////////////////
    //ユーティリティ
    ////////////////////////////////

    //Twitter APIで使用される時間(ISO 8601)を変換
    public function toJapanTime($iso8601){
        return date('Y-m-d H:i', strtotime($iso8601));
    }

    //"Y/m/d H:i:s" から iso8601へ変換
    public function toTwitterTime($date = 'now'){
        $asia_tokyo = new DateTime($date);
        $asia_tokyo->setTimeZone(new DateTimeZone('UTC'));
        $iso8601 = $asia_tokyo->format('Y-m-d\TH:i:s') . 'Z';

        return $iso8601;
    }

    //文字列からひらがな、カタカナを検出
    public function checkJapanese($str){
        if(preg_match("/[ぁ-ん]+|[ァ-ヴー]+|[ｦ-ﾟ]+/u", $str)){
            //Log::debug('JAPANESE');
            return true;
        }else{
            //Log::debug('FOREIGN LANGAGE');
            return false;
        }
    }

    //封印ー絶対開けるな
    public function uploadImage($base64_image){
        $base_url = 'https://upload.twitter.com/1.1/media/upload.json';
        $data = array(
            'media_data' => $base64_image
        );

        $boundary = 'p-a-h-o-o---------------' . md5(mt_rand());

        //POSTフィールド生成
        $request_body  = '';
        $request_body .= '--' . $boundary . "\r\n";
        $request_body .= 'Content-Disposition: form-data; name="'  . '"; ';
        $request_body .= "\r\n";
        $request_body .= "\r\n" . $base64_image . "\r\n";
        $request_body .= '--' . $boundary . '--' . "\r\n\r\n";

        //リクエストヘッダー生成
        $request_header = "Content-Type: multipart/form-data; boundary=p-a-h-o-o---------------";

        //multipart/form-data
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);

        $response = curl_exec($curl);
        $result = json_decode($response, true);

        curl_close($curl);

        return $response;

    }
}
