<?php

namespace App\Filament\Resources\PengaturanKantorResource\Pages;

use App\Filament\Resources\PengaturanKantorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanKantors extends ListRecords
{
    protected static string $resource = PengaturanKantorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
