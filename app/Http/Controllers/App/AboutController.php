<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function __invoke()
    {
        return view('app.about');
    }
}
