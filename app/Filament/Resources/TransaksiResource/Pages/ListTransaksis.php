<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use App\Filament\Resources\TransaksiResource\Widgets\TransaksiChart;
use App\Filament\Resources\TransaksiResource\Widgets\TransaksiStatsOverview;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;


class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('filters')
                ->label('Filter')
                ->form([
                    DatePicker::make('startDate')
                        ->label('Dari Tanggal')
                        ->default(now()->subDays(29)),
                    DatePicker::make('endDate')
                        ->label('Sampai Tanggal')
                        ->default(now()),
                ])
                ->action(function (array $data): void {
                    $this->tableFilters['startDate'] = $data['startDate'];
                    $this->tableFilters['endDate'] = $data['endDate'];
                })
                ->modalAlignment(Alignment::Center)
                ->modalWidth('md'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransaksiStatsOverview::class,
            TransaksiChart::class,
        ];
    }

}