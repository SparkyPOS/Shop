<?php

namespace App\Jobs;

use App\Services\ProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $afterCommit = true;

    public int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function handle(ProductSyncService $syncService): void
    {
        try {
            $syncService->syncProductById($this->productId);
        } catch (\Throwable $e) {
            Log::warning('Product sync failed', [
                'product_id' => $this->productId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

