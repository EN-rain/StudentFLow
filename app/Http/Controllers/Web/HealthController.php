<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke()
    {
        try {
            DB::select('select 1');
        } catch (Throwable) {
            return response()->json([
                'status' => 'unavailable',
                'service' => 'studentflow',
                'database' => 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'service' => 'studentflow',
            'database' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
