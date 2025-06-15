<?php

namespace App\Filament\Resources\PembelianResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Pembayaran;
use App\Models\Pembelian;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PembayaransRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayarans';
    protected static ?string $title = 'Riwayat Pembayaran';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('no_kwitansi')
                    ->default(fn() => Pembayaran::generateNoKwitansi()),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('jenis')
                        ->label('Jenis Pembayaran')
                        ->options([
                            'dp' => 'Down Payment',
                            'cicilan' => 'Cicilan',
                            'pelunasan' => 'Pelunasan',
                            'tunai_lunas' => 'Tunai Lunas',
                            'biaya_lain' => 'Biaya Lain-lain',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, $state, RelationManager $livewire) {
                            /** @var Pembelian $pembelian */
                            $pembelian = $livewire->getOwnerRecord();
                            if (!$pembelian) {
                                return;
                            }

                            if ($state === 'tunai_lunas') {
                                $set('jumlah', $pembelian->sisa_pembayaran_aktual);
                            } elseif ($state === 'dp') {
                                if ($pembelian->dp > 0) {
                                    $totalDpPaid = $pembelian->pembayarans()->where('jenis', 'dp')->sum('jumlah');
                                    $sisaDp = $pembelian->dp - $totalDpPaid;
                                    $set('jumlah', $sisaDp > 0 ? $sisaDp : 0);
                                } else {
                                    $set('jumlah', 0); // Default to 0 if no DP amount set on Pembelian
                                }
                            }
                        })
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('jumlah')
                        ->label('Jumlah Pembayaran')
                        ->prefix('Rp')
                        ->numeric()
                        ->required()
                        // Ubah disabled menjadi readOnly
                        ->readOnly(fn(Get $get) => $get('jenis') === 'tunai_lunas')
                        ->helperText(function (Get $get): ?string {
                            if ($get('jenis') === 'tunai_lunas') {
                                return 'Jumlah diisi otomatis sejumlah sisa pembayaran.';
                            }
                            return null;
                        })
                        ->columnSpan(2),
                ]),
                Forms\Components\TextInput::make('untuk_pembayaran')
                    ->label('Alokasi Pembayaran Untuk')
                    ->placeholder('Mis: DP, Angsuran ke-1, Pelunasan Pokok, Biaya Admin')
                    ->columnSpanFull(),
                Forms\Components\Select::make('metode_pembayaran_utama')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Tunai (Cash)',
                        'transfer' => 'Transfer Bank',
                        'edc_debit' => 'EDC - Kartu Debit',
                        'edc_kredit' => 'EDC - Kartu Kredit',
                        'ewallet' => 'E-Wallet',
                        'cheque' => 'Cek/Giro',
                        'setoran_leasing' => 'Setoran dari Leasing',
                    ])
                    ->live()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Section::make('Detail Transfer Bank')
                    ->visible(fn(Get $get) => $get('metode_pembayaran_utama') === 'transfer')
                    ->schema([
                        Forms\Components\TextInput::make('nama_bank_pengirim')->label('Bank Pengirim'),
                        Forms\Components\TextInput::make('nomor_rekening_pengirim')->label('No. Rekening Pengirim'),
                        Forms\Components\TextInput::make('nama_pemilik_rekening_pengirim')->label('Atas Nama Pengirim'),
                        Forms\Components\Select::make('nama_bank_tujuan')
                            ->label('Bank Tujuan (Dealer)')
                            ->options([ // Populate with your dealership's bank accounts
                                'BCA Dealer XYZ' => 'BCA Dealer XYZ',
                                'Mandiri Dealer XYZ' => 'Mandiri Dealer XYZ',
                            ]),
                        Forms\Components\TextInput::make('nomor_referensi_transaksi')->label('No. Referensi Transfer'),
                    ])->columns(2),

                Forms\Components\Section::make('Detail EDC')
                    ->visible(fn(Get $get) => in_array($get('metode_pembayaran_utama'), ['edc_debit', 'edc_kredit']))
                    ->schema([
                        Forms\Components\TextInput::make('nama_bank_pengirim')->label('Bank Kartu'),
                        Forms\Components\TextInput::make('nomor_kartu_edc')->label('No. Kartu (4 Digit Terakhir)'),
                        Forms\Components\TextInput::make('jenis_mesin_edc')->label('Mesin EDC (Mis: BCA, Mandiri)'),
                        Forms\Components\TextInput::make('nomor_referensi_transaksi')->label('Trace No / Approval Code'),
                    ])->columns(2),

                Forms\Components\Section::make('Detail E-Wallet')
                    ->visible(fn(Get $get) => $get('metode_pembayaran_utama') === 'ewallet')
                    ->schema([
                        Forms\Components\Select::make('nama_ewallet')
                            ->options(['GoPay' => 'GoPay', 'OVO' => 'OVO', 'Dana' => 'Dana', 'ShopeePay' => 'ShopeePay', 'Lainnya' => 'Lainnya']),
                        Forms\Components\TextInput::make('nomor_referensi_transaksi')->label('ID Transaksi E-Wallet'),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Cek/Giro')
                    ->visible(fn(Get $get) => $get('metode_pembayaran_utama') === 'cheque')
                    ->schema([
                        Forms\Components\TextInput::make('nama_bank_pengirim')->label('Bank Penerbit Cek/Giro'),
                        Forms\Components\TextInput::make('nomor_cek_giro')->label('Nomor Cek/Giro'),
                        Forms\Components\DatePicker::make('tanggal_jatuh_tempo_cek_giro')->label('Tgl Jatuh Tempo'),
                        Forms\Components\Select::make('status_cek_giro')
                            ->options(['belum_cair' => 'Belum Cair', 'cair' => 'Cair', 'ditolak' => 'Ditolak'])
                            ->default('belum_cair'),
                    ])->columns(2),

                Forms\Components\DatePicker::make('tanggal_bayar')
                    ->label('Tanggal Pembayaran Aktual')
                    ->default(now())
                    ->required(),
                Forms\Components\FileUpload::make('bukti_bayar')
                    ->label('Bukti Pembayaran')
                    ->image()
                    ->directory('pembayaran')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan Tambahan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('no_kwitansi')
            ->columns([
                Tables\Columns\TextColumn::make('no_kwitansi')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tanggal_bayar')->date()->sortable(),
                Tables\Columns\TextColumn::make('jumlah')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'dp' => 'DP',
                        'cicilan' => 'Cicilan',
                        'pelunasan' => 'Pelunasan',
                        'tunai_lunas' => 'Tunai Lunas',
                        'biaya_lain' => 'Biaya Lain',
                        default => $state ?? '-',
                    })
                    ->colors([
                        'primary' => 'dp',
                        'info' => 'cicilan',
                        'success' => fn($state) => $state === 'pelunasan' || $state === 'tunai_lunas',
                        'warning' => 'biaya_lain',
                    ]),
                Tables\Columns\TextColumn::make('metode_pembayaran_utama')
                    ->label('Metode')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'edc_debit' => 'EDC Debit',
                        'edc_kredit' => 'EDC Kredit',
                        'ewallet' => 'E-Wallet',
                        'cheque' => 'Cek/Giro',
                        'setoran_leasing' => 'Setoran Leasing',
                        default => $state ?? '-',
                    }),
                Tables\Columns\TextColumn::make('untuk_pembayaran')->label('Alokasi')->limit(30),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        // Memastikan 'jumlah' diisi dengan benar sebelum pembuatan, terutama untuk 'tunai_lunas'
                        if (isset($data['jenis'])) {
                            /** @var Pembelian $pembelian */
                            $pembelian = $livewire->getOwnerRecord();
                            if ($pembelian) {
                                if ($data['jenis'] === 'tunai_lunas') {
                                    $data['jumlah'] = $pembelian->sisa_pembayaran_aktual;
                                } elseif ($data['jenis'] === 'dp') {
                                    if ($pembelian->dp > 0) {
                                        $totalDpPaid = $pembelian->pembayarans()->where('jenis', 'dp')->sum('jumlah');
                                        $sisaDp = $pembelian->dp - $totalDpPaid;
                                        $data['jumlah'] = $sisaDp > 0 ? $sisaDp : 0;
                                    } else {
                                        $data['jumlah'] = $data['jumlah'] ?? 0; // Jika DP tidak diset, gunakan input user atau 0
                                    }
                                }
                            }
                        }
                        // Pastikan no_kwitansi selalu ada
                        if (empty($data['no_kwitansi'])) {
                            $data['no_kwitansi'] = Pembayaran::generateNoKwitansi();
                        }
                        return $data;
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
}
