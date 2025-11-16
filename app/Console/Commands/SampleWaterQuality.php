<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\SampleWaterQualityService;
use Illuminate\Support\Carbon;

class SampleWaterQuality extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sample:water-quality
                            {--users= : Comma separated emails}
                            {--count= : Number of samples to generate (if omitted with --loop, runs until Ctrl+C)}
                            {--interval=1 : Seconds between samples}
                            {--loop : Run continuously until stopped}
                            {--start= : Optional start timestamp for generated records}
                            {--no-broadcast : Do not broadcast events}
                            {--yes : Skip safety check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sample water quality data for dev/testing (local-only by default)';

    public function handle()
    {
        if (!app()->isLocal() && !$this->option('yes')) {
            $this->error('This command is restricted to local environment. Use --yes to override.');
            return 1;
        }

        $usersOpt = $this->option('users') ?: 'admin@admin.com';
        $emails = array_map('trim', explode(',', $usersOpt));
        $users = User::whereIn('email', $emails)->get();
        if ($users->isEmpty()) {
            $this->error('No users found for emails: ' . $usersOpt);
            return 1;
        }

        $count = (int) $this->option('count');
        $interval = max(1, (int) $this->option('interval'));
        $loop = (bool) $this->option('loop');
        $startOpt = $this->option('start');
        $noBroadcast = (bool) $this->option('no-broadcast');

        $this->info(sprintf('Starting sample generation for %d user(s). interval=%ds loop=%s', $users->count(), $interval, $loop ? 'yes' : 'no'));

        foreach ($users as $user) {
            $this->info('User: ' . $user->email . ' (id=' . $user->user_id . ')');
        }

        $runCount = 0;
        $service = new SampleWaterQualityService();

        // If loop is true and count omitted, run until Ctrl+C
        if ($loop) {
            $i = 0;
            while (true) {
                foreach ($users as $user) {
                    $recordedAt = $startOpt ? Carbon::parse($startOpt)->addSeconds($i) : Carbon::now();
                    $data = $service->createSampleForUser($user, $recordedAt, $noBroadcast);
                    $this->line(sprintf('[%s] user=%s id=%d temp=%s pH=%s DO=%s sal=%s', $recordedAt->toDateTimeString(), $user->email, $data->id, $data->temperature, $data->ph_level, $data->dissolved_oxygen, $data->salinity));
                }
                $i += $interval;
                $runCount++;
                if ($count > 0 && $runCount >= $count) {
                    break;
                }
                sleep($interval);
            }
        } else {
            if ($count <= 0) {
                $this->error('Please provide --count=N or use --loop.');
                return 1;
            }

            for ($i = 0; $i < $count; $i++) {
                foreach ($users as $user) {
                    $recordedAt = $startOpt ? Carbon::parse($startOpt)->addSeconds($i * $interval) : Carbon::now();
                    $data = $service->createSampleForUser($user, $recordedAt, $noBroadcast);
                    $this->line(sprintf('[%s] user=%s id=%d temp=%s pH=%s DO=%s sal=%s', $recordedAt->toDateTimeString(), $user->email, $data->id, $data->temperature, $data->ph_level, $data->dissolved_oxygen, $data->salinity));
                }
                if ($i < $count - 1) {
                    sleep($interval);
                }
            }
        }

        $this->info('Done.');
        return 0;
    }
}
