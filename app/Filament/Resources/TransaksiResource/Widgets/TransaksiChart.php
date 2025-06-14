<?php

namespace App\Filament\Resources\TransaksiResource\Widgets;

use App\Models\Transaksi;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class TransaksiChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Grafik Transaksi (Pemasukan vs Pengeluaran)';

    protected static ?string $maxHeight = '300px';

    // Make the widget collapsible and start collapsed
    protected static bool $isCollapsible = true;
    
    // Override the default state to be collapsed
    public function getDefaultTableRecordsPerPageSelectOption(): ?int
    {
        return null;
    }

    // Make it start collapsed by default
    protected function getDefaultState(): array
    {
        return [
            'isCollapsed' => true,
        ];
    }

    // Alternative approach - override the getHeading method to include collapse functionality
    public function getHeading(): ?string
    {
        return static::$heading;
    }

    // Set the widget to be collapsed by default
    public bool $isCollapsed = true;

    protected function getData(): array
    {
        // Get dates from the page filters, with a default of the last 30 days
        $startDate = Carbon::parse($this->filters['startDate'] ?? now()->subDays(29));
        $endDate = Carbon::parse($this->filters['endDate'] ?? now());

        // Fetch and group data
        $incomeData = Transaksi::where('tipe', 'income')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $expenseData = Transaksi::where('tipe', 'expense')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->selectRaw('DATE(tanggal) as date, SUM(jumlah) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Prepare data structure for the chart
        $period = CarbonPeriod::create($startDate, $endDate);
        $labels = [];
        $incomeValues = [];
        $expenseValues = [];

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $incomeValues[] = $incomeData[$formattedDate] ?? 0;
            $expenseValues[] = $expenseData[$formattedDate] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $incomeValues,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expenseValues,
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    // Override the getOptions method to ensure proper chart configuration
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}