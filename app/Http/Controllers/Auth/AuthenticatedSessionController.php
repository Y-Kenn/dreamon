<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Library\TwitterApi;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {

        $TwitterApi = new TwitterApi(env('API_KEY'), env('API_SECRET'), env('BEARER'), env('CLIENT_ID'), env('CLIENT_SECRET'), env('REDIRECT_URI'));
        $authorize_url = $TwitterApi->makeAuthorizeUrl();
        $page_name = 'login';
        return view('auth.login', compact('authorize_url'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $data = Auth::user()->twitterAccounts()
                            ->where('active_flag', true)
                            ->select('twitter_id')->first();

        Session::put('twitter_id', $data['twitter_id']);

        // return redirect()->intended(RouteServiceProvider::HOME);
        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Log::debug('LOGOUT : ' .print_r($request->all(), true));
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
