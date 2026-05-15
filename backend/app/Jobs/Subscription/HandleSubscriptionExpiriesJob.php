<?php

namespace App\Jobs\Subscription;

use App\Models\UserSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleSubscriptionExpiriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'subscriptions';

    public function handle(): void
    {
        UserSubscription::query()
            ->with('user')
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    if ($row->grace_ends_at && now()->lessThanOrEqualTo($row->grace_ends_at)) {
                        $row->update(['status' => 'grace']);
                        continue;
                    }

                    $row->update([
                        'status' => 'expired',
                        'expired_at' => now(),
                    ]);

                    if ($row->user) {
                        $row->user->forceFill([
                            'plan_type' => 'free',
                            'subscription_status' => 'active',
                            'subscription_end_date' => null,
                        ])->save();
                    }
                }
            });
    }
}
