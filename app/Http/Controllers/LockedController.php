<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use Illuminate\Http\Request;
use App\Models\TwitterAccount;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Exception;
use function Psy\debug;

//Twitterアカウント凍結時の処理
class LockedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //凍結の有無を送信
    public function index()
    {
        try{
            $data = Auth::user()->twitterAccounts()
                                ->where('twitter_id', Session::get('twitter_id'))
                                ->first();
            DBErrorHandler::checkFound($data);

            return $data;
        } catch (\Throwable $e) {
            Log::error('[ERROR] LOCKED ACCOUNT CONTROLLER - INDEX : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
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
    //フロント側からの復旧処理の受け付け（twitter_accountsテーブルのlocked_flagをfalseに更新）
    public function update(Request $request, string $id)
    {
        $request->validate([
            'locked_flag' => 'required|boolean'
        ]);

        try {
            DB::transaction(function () use($request){
                $result = Auth::user()->twitterAccounts()
                ->where('twitter_id', Session::get('twitter_id'))
                ->update($request->all());
                DBErrorHandler::checkUpdated($result);
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] LOCKED ACCOUNT CONTROLLER - UPDATE : ' .print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
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
