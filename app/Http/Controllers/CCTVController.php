<?php

namespace App\Http\Controllers;

use App\Models\Camera;
use App\Models\FeedEvent;
use Illuminate\Http\Request;

class CCTVController extends Controller
{
    public function index(Request $request)
    {
        $activeCategory = $request->query('category', 'All');
        $categories = ['All', 'Monitor', 'Front Gate', 'Exit Gate'];
    
        // Fetch cameras grouped by location when 'All' is selected
        if ($activeCategory === 'All') {
            $cameras = Camera::all()->groupBy('location'); // Group by 'location'
        } else {
            $cameras = Camera::where('location', $activeCategory)->get();
        }
    
        $feedEvents = FeedEvent::latest('time')->take(5)->get();
        //dd($cameras);
        return view('cctv.index', compact('categories', 'cameras', 'feedEvents', 'activeCategory'));
    }
}
