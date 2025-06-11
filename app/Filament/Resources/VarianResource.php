<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VarianResource\Pages;
use App\Models\Varian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VarianResource extends Resource
{
    protected static ?string $model = Varian::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Data Mobil';
    protected static ?string $navigationLabel = 'Varian Mobil';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        $isCreate = request()->routeIs('filament.resources.varian.create');
        return $form
            ->schema([
                \Filament\Forms\Components\Wizard::make([
                    \Filament\Forms\Components\Wizard\Step::make('Informasi Dasar')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Select::make('mobil_id')
                                ->relationship('mobil', 'nama')
                                ->required()
                                ->searchable()
                                ->preload(),
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

                    \Filament\Forms\Components\Wizard\Step::make('Spesifikasi Mesin')
                        ->icon('heroicon-o-cog')
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
                            Forms\Components\TextInput::make('konsumsi_bbm_kota')
                                ->numeric()
                                ->suffix('km/liter'),
                            Forms\Components\TextInput::make('konsumsi_bbm_luar_kota')
                                ->numeric()
                                ->suffix('km/liter'),
                        ])
                        ->columns(2),

                    \Filament\Forms\Components\Wizard\Step::make('Dimensi')
                        ->icon('heroicon-o-arrows-pointing-out')
                        ->schema([
                            Forms\Components\TextInput::make('panjang_mm')
                                ->numeric()
                                ->suffix('mm'),
                            Forms\Components\TextInput::make('lebar_mm')
                                ->numeric()
                                ->suffix('mm'),
                            Forms\Components\TextInput::make('tinggi_mm')
                                ->numeric()
                                ->suffix('mm'),
                            Forms\Components\TextInput::make('berat_kosong_kg')
                                ->numeric()
                                ->suffix('kg'),
                            Forms\Components\TextInput::make('berat_kotor_kg')
                                ->numeric()
                                ->suffix('kg'),
                            Forms\Components\TextInput::make('wheelbase_mm')
                                ->numeric()
                                ->suffix('mm'),
                            Forms\Components\TextInput::make('ground_clearance_mm')
                                ->numeric()
                                ->suffix('mm'),
                            Forms\Components\TextInput::make('kapasitas_bagasi_liter')
                                ->numeric()
                                ->suffix('liter'),
                            Forms\Components\TextInput::make('kapasitas_tangki_liter')
                                ->numeric()
                                ->suffix('liter'),
                        ])
                        ->columns(3),

                    \Filament\Forms\Components\Wizard\Step::make('Fitur Keselamatan')
                        ->icon('heroicon-o-shield-check')
                        ->schema([
                            Forms\Components\Toggle::make('airbag'),
                            Forms\Components\TextInput::make('jumlah_airbag')
                                ->numeric()
                                ->visible(fn (Forms\Get $get) => $get('airbag')),
                            Forms\Components\Toggle::make('abs'),
                            Forms\Components\Toggle::make('ebd'),
                            Forms\Components\Toggle::make('ba'),
                            Forms\Components\Toggle::make('esc'),
                            Forms\Components\Toggle::make('hill_start_assist'),
                            Forms\Components\Toggle::make('kamera_belakang'),
                            Forms\Components\Toggle::make('sensor_parkir'),
                        ])
                        ->columns(3),

                    \Filament\Forms\Components\Wizard\Step::make('Fitur Kenyamanan')
                        ->icon('heroicon-o-cube-transparent')
                        ->schema([
                            Forms\Components\Toggle::make('ac'),
                            Forms\Components\Toggle::make('ac_double_blower'),
                            Forms\Components\Toggle::make('power_steering'),
                            Forms\Components\Toggle::make('power_window'),
                            Forms\Components\Toggle::make('central_lock'),
                            Forms\Components\Toggle::make('audio_system'),
                            Forms\Components\Toggle::make('bluetooth'),
                            Forms\Components\Toggle::make('usb_port'),
                            Forms\Components\Toggle::make('wireless_charging'),
                            Forms\Components\Toggle::make('sunroof'),
                            Forms\Components\Toggle::make('cruise_control'),
                            Forms\Components\Toggle::make('keyless_entry'),
                            Forms\Components\Toggle::make('push_start_button'),
                        ])
                        ->columns(3),

                    \Filament\Forms\Components\Wizard\Step::make('Velg dan Ban')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Forms\Components\Select::make('jenis_velg')
                                ->options([
                                    'alloy' => 'Alloy',
                                    'steel' => 'Steel',
                                ]),
                            Forms\Components\TextInput::make('ukuran_ban')
                                ->placeholder('185/65 R15'),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull()
                ->skippable(! $isCreate),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mobil.nama')
                    ->sortable()
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mobil')
                    ->relationship('mobil', 'nama'),
                Tables\Filters\SelectFilter::make('jenis_mesin')
                    ->options([
                        'bensin' => 'Bensin',
                        'diesel' => 'Diesel',
                        'hybrid' => 'Hybrid',
                        'listrik' => 'Listrik',
                    ]),
                Tables\Filters\SelectFilter::make('transmisi')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                        'cvt' => 'CVT',
                        'amt' => 'AMT',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Tambahkan ini
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVarians::route('/'),
            'create' => Pages\CreateVarian::route('/create'),
            'edit' => Pages\EditVarian::route('/{record}/edit'),
            'view' => Pages\ViewVarian::route('/{record}'), // Tambahkan ini
        ];
    }
}