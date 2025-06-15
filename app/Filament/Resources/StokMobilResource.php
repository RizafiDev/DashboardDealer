<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokMobilResource\Pages;
use App\Filament\Resources\StokMobilResource\RelationManagers\ServiceRecordsRelationManager;
use App\Models\StokMobil;
use App\Models\Varian; // Import Varian model
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Get; // Import Get
use Filament\Forms\Set; // Import Set

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
                        ->preload()
                        ->live() // Penting untuk memicu pembaruan field lain
                        ->afterStateUpdated(fn(Set $set) => $set('varian_id', null)), // Reset varian_id saat mobil_id berubah

                    Forms\Components\Select::make('varian_id')
                        ->label('Varian')
                        ->options(function (Get $get) { // Gunakan Get untuk mendapatkan nilai mobil_id
                            $mobilId = $get('mobil_id');
                            if ($mobilId) {
                                return Varian::where('mobil_id', $mobilId)->pluck('nama', 'id');
                            }
                            return [];
                        })
                        ->searchable()
                        // ->preload() // Hindari preload pada select yang opsinya dinamis
                        ->required(fn(Get $get) => !empty($get('mobil_id'))) // Varian wajib jika mobil dipilih
                        ->placeholder('Pilih Mobil terlebih dahulu'),

                    Forms\Components\TextInput::make('warna')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('Contoh: Hitam, Putih, Silver'),
                    Forms\Components\TextInput::make('tahun')
                        ->required()
                        ->numeric()
                        ->minValue(1980) // Mungkin mobil tua juga ada
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
                        ->formatStateUsing(fn(?StokMobil $record) => $record && $record->laba !== null ? number_format($record->laba, 0, ',', '.') : 'Rp 0')
                        ->prefix('Rp')
                        ->helperText('Otomatis: harga jual - harga beli - total harga service'),
                    Forms\Components\DatePicker::make('tanggal_masuk')
                        ->label('Tanggal Masuk')
                        ->placeholder('Tanggal unit masuk stok'),
                    Forms\Components\TextInput::make('lokasi')
                        ->maxLength(100)
                        ->placeholder('Lokasi unit, contoh: Gudang Utama')
                        ->columnSpan(1),
                    Forms\Components\Textarea::make('kelengkapan_mobil')
                        ->label('Kelengkapan Mobil')
                        ->rows(3)
                        ->placeholder("Contoh:\n- Kunci serep ada\n- Buku manual lengkap\n- Dongkrak original")
                        ->columnSpanFull(),
                    Forms\Components\KeyValue::make('fitur_override')
                        ->label('Penyesuaian Fitur')
                        ->keyLabel('Nama Fitur')
                        ->valueLabel('Status/Keterangan Fitur')
                        ->helperText('Isi jika ada fitur standar varian yang hilang atau ada tambahan. Contoh: { "sunroof": "tidak ada", "audio_custom": "terpasang speaker JBL" }')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('keterangan')
                        ->rows(2)
                        ->placeholder('Keterangan tambahan (opsional)')
                        ->columnSpanFull(),
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
                    ->searchable()
                    ->placeholder('-'),
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
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('no_mesin')
                    ->label('No. Mesin')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('harga_beli')
                    ->money('IDR')
                    ->label('Harga Beli')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->money('IDR')
                    ->label('Harga Jual')
                    ->sortable(),
                Tables\Columns\TextColumn::make('laba') // Akan menggunakan accessor model
                    ->money('IDR')
                    ->label('Laba Bersih')
                    ->sortable() // Sorting mungkin berdasarkan laba dasar di DB
                    ->color(fn($record) => $record->laba > 0 ? 'success' : ($record->laba < 0 ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('lokasi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->date()
                    ->label('Tgl Masuk')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                // Filter Varian mungkin perlu kustomisasi jika ingin dependen juga, tapi untuk filter tabel biasanya independen
                Tables\Filters\SelectFilter::make('varian')->relationship('varian', 'nama'),
                // Tables\Filters\SelectFilter::make('warna'), // Ini akan error jika warna tidak ada di tabel relasi
                Tables\Filters\TernaryFilter::make('laba')
                    ->label('Laba Bersih > 0')
                    ->trueLabel('Untung')
                    ->falseLabel('Rugi / BEP')
                    ->queries(
                        true: fn($query) => $query->whereRaw('(harga_jual - harga_beli - (SELECT COALESCE(SUM(harga_service),0) FROM service_records WHERE service_records.stok_mobil_id = stok_mobils.id)) > 0'),
                        false: fn($query) => $query->whereRaw('(harga_jual - harga_beli - (SELECT COALESCE(SUM(harga_service),0) FROM service_records WHERE service_records.stok_mobil_id = stok_mobils.id)) <= 0'),
                        // Atau jika ingin filter berdasarkan laba dasar (DB virtual column)
                        // true: fn ($query) => $query->where('laba', '>', 0),
                        // false: fn ($query) => $query->where('laba', '<=', 0),
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
            'view' => Pages\ViewStokMobil::route('/{record}'),
        ];
    }
}
