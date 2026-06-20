<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class HealthController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'studentflow',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
