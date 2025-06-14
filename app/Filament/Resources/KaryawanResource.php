<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Data Karyawan';
    protected static ?string $pluralLabel = 'Data Karyawan';

    protected static ?string $navigationGroup = 'Karyawan & Presensi';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('akun_karyawan_id')
                            ->label('Akun Karyawan')
                            ->relationship(
                                'akunKaryawan',
                                'username',
                                fn($query) => $query->whereDoesntHave('karyawan')
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih akun karyawan')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $akunKaryawan = \App\Models\AkunKaryawan::find($state);
                                    if ($akunKaryawan) {
                                        $set('email', $akunKaryawan->email);
                                    }
                                }
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('akun_karyawans', 'username'),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('akun_karyawans', 'email'),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->minLength(8)
                                    ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->readonly(),
                        Forms\Components\TextInput::make('telepon')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\FileUpload::make('foto')
                            ->image()
                            ->directory('karyawan')
                            ->imageEditor(),
                        Forms\Components\Textarea::make('alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Informasi Pekerjaan')
                    ->schema([
                        Forms\Components\TextInput::make('jabatan')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('departemen')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\DatePicker::make('tanggal_masuk')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('status')
                            ->options([
                                'aktif' => 'Aktif',
                                'nonaktif' => 'Non-Aktif',
                            ])
                            ->default('aktif')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departemen')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'aktif',
                        'danger' => 'nonaktif',
                    ]),
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Non-Aktif',
                    ]),
                Tables\Filters\SelectFilter::make('departemen')
                    ->options(function () {
                        return Karyawan::whereNotNull('departemen')
                            ->distinct()
                            ->pluck('departemen', 'departemen')
                            ->toArray();
                    }),
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            // 'view' => Pages\ViewKaryawan::route('/{record}'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }
}