<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MobileOAuthController extends Controller
{
    public function github(): Response
    {
        return response(<<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentFlow sign-in</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; display: grid; min-height: 100vh; place-items: center; background: #f4f7fb; color: #172033; }
        main { max-width: 34rem; margin: 1.5rem; padding: 2rem; border-radius: 1rem; background: white; box-shadow: 0 1rem 3rem rgba(20, 35, 60, .12); }
        h1 { margin-top: 0; }
    </style>
</head>
<body>
<main>
    <h1>Return to StudentFlow</h1>
    <p>Open the StudentFlow Android app to finish GitHub sign-in. This page does not display or exchange your authorization code.</p>
</main>
</body>
</html>
HTML);
    }

    public function assetLinks(): JsonResponse
    {
        $fingerprint = trim((string) config('studentflow.android_app_cert_sha256'));
        if ($fingerprint === '') {
            return response()->json([], 503);
        }

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => 'com.studentflow.app',
                'sha256_cert_fingerprints' => [$fingerprint],
            ],
        ]]);
    }
}
