<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresensiResource\Pages;
use App\Models\Presensi;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PresensiResource extends Resource
{
    protected static ?string $model = Presensi::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Data Presensi';
    protected static ?string $pluralLabel = 'Data Presensi';
    protected static ?string $navigationGroup = 'Karyawan & Presensi';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Presensi')
                    ->schema([
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Karyawan')
                            ->relationship('karyawan', 'nama')
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal')
                            ->required()
                            ->default(now()),
                        Forms\Components\TimePicker::make('jam_masuk')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('jam_pulang')
                            ->seconds(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'hadir' => 'Hadir',
                                'terlambat' => 'Terlambat',
                                'tidak_hadir' => 'Tidak Hadir',
                                'sakit' => 'Sakit',
                                'izin' => 'Izin',
                                'libur' => 'Libur',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Foto & Lokasi')
                    ->schema([
                        Forms\Components\FileUpload::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->image()
                            ->directory('presensi')
                            ->imageEditor(),
                        Forms\Components\FileUpload::make('foto_pulang')
                            ->label('Foto Pulang')
                            ->image()
                            ->directory('presensi')
                            ->imageEditor(),
                        Forms\Components\Textarea::make('lokasi_masuk')
                            ->label('Lokasi Masuk (JSON)')
                            ->helperText('Format: {"lat": -7.123, "lng": 110.456, "alamat": "Jl. Contoh"}'),
                        Forms\Components\Textarea::make('lokasi_pulang')
                            ->label('Lokasi Pulang (JSON)')
                            ->helperText('Format: {"lat": -7.123, "lng": 110.456, "alamat": "Jl. Contoh"}'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('karyawan.nip')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->time()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jam_pulang')
                    ->time()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'hadir',
                        'warning' => 'terlambat',
                        'danger' => 'tidak_hadir',
                        'info' => ['sakit', 'izin'],
                        'secondary' => 'libur',
                    ]),
                Tables\Columns\TextColumn::make('jam_kerja')
                    ->label('Jam Kerja')
                    ->suffix(' jam')
                    ->numeric(2),
                Tables\Columns\IconColumn::make('terlambat')
                    ->boolean(),
                Tables\Columns\TextColumn::make('menit_terlambat')
                    ->label('Terlambat (menit)')
                    ->numeric(),
                Tables\Columns\ImageColumn::make('foto_masuk')
                    ->size(40)
                    ->circular(),
            ])
            ->filters([
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'hadir' => 'Hadir',
                        'terlambat' => 'Terlambat',
                        'tidak_hadir' => 'Tidak Hadir',
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                        'libur' => 'Libur',
                    ]),
                Tables\Filters\SelectFilter::make('karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPresensis::route('/'),
            'create' => Pages\CreatePresensi::route('/create'),
            // 'view' => Pages\ViewPresensi::route('/{record}'),
            'edit' => Pages\EditPresensi::route('/{record}/edit'),
        ];
    }
}