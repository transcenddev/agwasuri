<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class NormalizeAccountTypes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:normalize-account-types {--dry-run : Show changes without saving them}';

    /**
     * The console command description.
     */
    protected $description = 'Normalize user account_type values to canonical lowercase values (admin/user).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $stats = [
            'checked' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'errors' => 0,
        ];

        $users = User::select('user_id', 'account_type')->get();

        foreach ($users as $user) {
            $stats['checked']++;

            $original = $user->account_type;
            $normalized = $this->normalize($original);

            if ($normalized === $original) {
                $stats['unchanged']++;
                continue;
            }

            if ($normalized === null) {
                $stats['errors']++;
                $this->warn("[user_id={$user->user_id}] Skipped unknown account_type '{$original}'");
                continue;
            }

            if ($dryRun) {
                $this->line("[DRY RUN] user_id={$user->user_id}: '{$original}' -> '{$normalized}'");
                $stats['updated']++;
                continue;
            }

            try {
                $user->update(['account_type' => $normalized]);
                $stats['updated']++;
            } catch (\Throwable $e) {
                $stats['errors']++;
                $this->error("[user_id={$user->user_id}] Failed to update: {$e->getMessage()}");
            }
        }

        $this->info("Checked: {$stats['checked']} | Updated: {$stats['updated']} | Unchanged: {$stats['unchanged']} | Errors: {$stats['errors']}");

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function normalize($value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return 'user';
        }

        if ($normalized === 'admin' || str_starts_with($normalized, 'admin')) {
            return 'admin';
        }

        if ($normalized === 'user' || str_starts_with($normalized, 'user')) {
            return 'user';
        }

        // Safest fallback: treat unknown roles as user to avoid privilege escalation.
        return 'user';
    }
}
