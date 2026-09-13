<?php

declare(strict_types=1);

use App\Listeners\DrainQueueAfterResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\RecordQueueDrainJob;

uses(RefreshDatabase::class);

test('http requests drain the database queue after the response', function (): void {
    config(['queue.default' => 'database']);

    Route::post('/__queue-drain-test', function () {
        RecordQueueDrainJob::dispatch();

        return response('ok');
    });

    $this->post('/__queue-drain-test')->assertOk();

    expect(Cache::get('queue-drained'))->toBeTrue()
        ->and(DB::table('jobs')->count())->toBe(0);
});

test('database queue retry_after exceeds the after-response worker timeout', function (): void {
    expect(config('queue.connections.database.retry_after'))
        ->toBeGreaterThan(DrainQueueAfterResponse::WORKER_TIMEOUT);
});
