<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Response;

class EmailVerificationController extends Controller
{
    public function __invoke(int $id, string $hash): Response
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response(
            '<!doctype html><html><body><h1>Email verified</h1><p>You can now return to StudentFlow and sign in.</p></body></html>',
            200,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}
