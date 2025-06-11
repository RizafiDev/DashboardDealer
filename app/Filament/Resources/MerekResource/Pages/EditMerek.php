<?php

namespace App\Filament\Resources\MerekResource\Pages;

use App\Filament\Resources\MerekResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMerek extends EditRecord
{
    protected static string $resource = MerekResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
