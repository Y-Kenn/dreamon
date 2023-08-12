<?php

namespace App\Http\Controllers\OAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\TwitterApi;

class RedirectTwitterOAuthController extends Controller
{
    public function __invoke(){
        $TwitterApi = new TwitterApi(env('API_KEY'), env('API_SECRET'), env('BEARER'), env('CLIENT_ID'), env('CLIENT_SECRET'), env('REDIRECT_URI'));

        $oauth_url  = $TwitterApi->makeAuthorizeUrl();

        return redirect($oauth_url);
    }
}
