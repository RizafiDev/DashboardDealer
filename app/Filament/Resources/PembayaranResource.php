<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranResource\Pages;
use App\Models\Pembayaran;
use App\Models\Pembelian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;

class PembayaranResource extends Resource
{
    protected static ?string $model = Pembayaran::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationLabel = 'Pembayaran';
    
    protected static ?string $modelLabel = 'Pembayaran';
    
    protected static ?string $pluralModelLabel = 'Pembayaran';

    protected static ?string $navigationGroup = 'Laporan'; // Move to reports
    protected static ?int $navigationSort = 2;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        // Form is no longer needed for creation, but can be used for viewing
        return $form
            ->schema([
                // Make fields disabled for read-only view
                Section::make('Detail Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('pembelian.no_faktur')->label('Nomor Faktur')->disabled(),
                        Forms\Components\TextInput::make('no_kwitansi')->disabled(),
                        Forms\Components\TextInput::make('jumlah')->money('IDR')->disabled(),
                        Forms\Components\TextInput::make('jenis')->disabled(),
                        Forms\Components\TextInput::make('metode')->disabled(),
                        Forms\Components\DatePicker::make('tanggal_bayar')->disabled(),
                        Forms\Components\FileUpload::make('bukti_bayar')->disabled(),
                        Forms\Components\Textarea::make('keterangan')->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Table remains the same, it's a good log
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_kwitansi')
                    ->label('No. Kwitansi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pembelian.no_faktur')
                    ->label('No. Faktur')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('pembelian.nama_pembeli')
                    ->label('Pembeli')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dp' => 'Down Payment',
                        'pelunasan' => 'Pelunasan',
                        'cicilan' => 'Cicilan',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'dp',
                        'success' => 'pelunasan', 
                        'warning' => 'cicilan',
                    ]),

                Tables\Columns\TextColumn::make('metode')
                    ->label('Metode')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'kartu_kredit' => 'Kartu Kredit',
                        'kartu_debit' => 'Kartu Debit',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_bayar')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('bukti_bayar')
                    ->label('Bukti')
                    ->circular(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('jenis')
                    ->options([
                        'dp' => 'Down Payment',
                        'pelunasan' => 'Pelunasan',
                        'cicilan' => 'Cicilan',
                    ]),

                SelectFilter::make('metode')
                    ->options([
                        'cash' => 'Tunai', 
                        'transfer' => 'Transfer Bank',
                        'kartu_kredit' => 'Kartu Kredit',
                        'kartu_debit' => 'Kartu Debit',
                    ]),

                Filter::make('tanggal_bayar')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query) => $query->whereDate('tanggal_bayar', '>=', $data['dari_tanggal'])
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query) => $query->whereDate('tanggal_bayar', '<=', $data['sampai_tanggal'])
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Remove Edit and Delete to make it a true log
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayarans::route('/'),
            // Remove create and edit pages
        ];
    }
}
