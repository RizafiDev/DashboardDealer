<?php

namespace App\Filament\Resources\StokMobilResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class ServiceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceRecords';
    protected static ?string $title = 'Service Record';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('tanggal_service')->required(),
            Forms\Components\TextInput::make('jenis_service')->required()->maxLength(100),
            Forms\Components\TextInput::make('dealer')->maxLength(100),
            Forms\Components\TextInput::make('harga_service')->required()->numeric()->prefix('Rp'),
            Forms\Components\Textarea::make('keterangan')->rows(2),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_service')->date(),
                Tables\Columns\TextColumn::make('jenis_service'),
                Tables\Columns\TextColumn::make('dealer'),
                Tables\Columns\TextColumn::make('harga_service')->money('IDR'),
                Tables\Columns\TextColumn::make('keterangan')->limit(30),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}