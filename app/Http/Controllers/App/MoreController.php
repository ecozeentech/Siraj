<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MoreController extends Controller
{
    public function __invoke(Request $request)
    {
        return view('app.more', ['user' => $request->user()]);
    }
}
