<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    // Método index para a página inicial
    public function index()
    {
        return view('home'); // Retorna a view 'home.blade.php'
    }
}
