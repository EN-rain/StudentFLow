<?php

namespace App\Console\Commands;

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
            $this->info('Database already has users; skipping seed.');
            return self::SUCCESS;
        }

        $this->warn('Database has no users; running initial demo seed.');
        Artisan::call('db:seed', ['--force' => true]);
        $this->output->write(Artisan::output());

        return self::SUCCESS;
    }
}
