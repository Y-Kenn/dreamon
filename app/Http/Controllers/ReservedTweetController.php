<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwitterAccount;
use App\Models\ReservedTweet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ReservedTweetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data_builder = ReservedTweet::where('twitter_id', Session::get('twitter_id'))
                                        ->whereNull('thrown_at')
                                        ->orderBy('reserved_date')
                                        ->select('id', 'text', 'reserved_date');
        if(!$data_builder->exists()){
            return array();
        }

        $data = $data_builder->get();
        Log::debug('RESERVED TWEET - INDEX : ' .print_r($data, true));

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
        //Log::debug('RESERVED TWEET - STORE');
        Log::debug('RESERVED TWEET - STORE : ' .print_r($request->all(), true));
        $request->validate([
            'text' => 'required|string|max:' .env('TWEET_CHAR_NUM'),
            'reserved_date' => 'required',
        ]);
        
        ReservedTweet::create([
            'twitter_id' => Session::get('twitter_id'),
            'text' => $request->text,
            'reserved_date' => $request->reserved_date
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
        ReservedTweet::find($id)->forceDelete();
    }
}
