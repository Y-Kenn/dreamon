<?php

namespace App\Http\Controllers;

use App\Mail\ContactAdminMail;
use App\Mail\ContactCustomerMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function create(){
        return view('pages/contact');
    }

    public function store(Request $request){
        $request->validate([
            'email' => 'required|email:filter',
            'text' => 'required|string'
        ],[
            'email' => '有効なメールアドレスを入力してください'
        ]);


        Mail::send(new ContactAdminMail($request->email, $request->text));
        Mail::send(new ContactCustomerMail($request->email, $request->text));

        return redirect('/contact')->with('flash_message', 'お問合せを受け付けました');;
    }
}
