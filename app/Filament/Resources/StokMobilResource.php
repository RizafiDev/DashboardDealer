<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokMobilResource\Pages;
use App\Filament\Resources\StokMobilResource\RelationManagers\ServiceRecordsRelationManager;
use App\Models\StokMobil;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class StokMobilResource extends Resource
{
    protected static ?string $model = StokMobil::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Stok & Penjualan';
    protected static ?string $navigationLabel = 'Stok Mobil';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Stok Mobil')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('mobil_id')
                        ->label('Mobil')
                        ->relationship('mobil', 'nama')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('varian_id')
                        ->label('Varian')
                        ->relationship('varian', 'nama')
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('warna')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('Contoh: Hitam, Putih, Silver'),
                    Forms\Components\TextInput::make('tahun')
                        ->required()
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(date('Y') + 1)
                        ->placeholder(date('Y')),
                    Forms\Components\TextInput::make('no_rangka')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->placeholder('Nomor rangka kendaraan'),
                    Forms\Components\TextInput::make('no_mesin')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->placeholder('Nomor mesin kendaraan'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'ready' => 'Ready',
                            'sold' => 'Sold',
                            'booking' => 'Booking',
                            'indent' => 'Indent',
                        ])
                        ->default('ready')
                        ->required()
                        ->helperText('Status stok unit saat ini'),
                    Forms\Components\TextInput::make('harga_beli')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->placeholder('Harga beli dari dealer'),
                    Forms\Components\TextInput::make('harga_jual')
                        ->required()
                        ->numeric()
                        ->prefix('Rp')
                        ->placeholder('Harga jual ke customer'),
                    Forms\Components\TextInput::make('laba')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn ($record, $state) => $record ? number_format($record->laba, 0, ',', '.') : null)
                        ->prefix('Rp')
                        ->helperText('Otomatis: harga jual - harga beli - total harga service'),
                    Forms\Components\DatePicker::make('tanggal_masuk')
                        ->label('Tanggal Masuk')
                        ->placeholder('Tanggal unit masuk stok'),
                    Forms\Components\TextInput::make('lokasi')
                        ->maxLength(100)
                        ->placeholder('Lokasi unit, contoh: Gudang Utama'),
                    Forms\Components\Textarea::make('keterangan')
                        ->rows(2)
                        ->placeholder('Keterangan tambahan (opsional)'),
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mobil.nama')
                    ->label('Mobil')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('varian.nama')
                    ->label('Varian')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'ready',
                        'danger' => 'sold',
                        'warning' => 'booking',
                        'gray' => 'indent',
                    ])
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('warna')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('tahun')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('no_rangka')
                    ->label('No. Rangka')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('no_mesin')
                    ->label('No. Mesin')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->money('IDR')
                    ->label('Harga Beli')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->money('IDR')
                    ->label('Harga Jual')
                    ->sortable(),
                Tables\Columns\TextColumn::make('laba')
                    ->money('IDR')
                    ->label('Laba')
                    ->sortable()
                    ->color(fn ($record) => $record->laba > 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('lokasi')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->date()
                    ->label('Tgl Masuk')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'ready' => 'Ready',
                        'sold' => 'Sold',
                        'booking' => 'Booking',
                        'indent' => 'Indent',
                    ]),
                Tables\Filters\SelectFilter::make('mobil')->relationship('mobil', 'nama'),
                Tables\Filters\SelectFilter::make('varian')->relationship('varian', 'nama'),
                Tables\Filters\SelectFilter::make('warna'),
                Tables\Filters\TernaryFilter::make('laba')
                    ->label('Laba > 0')
                    ->trueLabel('Untung')
                    ->falseLabel('Rugi')
                    ->queries(
                        true: fn ($query) => $query->whereRaw('harga_jual > harga_beli'),
                        false: fn ($query) => $query->whereRaw('harga_jual <= harga_beli'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ServiceRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStokMobils::route('/'),
            'create' => Pages\CreateStokMobil::route('/create'),
            'edit' => Pages\EditStokMobil::route('/{record}/edit'),
            'view' => Pages\ViewStokMobil::route('/{record}'), // Aktifkan view page
        ];
    }
}
