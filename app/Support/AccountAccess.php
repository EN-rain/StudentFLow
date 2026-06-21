<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountAccess
{
    public static function setStatus(User $user, string $status): void
    {
        $user->forceFill(['status' => $status])->save();

        if ($status !== 'active') {
            self::revoke($user);
        }
    }

    public static function revoke(User $user): void
    {
        $user->tokens()->delete();

        if (config('session.driver') !== 'database') {
            return;
        }

        $table = (string) config('session.table', 'sessions');
        if ($table !== '' && Schema::hasTable($table)) {
            DB::table($table)->where('user_id', $user->id)->delete();
        }
    }
}
