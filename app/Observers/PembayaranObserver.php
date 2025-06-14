<?php

namespace App\Observers;

use App\Models\Pembayaran;
use App\Models\Pembelian;
use App\Models\Transaksi;

class PembayaranObserver
{
    /**
     * Handle the Pembayaran "created" event.
     */
    public function created(Pembayaran $pembayaran): void
    {
        $this->updatePembelianStatus($pembayaran->pembelian);

        // Create a financial transaction record
        Transaksi::create([
            'referensi_id' => $pembayaran->id,
            'referensi_type' => Pembayaran::class,
            'tanggal' => $pembayaran->tanggal_bayar,
            'jumlah' => $pembayaran->jumlah,
            'tipe' => 'income',
            'kategori' => 'Penjualan Mobil',
            'deskripsi' => "Pembayaran {$pembayaran->jenis} untuk faktur {$pembayaran->pembelian->no_faktur}",
        ]);
    }

    /**
     * Handle the Pembayaran "updated" event.
     */
    public function updated(Pembayaran $pembayaran): void
    {
        $this->updatePembelianStatus($pembayaran->pembelian);

        // Update financial transaction if amount or date changes
        $transaksi = Transaksi::where('referensi_id', $pembayaran->id)
                              ->where('referensi_type', Pembayaran::class)
                              ->first();
        if ($transaksi) {
            $transaksi->update([
                'tanggal' => $pembayaran->tanggal_bayar,
                'jumlah' => $pembayaran->jumlah,
            ]);
        }
    }

    /**
     * Handle the Pembayaran "deleted" event.
     */
    public function deleted(Pembayaran $pembayaran): void
    {
        $this->updatePembelianStatus($pembayaran->pembelian);

        // Delete the associated financial transaction
        Transaksi::where('referensi_id', $pembayaran->id)
                 ->where('referensi_type', Pembayaran::class)
                 ->delete();
    }

    /**
     * Update the status of the related Pembelian record.
     */
    protected function updatePembelianStatus(Pembelian $pembelian): void
    {
        $pembelian->refresh(); // Ensure we have the latest data
        $sisa = $pembelian->sisa_pembayaran_aktual;
        $totalBayar = $pembelian->total_bayar;

        if ($sisa <= 0) {
            $pembelian->status = 'completed';
        } elseif ($totalBayar > 0) {
            $pembelian->status = 'in_progress'; // Or 'dp_paid' if you want more granularity
        } else {
            $pembelian->status = 'booking';
        }
        
        $pembelian->saveQuietly(); // Use saveQuietly to avoid triggering infinite loops
    }
}
