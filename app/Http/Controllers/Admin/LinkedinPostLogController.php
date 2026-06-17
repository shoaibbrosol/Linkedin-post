<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LinkedinPostLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = $request->user()->linkedinPosts()
            ->with(['logs' => fn ($query) => $query->latest()])
            ->latest()
            ->paginate(20);

        return view('linkedin.logs', compact('logs'));
    }
}
