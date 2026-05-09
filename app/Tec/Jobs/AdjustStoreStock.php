<?php

namespace App\Tec\Jobs;

use App\Models\User;
use App\Models\Sma\Product\StockCount;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class AdjustStoreStock implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public StockCount $stockCount, public User $user) {}

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            // (new WithoutOverlapping($this->stockCount->id))->dontRelease(),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $items = $this->stockCount->items()->with([
            'product' => fn ($q) => $q->without('taxes'),
            'product.storeStock', 'variation.storeStock',
        ])->get();

        foreach ($items as $item) {
            if ($item->variation) {
                $item->variation->storeStock->setBalance(0, ['description' => __('Reset store stock for {stock_count}', ['stock_count' => '<a class="link" href="' . route('stock_counts.index', ['id' => $this->stockCount->id], false) . '">' . (__('Stock Count') . ' #' . $this->stockCount->id) . '</a>'])]);
                $item->variation->storeStock->setBalance($item->in_store_quantity, ['description' => __('Set store stock for {stock_count}', ['stock_count' => '<a class="link" href="' . route('stock_counts.index', ['id' => $this->stockCount->id], false) . '">' . (__('Stock Count') . ' #' . $this->stockCount->id) . '</a>'])]);
            } else {
                $item->product->storeStock->setBalance(0, ['description' => __('Reset store stock for {stock_count}', ['stock_count' => '<a class="link" href="' . route('stock_counts.index', ['id' => $this->stockCount->id], false) . '">' . (__('Stock Count') . ' #' . $this->stockCount->id) . '</a>'])]);
                $item->product->storeStock->setBalance($item->in_store_quantity, ['description' => __('Set store stock for {stock_count}', ['stock_count' => '<a class="link" href="' . route('stock_counts.index', ['id' => $this->stockCount->id], false) . '">' . (__('Stock Count') . ' #' . $this->stockCount->id) . '</a>'])]);
            }
        }

        $this->stockCount->update(['adjusted_at' => now(), 'adjusted_by' => $this->user->id]);
    }
}
