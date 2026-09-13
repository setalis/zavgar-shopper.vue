<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

final class RecordQueueDrainJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Cache::put('queue-drained', true);
    }
}
