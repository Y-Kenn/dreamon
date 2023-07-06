<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

//パスワード録用コントローラ
class RegistPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //パスワードが登録済みかどうかを返却
    public function index()
    {
        if(Auth::user()['password'] === null){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    //パスワード登録
    public function update(Request $request, string $id)
    {
        //パスワード未登録の場合（初回登録）
        if(Auth::user()['password'] === null){
            $validated = $request->validate([
                'password' => 'required|min:8|max:20|confirmed:password|regex:/^[!-~]+$/',
            ]);

            Log::debug('PASSWORD : ' .print_r(Auth::user()['password'], true));

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        //パスワード登録済みの場合（パスワード変更の場合）
        }else{
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => 'required|min:8|max:20|confirmed:password|regex:/^[!-~]+$/',//^[!-~]+$

            ]);

            Log::debug('PASSWORD2 : ' .print_r($request->all(), true));

            $current_password_db = $request->user()['password'];

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        }


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
