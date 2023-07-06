<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwitterAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

//Twitterアカウント凍結時の処理
class LockedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //凍結の有無を送信
    public function index()
    {
        $data = Auth::user()->twitterAccounts()
                            ->where('twitter_id', Session::get('twitter_id'))
                            ->select('locked_flag')
                            ->first();

        return $data;
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
    //フロント側からの復旧処理の受け付け（twitter_accountsテーブルのlocked_flagをfalseに更新）
    public function update(Request $request, string $id)
    {
        $request->validate([
            'locked_flag' => 'required|boolean'
        ]);

        Auth::user()->twitterAccounts()
        ->where('twitter_id', Session::get('twitter_id'))
        ->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
