<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\TwitterAccount;
use App\Models\FollowKeyword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

//フォローキーワード用コントローラ
class FollowKeywordsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FollowKeyword::where('twitter_id', Session::get('twitter_id'))
                                ->select('id', 'keywords', 'not_flag')
                                ->get();
        return $data->toArray();
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

        FollowKeyword::create([
            'twitter_id' => Session::get('twitter_id'),
            'keywords' => $request->keywords,
            'not_flag' => $request->not_flag,
        ]);
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
        FollowKeyword::find($id)->forceDelete();
    }
}
