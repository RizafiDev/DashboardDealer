<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerekResource\Pages;
use App\Filament\Resources\MerekResource\RelationManagers;
use App\Models\Merek;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MerekResource extends Resource
{
    protected static ?string $model = Merek::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationGroup = 'Data Mobil';
    protected static ?string $navigationLabel = 'Merek';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Forms\Components\FileUpload::make('logo')
                    ->required()
                    ->image()
                    ->maxSize(1024) // 1MB
                    ->directory('logos')
                    ->preserveFilenames()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable()
                    ->label('ID')
                    ->disableClick(),
                
                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable()
                    ->label('Nama Merek')
                    ->disableClick(),
                
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->disk('public')
                    ->width(205)
                    ->height(150)
                    ->disableClick(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMereks::route('/'),
            'create' => Pages\CreateMerek::route('/create'),
            'edit' => Pages\EditMerek::route('/{record}/edit'),
        ];
    }
}
