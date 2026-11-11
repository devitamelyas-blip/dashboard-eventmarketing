<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of system activity logs.
     */
    public function index(Request $request): Response
    {
        $query = Activity::with(['causer'])->latest();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhereHasMorph('causer', ['App\Models\User'], function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->input('log_name'));
        }

        $activities = $query->paginate(20)
            ->withQueryString()
            ->through(fn (Activity $act) => [
                'id' => $act->id,
                'log_name' => $act->log_name,
                'description' => $act->description,
                'event' => $act->event,
                'subject_type' => $act->subject_type ? class_basename($act->subject_type) : null,
                'subject_id' => $act->subject_id,
                'causer' => $act->causer ? [
                    'id' => $act->causer->id,
                    'name' => $act->causer->name,
                    'email' => $act->causer->email,
                ] : null,
                'properties' => $act->properties,
                'created_at' => $act->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $act->created_at->diffForHumans(),
            ]);

        $logNames = Activity::select('log_name')->distinct()->pluck('log_name')->filter()->values();

        return Inertia::render('Admin/ActivityLogs/Index', [
            'activities' => $activities,
            'filters' => $request->only(['search', 'log_name']),
            'logNames' => $logNames,
        ]);
    }
}
