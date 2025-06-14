<?php

namespace App\Filament\Resources\AkunKaryawanResource\Pages;

use App\Filament\Resources\AkunKaryawanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAkunKaryawan extends EditRecord
{
    protected static string $resource = AkunKaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
