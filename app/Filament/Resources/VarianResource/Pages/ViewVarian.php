<?php

namespace App\Filament\Resources\VarianResource\Pages;

use App\Filament\Resources\VarianResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions;

class ViewVarian extends ViewRecord
{
    protected static string $resource = VarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}