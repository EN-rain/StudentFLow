<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\ApiPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ActivityLog::with('user')->latest();
        if ($q = $request->query('q')) {
            $query->where(function ($filter) use ($q) {
                $filter->where('action', 'like', "%{$q}%")
                    ->orWhere('entity_type', 'like', "%{$q}%");
            });
        }
        if ($from = $request->query('from')) {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }
        if ($to = $request->query('to')) {
            $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        return response()->json(ApiPagination::paginate($query, $request));
    }
}
