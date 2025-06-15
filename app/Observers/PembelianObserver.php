<?php

namespace App\Observers;

use App\Models\Pembelian;
use App\Models\StokMobil;
use App\Models\Pembayaran;

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

        // Automatically create a payment record for the Down Payment (DP) if it exists
        if ($pembelian->dp > 0) {
            Pembayaran::create([
                'pembelian_id' => $pembelian->id,
                'no_kwitansi' => Pembayaran::generateNoKwitansi(),
                'jumlah' => $pembelian->dp,
                'tanggal_bayar' => $pembelian->tanggal_pembelian,
                'jenis' => 'dp',
                'metode_pembayaran_utama' => 'cash', // Default method, can be adjusted
                'untuk_pembayaran' => 'Down Payment Awal (Otomatis)',
                'keterangan' => 'Pembayaran DP dibuat secara otomatis saat transaksi dibuat.',
            ]);

            // Update status pembelian menjadi 'in_progress' jika ada DP
            $pembelian->status = 'in_progress';
            $pembelian->saveQuietly(); // saveQuietly to avoid triggering observers again
        }
    }

    /**
     * Handle the Pembelian "updated" event.
     */
    public function updated(Pembelian $pembelian): void
    {
        // Update status stok berdasarkan status pembayaran
        if ($pembelian->stokMobil) {
            // Cek apakah pembayaran sudah lunas
            if ($pembelian->sisa_pembayaran_aktual <= 0) {
                // Jika sudah lunas, update status pembelian dan stok
                $pembelian->status = 'completed';
                $pembelian->saveQuietly();
                $pembelian->stokMobil->status = 'sold';
                $pembelian->stokMobil->save();
                return; // Keluar dari method karena sudah selesai
            }

            // Jika belum lunas, update status berdasarkan status pembelian
            switch ($pembelian->status) {
                case 'completed':
                    $pembelian->stokMobil->status = 'sold';
                    break;
                case 'cancelled':
                    $pembelian->stokMobil->status = 'ready';
                    break;
                case 'booking':
                case 'in_progress':
                    $pembelian->stokMobil->status = 'booking';
                    break;
            }
            $pembelian->stokMobil->save();
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
