<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Queue\Events\JobQueued;

final class DrainQueueAfterResponse
{
    public const CONTAINER_KEY = 'queue.draining-after-response';

    public const WORKER_TIMEOUT = 120;

    public const WORKER_TRIES = 3;

    public function __construct(
        private Application $app,
        private Repository $config,
        private Kernel $artisan,
    ) {}

    public function handle(JobQueued $event): void
    {
        if (! $this->shouldDrain($event->connectionName)) {
            return;
        }

        $this->app->instance(self::CONTAINER_KEY, true);

        $artisan = $this->artisan;

        $this->app->terminating(function () use ($artisan): void {
            $artisan->call('queue:work', [
                '--stop-when-empty' => true,
                '--tries' => self::WORKER_TRIES,
                '--timeout' => self::WORKER_TIMEOUT,
                '--sleep' => 0,
            ]);
        });
    }

    private function shouldDrain(string $connectionName): bool
    {
        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            return false;
        }

        if ($this->app->bound(self::CONTAINER_KEY)) {
            return false;
        }

        $driver = $this->config->get("queue.connections.{$connectionName}.driver", $connectionName);

        return ! in_array($driver, ['sync', 'null'], true);
    }
}
