<?php

namespace App\Listeners;

use App\Events\BorrowingCreated;
use App\Events\BorrowingReturned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateStockStatus
{
    public function handle($event)
    {
        if ($event instanceof BorrowingCreated) {
            $stock = $event->borrowing->stock;
            $stock->update([
                'status' => 'borrowed',
                'is_available' => false
            ]);
        }
        
        if ($event instanceof BorrowingReturned) {
            $stock = $event->borrowing->stock;
            $stock->update([
                'status' => 'available',
                'is_available' => true
            ]);
        }
    }
} 