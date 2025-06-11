<?php

namespace App\Filament\Resources\VarianResource\Pages;

use App\Filament\Resources\VarianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVarians extends ListRecords
{
    protected static string $resource = VarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
