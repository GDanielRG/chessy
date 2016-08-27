<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function action(Request $request)
    {
        \Log::info($request);
        return 'lol gtfo';
        #id NAME
#createGame
#joinGame CODE
#startGame
#move MOVE
    }
}
