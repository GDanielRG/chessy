<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class WhatsappController extends Controller
{
    public function register()
    {
        $username = "528111872585";
        $debug = true;

        $r = new \Registration($username, $debug);

        \Log::info($r->codeRequest('sms'));
    }

    public function codeRegister()
    {
        $username = "528111872585";
        $debug = true;

        $r = new \Registration($username, $debug);

        $code = '221987';

        dd($r->codeRegister($code));
    }

    public function login()
    {
        $username = "528111872585";
        $nickname = "Chessy";
        $password = "AO79S33m2TlfiHjnebzmvJDDNAc"; // The one we got registering the number
        $debug = true;

        // Create a instance of WhastPort.
        $w = new WhatsProt($username, $nickname, $debug);

        $w->connect(); // Connect to WhatsApp network
        $w->loginWithPassword($password); // logging in with the password we got!
    }
}
