<?php

namespace Plugins\Analytics\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Plugins\Analytics\Services\AnalyticsSnapshotter;

class SnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:snapshot {--user= : Capture only the given user id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store the analytics snapshot of the day';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsSnapshotter $snapshotter): int
    {
        $userId = $this->option('user');

        if ($userId !== null) {
            $user = User::query()->find($userId);

            if ($user === null) {
                $this->error("User [{$userId}] was not found.");

                return self::FAILURE;
            }

            $snapshotter->capture($user);
            $this->components->info("Snapshot for user [{$user->getKey()}] stored.");

            return self::SUCCESS;
        }

        $snapshotter->capture();

        User::query()->each(fn (User $user) => $snapshotter->capture($user));

        $this->components->info('Global and per-user snapshots stored.');

        return self::SUCCESS;
    }
}
