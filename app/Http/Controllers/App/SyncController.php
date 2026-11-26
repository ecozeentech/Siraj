<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\Sync\SyncProcessor;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __invoke(Request $request, SyncProcessor $processor)
    {
        $data = $request->validate(['actions' => ['required', 'array', 'max:100'], 'actions.*.id' => ['required', 'string', 'max:64'], 'actions.*.type' => ['required', 'string', 'max:48'], 'actions.*.payload' => ['nullable', 'array']]);

        return response()->json($processor->process($request->user(), $data['actions']));
    }
}
