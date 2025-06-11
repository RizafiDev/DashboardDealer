<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MobilResource\Pages;
use App\Filament\Resources\MobilResource\RelationManagers;
use App\Models\Mobil;
use App\Models\Merek;
use App\Models\Kategori;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;

class MobilResource extends Resource
{
    protected static ?string $model = Mobil::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Data Mobil';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Mobil';
    public static function form(Form $form): Form
    {
        // Cek context halaman
        $isCreate = request()->routeIs('filament.resources.mobil.create');

        return $form
            ->schema([
                Wizard::make([
                    Step::make('Informasi Dasar')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Forms\Components\Select::make('merek_id')
                                ->relationship('merek', 'nama')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('kategori_id')
                                ->relationship('kategori', 'nama')
                                ->required()
                                ->searchable()
                                ->preload(),
                            Forms\Components\TextInput::make('nama')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('model')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('kapasitas_penumpang')
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'active' => 'Active',
                                    'discontinued' => 'Discontinued',
                                ])
                                ->default('active')
                                ->required(),
                        ])
                        ->columns(2),

                    Step::make('Periode Produksi')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            Forms\Components\TextInput::make('tahun_mulai')
                                ->required()
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue(date('Y') + 5),
                            Forms\Components\TextInput::make('tahun_akhir')
                                ->numeric()
                                ->minValue(1900)
                                ->maxValue(date('Y') + 5)
                                ->gte('tahun_mulai'),
                        ])
                        ->columns(2),

                    Step::make('Deskripsi')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Textarea::make('deskripsi')
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    Step::make('Foto Mobil')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Forms\Components\Repeater::make('fotos')
                                ->relationship()
                                ->schema([
                                    Forms\Components\FileUpload::make('foto_path')
                                        ->image()
                                        ->directory('mobil-photos')
                                        ->disk('public')
                                        ->imageEditor()
                                        ->maxSize(5120)
                                        ->required(),
                                    Forms\Components\Select::make('foto_type')
                                        ->options([
                                            'main' => 'Main Photo',
                                            'thumbnail' => 'Thumbnail',
                                            'gallery' => 'Gallery',
                                        ])
                                        ->default('gallery')
                                        ->required(),
                                    Forms\Components\TextInput::make('urutan')
                                        ->numeric()
                                        ->default(0),
                                    Forms\Components\TextInput::make('alt_text')
                                        ->maxLength(255),
                                ])
                                ->columns(2)
                                ->reorderable('urutan')
                                ->collapsible(),
                        ]),
                ])
                ->columnSpanFull()
                ->skippable(! $isCreate), // Bisa lompat step jika BUKAN create
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('fotos.foto_path')
                    ->getStateUsing(fn ($record) => $record->fotos->where('foto_type', 'thumbnail')->first()?->foto_path ?? $record->fotos->first()?->foto_path)
                    ->disk('public')
                    ->height(50)
                    ->width(80),
                Tables\Columns\TextColumn::make('merek.nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kategori.nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_mulai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tahun_akhir')
                    ->sortable()
                    ->placeholder('Masih diproduksi'),
                Tables\Columns\TextColumn::make('kapasitas_penumpang')
                    ->suffix(' orang'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'discontinued',
                    ]),
                Tables\Columns\TextColumn::make('varians_count')
                    ->counts('varians')
                    ->label('Varian'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('merek')
                    ->relationship('merek', 'nama'),
                Tables\Filters\SelectFilter::make('kategori')
                    ->relationship('kategori', 'nama'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'discontinued' => 'Discontinued',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(), // Tambahkan ini
                \Filament\Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMobils::route('/'),
            'create' => Pages\CreateMobil::route('/create'),
            'edit' => Pages\EditMobil::route('/{record}/edit'),
            'view' => Pages\ViewMobil::route('/{record}'), // Tambahkan ini
        ];
    }
}