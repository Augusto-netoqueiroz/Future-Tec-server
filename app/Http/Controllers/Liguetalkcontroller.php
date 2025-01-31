<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Liguetalkcontroller extends Controller
{
    public function index()
    {
        return view('Liguetalk.index');
    }
}
