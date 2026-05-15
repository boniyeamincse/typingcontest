<?php

namespace App\Jobs\Social;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class GenerateFeedSnapshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'default';

    public function __construct(private readonly int $userId)
    {
    }

    public function handle(): void
    {
        DB::table('social_feed_items')->insert([
            'user_id' => $this->userId,
            'type' => 'snapshot',
            'title' => 'Feed refreshed',
            'payload' => json_encode(['generated_at' => now()->toISOString()]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
