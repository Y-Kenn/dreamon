<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RegistPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
    public function update(Request $request, string $id)
    {
        if(Auth::user()['password'] === null){
            $validated = $request->validate([
                'password' => 'required|min:6|max:20|confirmed:password|regex:/^[!-~]+$/',
            ]);

            Log::debug('PASSWORD : ' .print_r(Auth::user()['password'], true));

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);
        }else{
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => 'required|min:6|max:20|confirmed:password|regex:/^[!-~]+$/',//^[!-~]+$

            ]);

            Log::debug('PASSWORD2 : ' .print_r($request->all(), true));

            $current_password_db = $request->user()['password'];
            // Log::debug('CURRENT PASSWORD : ' .print_r($current_password_db, true));
            // Log::debug('CURRENT PASSWORD : ' .print_r(Hash::make($request->current_password), true));
            // if($current_password_db !== Hash::make($request->current_password)){
            //     Log::debug('NOT MATCH');
            //     return false;
            // }

            // Log::debug('MATCH');
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
