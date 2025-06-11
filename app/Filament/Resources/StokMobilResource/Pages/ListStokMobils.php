<?php

namespace App\Filament\Resources\StokMobilResource\Pages;

use App\Filament\Resources\StokMobilResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStokMobils extends ListRecords
{
    protected static string $resource = StokMobilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
