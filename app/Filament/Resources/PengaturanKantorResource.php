<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanKantorResource\Pages;
use App\Filament\Resources\PengaturanKantorResource\RelationManagers;
use App\Models\PengaturanKantor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

namespace App\Filament\Resources;

use App\Filament\Resources\PengaturanKantorResource\Pages;
use App\Models\PengaturanKantor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengaturanKantorResource extends Resource
{
    protected static ?string $model = PengaturanKantor::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Pengaturan Kantor';
    protected static ?string $pluralLabel = 'Pengaturan Kantor';
    protected static ?string $navigationGroup = 'Karyawan & Presensi';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kantor')
                    ->schema([
                        Forms\Components\TextInput::make('nama_kantor')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('alamat_kantor')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Koordinat Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->required()
                            ->numeric()
                            ->step(0.00000001)
                            ->helperText('Contoh: -7.7956'),
                        Forms\Components\TextInput::make('longitude')
                            ->required()
                            ->numeric()
                            ->step(0.00000001)
                            ->helperText('Contoh: 110.3695'),
                        Forms\Components\TextInput::make('radius_meter')
                            ->label('Radius (Meter)')
                            ->required()
                            ->numeric()
                            ->default(100)
                            ->helperText('Jarak maksimal untuk presensi'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Jam Kerja')
                    ->schema([
                        Forms\Components\TimePicker::make('jam_masuk')
                            ->required()
                            ->default('08:00')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('jam_pulang')
                            ->required()
                            ->default('17:00')
                            ->seconds(false),
                        Forms\Components\TextInput::make('toleransi_terlambat')
                            ->label('Toleransi Terlambat (Menit)')
                            ->required()
                            ->numeric()
                            ->default(15),
                        Forms\Components\Toggle::make('aktif')
                            ->default(true)
                            ->helperText('Aktifkan pengaturan ini'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_kantor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alamat_kantor')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->numeric(8),
                Tables\Columns\TextColumn::make('longitude')
                    ->numeric(8),
                Tables\Columns\TextColumn::make('radius_meter')
                    ->label('Radius (m)')
                    ->numeric(),
                Tables\Columns\TextColumn::make('jam_masuk')
                    ->time(),
                Tables\Columns\TextColumn::make('jam_pulang')
                    ->time(),
                Tables\Columns\TextColumn::make('toleransi_terlambat')
                    ->label('Toleransi (mnt)')
                    ->numeric(),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('aktif'),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengaturanKantors::route('/'),
            'create' => Pages\CreatePengaturanKantor::route('/create'),
            // 'view' => Pages\ViewPengaturanKantor::route('/{record}'),
            'edit' => Pages\EditPengaturanKantor::route('/{record}/edit'),
        ];
    }
}