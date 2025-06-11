<?php

// app/Filament/Resources/MobilResource/RelationManagers/VariansRelationManager.php
namespace App\Filament\Resources\MobilResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariansRelationManager extends RelationManager
{
    protected static string $relationship = 'varians';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('harga_otr')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\Textarea::make('deskripsi')
                            ->rows(3),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Spesifikasi Mesin')
                    ->schema([
                        Forms\Components\Select::make('jenis_mesin')
                            ->options([
                                'bensin' => 'Bensin',
                                'diesel' => 'Diesel',
                                'hybrid' => 'Hybrid',
                                'listrik' => 'Listrik',
                            ]),
                        Forms\Components\TextInput::make('kapasitas_mesin')
                            ->numeric()
                            ->suffix('CC'),
                        Forms\Components\Select::make('transmisi')
                            ->options([
                                'manual' => 'Manual',
                                'automatic' => 'Automatic',
                                'cvt' => 'CVT',
                                'amt' => 'AMT',
                            ]),
                        Forms\Components\TextInput::make('tenaga_hp')
                            ->numeric()
                            ->suffix('HP'),
                        Forms\Components\TextInput::make('torsi_nm')
                            ->numeric()
                            ->suffix('Nm'),
                        Forms\Components\TextInput::make('bahan_bakar')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                
                Forms\Components\Section::make('Fitur Keselamatan')
                    ->schema([
                        Forms\Components\Toggle::make('airbag'),
                        Forms\Components\TextInput::make('jumlah_airbag')
                            ->numeric()
                            ->visible(fn (Forms\Get $get) => $get('airbag')),
                        Forms\Components\Toggle::make('abs'),
                        Forms\Components\Toggle::make('ebd'),
                        Forms\Components\Toggle::make('esc'),
                        Forms\Components\Toggle::make('kamera_belakang'),
                    ])
                    ->columns(3)
                    ->collapsible(),
                
                Forms\Components\Section::make('Fitur Kenyamanan')
                    ->schema([
                        Forms\Components\Toggle::make('ac'),
                        Forms\Components\Toggle::make('power_steering'),
                        Forms\Components\Toggle::make('power_window'),
                        Forms\Components\Toggle::make('central_lock'),
                        Forms\Components\Toggle::make('audio_system'),
                        Forms\Components\Toggle::make('bluetooth'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_mesin')
                    ->badge()
                    ->colors([
                        'success' => 'bensin',
                        'warning' => 'diesel',
                        'info' => 'hybrid',
                        'danger' => 'listrik',
                    ]),
                Tables\Columns\TextColumn::make('transmisi')
                    ->badge(),
                Tables\Columns\TextColumn::make('harga_otr')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_mesin')
                    ->options([
                        'bensin' => 'Bensin',
                        'diesel' => 'Diesel',
                        'hybrid' => 'Hybrid',
                        'listrik' => 'Listrik',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}