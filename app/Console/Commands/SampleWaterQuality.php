<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\WaterQualityData;
use App\Events\WaterQualityCreated;
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

        $generate = function($i, $user) use ($startOpt, $noBroadcast) {
            $recordedAt = $startOpt ? Carbon::parse($startOpt)->addSeconds($i) : Carbon::now();

            $temperature = round(24 + lcg_value() * 7, 1);
            $ph = round(6 + lcg_value() * 2.5, 2);
            $dissolvedOxygen = round(3 + lcg_value() * 6, 2);
            $salinity = round(lcg_value() * 15, 2);

            $data = WaterQualityData::create([
                'user_id' => $user->user_id,
                'recorded_at' => $recordedAt->toDateTimeString(),
                'temperature' => $temperature,
                'ph_level' => $ph,
                'dissolved_oxygen' => $dissolvedOxygen,
                'salinity' => $salinity,
            ]);

            if (!$noBroadcast) {
                event(new WaterQualityCreated($data));
            }

            $this->line(sprintf('[%s] user=%s id=%d temp=%s pH=%s DO=%s sal=%s', $recordedAt->toDateTimeString(), $user->email, $data->id, $temperature, $ph, $dissolvedOxygen, $salinity));
        };

        // If loop is true and count omitted, run until Ctrl+C
        if ($loop) {
            $i = 0;
            while (true) {
                foreach ($users as $user) {
                    $generate($i, $user);
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
                    $generate($i * $interval, $user);
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
