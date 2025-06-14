<?php

namespace App\Filament\Resources\TransaksiResource\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Carbon\Carbon;
use App\Models\StokMobil;

class TransaksiStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    // Set the widget to use 2 columns for 2x2 layout
    protected int $columnsSpan = 2;

    protected function getStats(): array
    {
        // Get dates from the page filters, default to all time if not set
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Transaksi::query();

        if ($startDate) {
            $query->where('tanggal', '>=', Carbon::parse($startDate));
        }
        if ($endDate) {
            $query->where('tanggal', '<=', Carbon::parse($endDate));
        }

        $totals = $query->selectRaw('
                SUM(CASE WHEN tipe = "income" THEN jumlah ELSE 0 END) as total_income,
                SUM(CASE WHEN tipe = "expense" THEN jumlah ELSE 0 END) as total_expense
            ')
            ->first();

        $totalIncome = $totals->total_income ?? 0;
        $totalExpense = $totals->total_expense ?? 0;
        $netProfit = $totalIncome - $totalExpense;
        $totalAssets = StokMobil::where('status', 'ready')->sum('harga_beli');

        return [
            Stat::make('Total Pemasukan', Number::currency($totalIncome, 'IDR'))
                ->description('Pemasukan dalam periode terpilih')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Pengeluaran', Number::currency($totalExpense, 'IDR'))
                ->description('Pengeluaran dalam periode terpilih')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Keuntungan Bersih', Number::currency($netProfit, 'IDR'))
                ->description('Pemasukan dikurangi pengeluaran')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-exclamation-triangle')
                ->color($netProfit >= 0 ? 'primary' : 'danger'),
            Stat::make('Total Assets', Number::currency($totalAssets, 'IDR'))
                ->description('Nilai pembelian stok mobil yang masih tersedia')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),
        ];
    }

    // Override getColumns to set 2 columns for 2x2 layout
    protected function getColumns(): int
    {
        return 2;
    }
}