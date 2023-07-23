<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Exception;

//パスワード登録用コントローラ
class RegistPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //パスワードが登録済みかどうかを返却
    public function index()
    {
        try{
            $result = Auth::user();
            DBErrorHandler::checkFound($result);
        } catch (\Throwable $e) {
            Log::error('[ERROR] REGIST PASSWORD CONTROLLER - INDEX : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }

        if($result['password'] === null){
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

        try{
            $user_info = Auth::user();
            DBErrorHandler::checkFound($user_info);
        } catch (\Throwable $e) {
            Log::error('[ERROR] REGIST PASSWORD CONTROLLER - UPDATE - FIND : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }
        //パスワード未登録の場合（初回登録）
        if($user_info['password'] === null){
            $validated = $request->validate([
                'password' => 'required|min:8|max:20|confirmed:password|regex:/^[!-~]+$/',
            ]);

            Log::debug('PASSWORD : ' .print_r(Auth::user()['password'], true));

            try {
                DB::transaction(function () use($request, $validated){
                    $result = $request->user()->update([
                        'password' => Hash::make($validated['password']),
                    ]);
                    DBErrorHandler::checkUpdated($result);
                });
            }catch (\Throwable $e){
                Log::error('[ERROR] REGIST PASSWORD CONTROLLER - UPDATE - REGIST : ' .print_r($e->getMessage(), true));

                return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
            }
        //パスワード登録済みの場合（パスワード変更の場合）
        }else{
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => 'required|min:8|max:20|confirmed:password|regex:/^[!-~]+$/',//^[!-~]+$

            ]);

            try {
                DB::transaction(function () use($request, $validated){
                    $result = $request->user()->update([
                        'password' => Hash::make($validated['password']),
                    ]);

                    DBErrorHandler::checkUpdated($result);
                });
            }catch (\Throwable $e){
                Log::error('[ERROR] REGIST PASSWORD CONTROLLER - UPDATE - UPDATE : ' .print_r($e->getMessage(), true));

                return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
            }
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
