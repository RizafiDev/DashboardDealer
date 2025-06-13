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

    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Pembayaran')
                    ->description('Masukkan detail pembayaran')
                    ->schema([
                        Forms\Components\Select::make('pembelian_id')
                            ->label('Nomor Faktur Pembelian')
                            ->options(function () {
                                return Pembelian::whereIn('status', ['pending', 'dp_paid'])
                                    ->with(['stokMobil.mobil'])
                                    ->get()
                                    ->mapWithKeys(function ($pembelian) {
                                        $mobilNama = $pembelian->stokMobil?->mobil?->nama ?? '-';
                                        $label = "{$pembelian->no_faktur} - {$pembelian->nama_pembeli} ({$mobilNama})";
                                        return [$pembelian->id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        Forms\Components\Hidden::make('no_kwitansi')
                            ->default(fn () => Pembayaran::generateNoKwitansi()),

                        Forms\Components\TextInput::make('jumlah')
                            ->label('Jumlah Pembayaran')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('jenis')
                            ->label('Jenis Pembayaran')
                            ->options([
                                'dp' => 'Down Payment',
                                'pelunasan' => 'Pelunasan',
                                'cicilan' => 'Cicilan',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('metode')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Tunai',
                                'transfer' => 'Transfer Bank',
                                'kartu_kredit' => 'Kartu Kredit',
                                'kartu_debit' => 'Kartu Debit',
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('bank')
                            ->label('Bank')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => in_array($get('metode'), ['transfer', 'kartu_kredit', 'kartu_debit']))
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('no_referensi')
                            ->label('Nomor Referensi')
                            ->helperText('Nomor transfer/nomor kartu')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => in_array($get('metode'), ['transfer', 'kartu_kredit', 'kartu_debit']))
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('tanggal_bayar')
                            ->label('Tanggal Pembayaran')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('bukti_bayar')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->directory('pembayaran')
                            ->preserveFilenames()
                            ->maxSize(2048)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPembayarans::route('/'),
            'create' => Pages\CreatePembayaran::route('/create'),
            'edit' => Pages\EditPembayaran::route('/{record}/edit'),
        ];
    }
}
