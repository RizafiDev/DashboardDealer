<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Models\StokMobil;

class PembelianObserver
{
    /**
     * Handle the Pembelian "created" event.
     */
    public function created(Pembelian $pembelian): void
    {
        // Set stok to 'booking' when a purchase order is created
        if ($pembelian->stokMobil) {
            $pembelian->stokMobil->status = 'booking';
            $pembelian->stokMobil->save();
        }
    }

    /**
     * Handle the Pembelian "updated" event.
     */
    public function updated(Pembelian $pembelian): void
    {
        if ($pembelian->isDirty('status')) {
            $stok = $pembelian->stokMobil;
            if ($stok) {
                switch ($pembelian->status) {
                    case 'completed':
                        $stok->status = 'sold';
                        break;
                    case 'cancelled':
                        $stok->status = 'ready';
                        break;
                    case 'booking':
                    case 'in_progress':
                        $stok->status = 'booking';
                        break;
                }
                $stok->save();
            }
        }
    }

    /**
     * Handle the Pembelian "deleted" event.
     */
    public function deleted(Pembelian $pembelian): void
    {
        // Return the car to stock if the purchase is deleted
        if ($pembelian->stokMobil && $pembelian->stokMobil->status !== 'sold') {
            $pembelian->stokMobil->status = 'ready';
            $pembelian->stokMobil->save();
        }
    }
}
