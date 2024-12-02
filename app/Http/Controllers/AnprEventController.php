<?php

namespace App\Http\Controllers;
// app\Http\Controllers\AnprEventController.php
use App\Models\AnprEvent;
use App\Models\DahuaEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnprEventController extends Controller
{
    public function index(Request $request)
    {
        $events = AnprEvent::orderBy('event_time', 'desc')->paginate(10);
        return view('anpr.index', compact('events'));
    }
    public function dahuaIndex(Request $request)
    {
        $dahua_events = DahuaEvent::orderBy('event_time', 'desc')->paginate(10);
        return view('anpr.dahua_anpr', compact('dahua_events'));
    }
    public function fetchAnalyticsData(Request $request)
    {
        $today = Carbon::today('Asia/Kuala_Lumpur');

        // Get selected date, week, or month from request, or use defaults
        $selectedDate = $request->input('date', $today->toDateString());
        $selectedWeek = $request->input('week', $today->format('Y-\WW'));
        $selectedMonth = $request->input('month', $today->format('Y-m'));

        // Daily total
        $dailyTotal = AnprEvent::whereDate('event_time', $selectedDate)->count();
        $dailyTotalDahua = DahuaEvent::whereDate('event_time', $selectedDate)->count();

        // Weekly total
        $weeklyStartDate = Carbon::parse($selectedWeek . '-1')->startOfWeek();
        $weeklyEndDate = Carbon::parse($selectedWeek . '-1')->endOfWeek();
        $weeklyTotal = AnprEvent::whereBetween('event_time', [
            $weeklyStartDate,
            $weeklyEndDate
        ])->count();
        $weeklyTotalDahua = DahuaEvent::whereBetween('event_time', [
            $weeklyStartDate,
            $weeklyEndDate
        ])->count();

        // Monthly total
        $monthlyTotal = AnprEvent::whereMonth('event_time', Carbon::parse($selectedMonth)->month)
            ->whereYear('event_time', Carbon::parse($selectedMonth)->year)
            ->count();
        $monthlyTotalDahua = DahuaEvent::whereMonth('event_time', Carbon::parse($selectedMonth)->month)
            ->whereYear('event_time', Carbon::parse($selectedMonth)->year)
            ->count();

        // Return as JSON
        return response()->json([
            'status' => 1,
            'dailyTotal' => $dailyTotal,
            'weeklyTotal' => $weeklyTotal,
            'monthlyTotal' => $monthlyTotal,
            'dailyTotalDahua' => $dailyTotalDahua,
            'weeklyTotalDahua' => $weeklyTotalDahua,
            'monthlyTotalDahua' => $monthlyTotalDahua
        ]);
    }
}
