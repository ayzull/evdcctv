<?php

namespace App\Http\Controllers;
// app\Http\Controllers\AnprEventController.php
use App\Models\AnprEvent;
use Illuminate\Http\Request;

class AnprEventController extends Controller
{
    public function index(Request $request)
    {
        $events = AnprEvent::orderBy('event_time', 'desc')->paginate(10);
        return view('anpr.index', compact('events'));
    }
}
