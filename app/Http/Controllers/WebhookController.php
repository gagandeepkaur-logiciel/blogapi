<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Redirect 
     */
    public function backFromWebHook(){
        echo "yes";
    }
}
