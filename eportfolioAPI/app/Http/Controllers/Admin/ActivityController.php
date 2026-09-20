<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    use HandlesMongoFallback;

    public function index(Request $request)
    {
        if (! $this->isMongoAvailable()) {
            return view('admin.activity.index', ['activities' => $this->mockPaginate([])]);
        }

        $query = ActivityLog::with('user');

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        $query->orderByDesc('created_at');

        $activities = $query->paginate(25)->withQueryString();

        return view('admin.activity.index', compact('activities'));
    }
}
