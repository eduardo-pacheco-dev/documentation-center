<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function show(): View
    {
        return view('settings.show', [
            'user' => auth()->user(),
        ]);
    }
}
