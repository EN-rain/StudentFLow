<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncStarterCredentials extends Command
{
    protected $signature = 'app:sync-starter-credentials';

    protected $description = 'Update known starter accounts to the configured demo passwords without reseeding data.';

    public function handle(): int
    {
        $groups = [
            'STUDENTFLOW_SEED_ADMIN_PASSWORD' => ['admin'],
            'STUDENTFLOW_SEED_TEACHER_PASSWORD' => [
                'john.reyes',
                'angela.cruz',
                'roberto.delapena',
                'paolo.mercado',
                'sophia.tan',
            ],
            'STUDENTFLOW_SEED_STUDENT_PASSWORD' => [
                'aaronvillanueva001',
                'biancaramos002',
                'carlomendoza003',
                'denisegarcia004',
                'ethanflores005',
                'faithnavarro006',
                'gabrieltorres007',
                'hannahlen008',
                'ivancastillo009',
                'jasmineaquino010',
            ],
        ];

        $updated = 0;

        foreach ($groups as $envKey => $usernames) {
            $password = env($envKey, match ($envKey) {
                'STUDENTFLOW_SEED_ADMIN_PASSWORD' => 'AdminPass123!',
                'STUDENTFLOW_SEED_TEACHER_PASSWORD' => 'TeacherPass123!',
                'STUDENTFLOW_SEED_STUDENT_PASSWORD' => 'StudentPass123!',
                default => null,
            });
            if (! is_string($password) || trim($password) === '') {
                $this->warn("Skipping {$envKey}; no password configured.");

                continue;
            }

            foreach ($usernames as $username) {
                $user = User::where('username', $username)->first();
                if (! $user) {
                    $this->warn("Starter account missing: {$username}");

                    continue;
                }

                $user->forceFill(['password' => Hash::make($password)])->save();
                $updated++;
            }
        }

        $this->info("Starter credentials synced for {$updated} account(s).");

        return self::SUCCESS;
    }
}
