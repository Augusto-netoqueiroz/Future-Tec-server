<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        return view('landing.index'); // Certifique-se de que a view está em resources/views/page.blade.php
    }
}