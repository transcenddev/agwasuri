<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteWaterQualityForUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'water-quality:delete-user {user_id : The user_id to delete data for} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all water_quality_data rows for a given user_id (irreversible)';

    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        if ($userId <= 0) {
            $this->error('Invalid user_id specified.');
            return 1;
        }

        $count = DB::table('water_quality_data')->where('user_id', $userId)->count();

        if ($count === 0) {
            $this->info("No water_quality_data rows found for user_id {$userId}.");
            return 0;
        }

        $this->info("Found {$count} rows for user_id {$userId}.");

        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to permanently delete these rows? This action cannot be undone.')) {
                $this->info('Aborted. No rows were deleted.');
                return 0;
            }
        }

        DB::beginTransaction();
        try {
            $deleted = DB::table('water_quality_data')->where('user_id', $userId)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to delete rows: ' . $e->getMessage());
            return 1;
        }

        $this->info("Deleted {$deleted} rows for user_id {$userId}.");
        return 0;
    }
}
