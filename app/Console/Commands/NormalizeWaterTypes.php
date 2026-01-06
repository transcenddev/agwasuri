<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class NormalizeWaterTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'water:normalize-types {--dry-run : Show what would change without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize user water_type values to Freshwater, Brackish, or Saltwater.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $mapToCanonical = function (?string $value): ?string {
            if ($value === null) {
                return null;
            }
            $normalized = strtolower(trim($value));
            if ($normalized === '') {
                return null;
            }
            if (str_starts_with($normalized, 'fresh')) {
                return 'Freshwater';
            }
            if (str_starts_with($normalized, 'brack')) {
                return 'Brackish';
            }
            if (str_starts_with($normalized, 'salt')) {
                return 'Saltwater';
            }
            return $value; // leave other/custom values untouched
        };

        $updated = 0;
        $unchanged = 0;
        $checked = 0;

        User::query()
            ->select('user_id', 'water_type')
            ->orderBy('user_id')
            ->chunkById(200, function ($users) use ($mapToCanonical, $dryRun, &$updated, &$unchanged, &$checked) {
                foreach ($users as $user) {
                    $checked++;
                    $current = $user->water_type;
                    $canonical = $mapToCanonical($current);
                    if ($canonical === $current) {
                        $unchanged++;
                        continue;
                    }

                    $this->line("#{$user->user_id}: '{$current}' -> '{$canonical}'");
                    if (!$dryRun) {
                        $user->water_type = $canonical;
                        $user->save();
                    }
                    $updated++;
                }
            });

        $this->info("Checked: {$checked}, Updated: {$updated}, Unchanged: {$unchanged}");
        if ($dryRun) {
            $this->info('Dry run only. No changes were written.');
        }

        return Command::SUCCESS;
    }
}
