<?php

namespace App\Console\Commands;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedIfEmpty extends Command
{
    protected $signature = 'app:seed-if-empty';

    protected $description = 'Seed demo data only when the users table is empty.';

    public function handle(): int
    {
        if (User::count() > 0) {
            if (SchoolSetting::count() === 0) {
                $this->error('Database appears partially initialized. Refusing to skip or destructively reseed it.');

                return self::FAILURE;
            }

            $this->info('Database already has initialized data; skipping seed. Classes are managed from the admin Classes page.');

            return self::SUCCESS;
        }

        $this->warn('Database has no users; running initial demo seed.');
        config(['studentflow.allow_demo_seed' => true]);
        Artisan::call('db:seed', ['--force' => true]);
        $this->output->write(Artisan::output());

        if (blank(env('STUDENTFLOW_SEED_ADMIN_PASSWORD')) || blank(env('STUDENTFLOW_SEED_TEACHER_PASSWORD'))) {
            $this->warn('Seeded accounts were created without fixed default passwords. Set STUDENTFLOW_SEED_ADMIN_PASSWORD and STUDENTFLOW_SEED_TEACHER_PASSWORD before seeding if you need known bootstrap credentials.');
        }

        return self::SUCCESS;
    }
}
