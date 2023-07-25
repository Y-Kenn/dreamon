<?php

namespace Tests\Unit;

use App\Library\TwitterApi;
use Tests\TestCase;

class CheckRefreshTokenTest extends TestCase
{
    /**ユニットテスト前にテストDBのTwitterAPIのアクセス・リフレッシュトークンを更新するためのものです。
     * テスト目的ではありません。*/
    public function testCheckRefreshToken(): void
    {

        $TwitterApi = new TwitterApi(env('API_KEY'),
            env('API_SECRET'),
            env('BEARER'),
            env('CLIENT_ID'),
            env('CLIENT_SECRET'),
            env('REDIRECT_URI'));
        $access_token = $TwitterApi->checkRefreshToken('1683346494706028549');
        $access_token = $TwitterApi->checkRefreshToken('1683349434913153026');
    }
}
