<?php

namespace App\Filament\Resources\PengaturanKantorResource\Pages;

use App\Filament\Resources\PengaturanKantorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanKantor extends EditRecord
{
    protected static string $resource = PengaturanKantorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
