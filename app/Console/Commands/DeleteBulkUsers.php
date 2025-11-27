<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DeleteBulkUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * - `--dry-run` shows matching users without deleting
     * - `--pattern=` allows overriding default match pattern (SQL LIKE or email prefix)
     */
    protected $signature = 'users:delete-bulk {--dry-run : Preview matches without deleting} {--pattern= : Override match pattern (comma-separated terms)} {--yes : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete bulk/sample users (e.g. "Bulk User*" or test.bulk* emails). Use --dry-run to preview first.';

    public function handle()
    {
        $defaultPatterns = [
            "first_name LIKE 'Bulk User%'",
            "email LIKE 'test.bulk%'",
        ];

        $patternOption = $this->option('pattern');
        if ($patternOption) {
            // Accept comma-separated terms; allow simple column:value or raw SQL-ish patterns
            $terms = array_map('trim', explode(',', $patternOption));
            $clauses = [];
            foreach ($terms as $t) {
                // simple heuristics: if contains @ or dot, match email prefix
                if (Str::contains($t, '@') || Str::contains($t, 'test.bulk')) {
                    $clauses[] = "email LIKE '" . addslashes($t) . "%'";
                } else {
                    $clauses[] = "first_name LIKE '" . addslashes($t) . "%'";
                }
            }
        } else {
            $clauses = $defaultPatterns;
        }

        $whereRaw = implode(' OR ', $clauses);

        // Count matches
        $count = User::whereRaw($whereRaw)->count();

        if ($count === 0) {
            $this->info('No matching bulk/sample users found.');
            return 0;
        }

        $this->info("Found {$count} matching users.");

        if ($this->option('dry-run')) {
            $this->line('Dry-run: listing matching users (id, full name, email)');
            User::whereRaw($whereRaw)->orderBy('user_id')->chunk(100, function ($rows) {
                foreach ($rows as $u) {
                    $this->line("{$u->user_id}\t{$u->first_name} {$u->last_name}\t{$u->email}");
                }
            });
            $this->info('Dry-run complete. No users were deleted.');
            return 0;
        }

        if (! $this->option('yes')) {
            if (! $this->confirm("Do you want to permanently delete these {$count} users?")) {
                $this->info('Aborted. No users were deleted.');
                return 0;
            }
        }

        $deleted = 0;
        User::whereRaw($whereRaw)->orderBy('user_id')->chunk(100, function ($rows) use (&$deleted) {
            foreach ($rows as $u) {
                try {
                    $u->delete();
                    $deleted++;
                    $this->output->write('.');
                } catch (\Throwable $e) {
                    $this->error("\nFailed to delete user {$u->user_id}: {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info("Deleted {$deleted} users.");
        return 0;
    }
}
