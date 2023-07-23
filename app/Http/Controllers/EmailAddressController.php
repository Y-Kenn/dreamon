<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

//メールアドレス更新用コントローラ
class EmailAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Auth::user()->toArray();

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
    public function update(Request $request, string $id)
    {
        Log::debug('EMAIL : ' .print_r($request->all(), true));
        $request->validate([
            'email' => 'required|email:filter',
        ]);

        try {
            DB::transaction(function () use($request){
                $result = Auth::user()->update([
                    'email' => $request->email,
                ]);
                Log::debug('EMAIL - UPDATE - RESULT : ' .print_r($result, true));
                DBErrorHandler::checkUpdated($result);
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] EMAIL ADDRESS CONTROLLER - UPDATE : ' .print_r($e->getMessage(), true));

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
