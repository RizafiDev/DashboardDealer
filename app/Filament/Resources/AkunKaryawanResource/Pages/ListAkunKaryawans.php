<?php

namespace App\Filament\Resources\AkunKaryawanResource\Pages;

use App\Filament\Resources\AkunKaryawanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAkunKaryawans extends ListRecords
{
    protected static string $resource = AkunKaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
