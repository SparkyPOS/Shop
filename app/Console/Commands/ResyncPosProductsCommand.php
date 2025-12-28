<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Product\Entities\Product;
use App\Jobs\SyncProductJob;
use App\Services\ProductSyncService;

class ResyncPosProductsCommand extends Command
{
    protected $signature = 'possync:resync-products
        {--ids= : Comma-separated product IDs to resync}
        {--since= : Only products updated at or after this datetime (Y-m-d or Y-m-d H:i)}
        {--chunk=200 : Chunk size for processing}
        {--queue : Dispatch jobs to queue instead of inline}
        {--sleep=0 : Sleep milliseconds between items when running inline}';

    protected $description = 'Push all (or filtered) Shop products to SparkyPOS via the existing sync service';

    public function handle(): int
    {
        $idsOpt = $this->option('ids');
        $sinceOpt = $this->option('since');
        $chunk = (int) $this->option('chunk');
        $queue = (bool) $this->option('queue');
        $sleepMs = (int) $this->option('sleep');

        $query = Product::query();
        if (!empty($idsOpt)) {
            $ids = collect(explode(',', $idsOpt))->filter()->map(fn($v) => (int) trim($v))->all();
            $query->whereIn('id', $ids);
        }
        if (!empty($sinceOpt)) {
            $query->where('updated_at', '>=', $sinceOpt);
        }

        $count = $query->count();
        $this->info("Resyncing {$count} product(s) to POS...");

        $processed = 0;
        $query->orderBy('id')->chunkById($chunk, function($rows) use (&$processed, $queue, $sleepMs) {
            foreach ($rows as $product) {
                if ($queue) {
                    SyncProductJob::dispatch($product->id)->onQueue('default');
                } else {
                    app(ProductSyncService::class)->syncProductById($product->id);
                    if ($sleepMs > 0) { usleep($sleepMs * 1000); }
                }
                $processed++;
            }
        }, 'id');

        $this->info("Done. Scheduled/processed {$processed} product(s).");
        return Command::SUCCESS;
    }
}

