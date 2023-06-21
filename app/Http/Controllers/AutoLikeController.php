<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TwitterAccount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AutoLikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TwitterAccount::where('twitter_id', Session::get('twitter_id'))
                                            ->select('twitter_id', 'liking_flag')
                                            ->first();
        
        $data_array = $data->toArray();
        $data_array['twitter_id'] = (string)$data_array['twitter_id'];
        Log::debug('GET : ' . print_r(gettype($data_array['twitter_id']), true));
        return $data_array;
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
        Log::debug('PUT : ' . print_r($id, true));
        $request->validate([
            'liking_flag' => 'required|boolean'
        ]);
        
        TwitterAccount::find($id)->update(['liking_flag' => $request->liking_flag]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
