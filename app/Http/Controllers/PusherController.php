<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\NotifyEvent;

class PusherController extends Controller
{
    //
    public function index()
    {

        return view('test.pusher');
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required',
        ]);

        // dd($request->message);
        event(new NotifyEvent($request->message));

        return view('test.pusher');
    }
}
