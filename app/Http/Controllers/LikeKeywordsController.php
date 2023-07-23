<?php

namespace App\Http\Controllers;

use App\Library\DBErrorHandler;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\LikeKeyword;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

//いいねキーワード用コントローラ
class LikeKeywordsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $data = LikeKeyword::where('twitter_id', Session::get('twitter_id'))
                                    ->select('id', 'keywords', 'not_flag')
                                    ->get();

            return $data->toArray();
        } catch (\Throwable $e) {
            Log::error('[ERROR] LIKE KEYWORD CONTROLLER - INDEX : ' . print_r($e->getMessage(), true));

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
        $request->validate([
            'keywords' => 'required|string|max:255',
            'not_flag' => 'required|boolean',
        ]);
        Log::debug('SESSION : ' .print_r($request->session()->all(), true));

        try{
            DB::transaction(function () use($request){
                $result = LikeKeyword::create([
                    'twitter_id' => Session::get('twitter_id'),
                    'keywords' => $request->keywords,
                    'not_flag' => $request->not_flag,
                ]);
                DBErrorHandler::checkCreated($result);
            });
        }catch (\Throwable $e){
            Log::error('[ERROR] LIKE KEYWORD CONTROLLER - STORE : ' .print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Log::debug('DELETE : ' .print_r($id, true));

        try{
            DB::transaction(function () use ($id) {
                $result = LikeKeyword::find($id)->forceDelete();
                DBErrorHandler::checkDeleted($result);
            });
        } catch (\Throwable $e) {
            Log::error('[ERROR] LIKE KEYWORD CONTROLLER - DESTROY : ' . print_r($e->getMessage(), true));

            return response()->json('', Response::HTTP_NOT_IMPLEMENTED);
        }
    }
}
