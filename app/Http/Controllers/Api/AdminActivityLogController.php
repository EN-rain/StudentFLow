<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user')->latest();
        if ($q = $request->query('q')) {
            $query->where('action', 'like', "%{$q}%")
                ->orWhere('entity_type', 'like', "%{$q}%");
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        return response()->json(['data' => $query->limit(500)->get()]);
    }
}
