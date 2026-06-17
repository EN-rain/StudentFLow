<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        $this->applyFilters($query, $request);
        $logs = $query->limit(500)->get();
        return view('admin.activity-logs.index', compact('logs'));
    }

    public function csv(Request $request)
    {
        $query = ActivityLog::with('user')->latest();
        $this->applyFilters($query, $request);
        $logs = $query->limit(2000)->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'User', 'Action', 'Entity', 'Entity ID', 'IP', 'Metadata']);
            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at,
                    $log->user?->username ?? 'system',
                    $log->action,
                    class_basename($log->entity_type),
                    $log->entity_id,
                    $log->ip_address,
                    json_encode($log->metadata),
                ]);
            }
            fclose($out);
        }, 'activity_logs.csv', ['Content-Type' => 'text/csv']);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where('action', 'like', $like)
                    ->orWhere('entity_type', 'like', $like)
                    ->orWhere('ip_address', 'like', $like)
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', $like)->orWhere('email', 'like', $like));
            });
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
    }
}
