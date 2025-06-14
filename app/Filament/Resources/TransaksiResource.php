<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Models\Transaksi;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Transaksi';
    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')->date()->sortable(),
                Tables\Columns\TextColumn::make('deskripsi')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('kategori')->badge(),
                Tables\Columns\TextColumn::make('jumlah')->money('IDR')->sortable()
                    ->color(fn (string $state, Transaksi $record) => $record->tipe === 'income' ? 'success' : 'danger')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('tipe')->badge()
                    ->colors([
                        'success' => 'income',
                        'danger' => 'expense',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        DatePicker::make('dari_tanggal'),
                        DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['dari_tanggal'], fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai_tanggal'], fn (Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date));
                    }),
            ])
            ->actions([
                // No actions as it's a read-only log
            ])
            ->bulkActions([])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
        ];
    }
}
