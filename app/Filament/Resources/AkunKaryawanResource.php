<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AkunKaryawanResource\Pages;
use App\Filament\Resources\AkunKaryawanResource\RelationManagers;
use App\Models\AkunKaryawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class AkunKaryawanResource extends Resource
{
    protected static ?string $model = AkunKaryawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Akun Karyawan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->visibleOn('create'),
                Forms\Components\TextInput::make('passwordConfirmation')
                    ->password()
                    ->same('password')
                    ->dehydrated(false)
                    ->visibleOn('create')
                    ->label('Konfirmasi Password'),

                // Form untuk edit - password optional
                Forms\Components\TextInput::make('new_password')
                    ->password()
                    ->minLength(8)
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->visibleOn('edit')
                    ->label('Password Baru (kosongkan jika tidak ingin mengubah)'),
                Forms\Components\TextInput::make('new_password_confirmation')
                    ->password()
                    ->same('new_password')
                    ->dehydrated(false)
                    ->visibleOn('edit')
                    ->label('Konfirmasi Password Baru'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListAkunKaryawans::route('/'),
            'create' => Pages\CreateAkunKaryawan::route('/create'),
            'edit' => Pages\EditAkunKaryawan::route('/{record}/edit'),
        ];
    }
}