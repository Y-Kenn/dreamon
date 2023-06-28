<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create()//: View//: Response
    {
        Log::debug('LOGIN');

        return view('auth.login');

        // return Inertia::render('Auth/Login', [
        //     'canResetPassword' => Route::has('password.request'),
        //     'status' => session('status'),
        // ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)//: RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $data = Auth::user()->twitterAccounts()
                            ->where('active_flag', true)
                            ->select('twitter_id')->first();

        Session::put('twitter_id', $data['twitter_id']);
        Log::debug('LOGIN STORE : ' .print_r($request->all(), true));

        // return redirect()->intended(RouteServiceProvider::HOME);
        return view('welcome');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
