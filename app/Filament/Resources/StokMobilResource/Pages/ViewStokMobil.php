<?php

namespace App\Filament\Resources\StokMobilResource\Pages;

use App\Filament\Resources\StokMobilResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;

class ViewStokMobil extends ViewRecord
{
    protected static string $resource = StokMobilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}