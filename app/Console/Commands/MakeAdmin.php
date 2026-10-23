<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Bangla;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'formsathi:make-admin {mobile} {--revoke}';

    protected $description = 'Give (or take away) staff access for an existing account';

    public function handle(): int
    {
        $user = User::where('mobile', Bangla::mobile($this->argument('mobile')))->first();

        if (! $user) {
            $this->error('No account with that mobile number. The person must sign up first.');

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => ! $this->option('revoke')])->save();
        $this->info($this->option('revoke') ? 'Staff access removed.' : 'Staff access granted.');

        return self::SUCCESS;
    }
}
