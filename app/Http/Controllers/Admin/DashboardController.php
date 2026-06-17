<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $posts = $request->user()->linkedinPosts();
        $todayStart = now($request->user()->timezone)->startOfDay()->utc();
        $todayEnd = now($request->user()->timezone)->endOfDay()->utc();

        return view('dashboard', [
            'counts' => [
                'total' => (clone $posts)->count(),
                'pending' => (clone $posts)->where('status', 'pending')->count(),
                'posted' => (clone $posts)->where('status', 'posted')->count(),
                'failed' => (clone $posts)->where('status', 'failed')->count(),
            ],
            'todayPosts' => (clone $posts)
                ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
                ->orderBy('scheduled_at')
                ->get(),
            'upcomingPosts' => (clone $posts)
                ->where('status', 'pending')
                ->where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->limit(8)
                ->get(),
            'recentLogs' => $request->user()->linkedinPosts()
                ->with('logs')
                ->latest()
                ->limit(8)
                ->get()
                ->flatMap->logs
                ->sortByDesc('created_at')
                ->take(8),
        ]);
    }
}
