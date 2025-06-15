<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use App\Models\StokMobil;
use App\Models\Mobil;
use App\Models\Varian;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\PembelianResource\RelationManagers;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Pembelian';

    protected static ?string $modelLabel = 'Pembelian';

    protected static ?string $pluralModelLabel = 'Pembelian';

    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Pilih Mobil & Sales')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Section::make('Informasi Sales & Mobil')
                                ->description('Pilih sales yang melayani dan mobil yang akan dijual')
                                ->schema([
                                    Forms\Components\Select::make('karyawan_id')
                                        ->relationship('karyawan', 'nama') // Menggunakan relationship untuk efisiensi
                                        ->label('Sales Yang Melayani')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('mobil_id')
                                        ->label('Pilih Mobil')
                                        ->relationship('stokMobil.mobil', 'nama') // Pre-fill dari relasi
                                        ->options(
                                            Mobil::with('merek')->get()->mapWithKeys(fn($mobil) => [
                                                $mobil->id => ($mobil->merek?->nama ? $mobil->merek->nama . ' ' : '') . $mobil->nama,
                                            ])
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('varian_id', null);
                                            $set('stok_mobil_id', null);
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('varian_id')
                                        ->label('Varian')
                                        ->options(function (Get $get, $state): Collection {
                                            $mobilId = $get('mobil_id');
                                            if (!$mobilId) {
                                                return collect();
                                            }

                                            $query = Varian::query()->where('mobil_id', $mobilId);

                                            // Saat edit, pastikan varian yang sudah terpilih tetap ada di list
                                            if ($state) {
                                                $query->orWhere('id', $state);
                                            }

                                            return $query->get()->pluck('nama', 'id');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn(Set $set) => $set('stok_mobil_id', null))
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('stok_mobil_id')
                                        ->label('Pilih Unit Mobil')
                                        ->options(function (Get $get, $state): Collection {
                                            $mobilId = $get('mobil_id');
                                            $varianId = $get('varian_id');
                                            if (!$mobilId || !$varianId) {
                                                return collect();
                                            }

                                            $query = StokMobil::query()
                                                ->where('mobil_id', $mobilId)
                                                ->where('varian_id', $varianId);

                                            // Saat edit, unit yang sudah terpilih (misal statusnya 'booking' atau 'sold')
                                            // harus tetap muncul di pilihan.
                                            $query->where(function (Builder $q) use ($state) {
                                                $q->where('status', 'ready')
                                                    ->orWhere('id', $state);
                                            });

                                            return $query->get()->mapWithKeys(fn($stok) => [
                                                $stok->id => "{$stok->warna} - {$stok->no_rangka} - Rp " . number_format($stok->harga_jual, 0, ',', '.')
                                            ]);
                                        })
                                        ->getOptionLabelFromRecordUsing(function (StokMobil $record) {
                                            // Ini untuk menampilkan label saat form edit dimuat
                                            return "{$record->warna} - {$record->no_rangka} - Rp " . number_format($record->harga_jual, 0, ',', '.');
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            if ($state) {
                                                $stok = StokMobil::find($state);
                                                if ($stok) {
                                                    $set('harga_jual', $stok->harga_jual);
                                                }
                                            }
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('harga_jual')
                                        ->label('Harga Jual (OTR)')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated()
                                        ->columnSpan(1),

                                    Forms\Components\DatePicker::make('tanggal_pembelian')
                                        ->label('Tanggal Pembelian')
                                        ->default(now())
                                        ->required()
                                        ->columnSpan(1),
                                ])
                                ->columns(2),
                        ]),
                    Wizard\Step::make('Data Pembeli')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('Identitas Pembeli')
                                ->description('Lengkapi data identitas pembeli')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_pembeli')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),
                                    Forms\Components\TextInput::make('nik_pembeli')
                                        ->label('NIK')
                                        ->required()
                                        ->maxLength(20)
                                        ->unique(ignoreRecord: true)
                                        ->columnSpan(1),
                                    Forms\Components\DatePicker::make('tanggal_lahir_pembeli')
                                        ->label('Tanggal Lahir')
                                        ->required()
                                        ->maxDate(now()->subYears(17))
                                        ->columnSpan(1),
                                    Forms\Components\Select::make('jenis_kelamin_pembeli')
                                        ->label('Jenis Kelamin')
                                        ->options([
                                            'L' => 'Laki-laki',
                                            'P' => 'Perempuan',
                                        ])
                                        ->required()
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('pekerjaan_pembeli')
                                        ->label('Pekerjaan')
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                ])
                                ->columns(2),
                            Section::make('Kontak & Alamat')
                                ->schema([
                                    Forms\Components\TextInput::make('telepon_pembeli')
                                        ->label('Nomor Telepon')
                                        ->tel()
                                        ->required()
                                        ->maxLength(15)
                                        ->columnSpan(1),
                                    Forms\Components\TextInput::make('email_pembeli')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                    Forms\Components\Textarea::make('alamat_pembeli')
                                        ->label('Alamat Lengkap')
                                        ->required()
                                        ->rows(3)
                                        ->columnSpan(2),
                                ])
                                ->columns(2),
                        ]),
                    Wizard\Step::make('Detail Pembayaran & Kredit')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Section::make('Metode Pembelian')
                                ->schema([
                                    Forms\Components\Select::make('metode_pembayaran')
                                        ->label('Metode Pembelian')
                                        ->options([
                                            'tunai_lunas' => 'Tunai Lunas',
                                            'tunai_bertahap' => 'Tunai Bertahap',
                                            'kredit_bank' => 'Kredit Bank',
                                            'leasing' => 'Leasing',
                                        ])
                                        ->required()
                                        ->live()
                                        ->columnSpanFull(),
                                ])->columns(1),
                            Section::make('Detail Down Payment (DP)')
                                ->schema([
                                    Forms\Components\TextInput::make('dp')
                                        ->label('DP Total Dibayar ke Dealer')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->required(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing', 'tunai_bertahap']))
                                        ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing', 'tunai_bertahap'])),
                                    Forms\Components\TextInput::make('dp_murni')
                                        ->label('DP Murni dari Customer')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing'])),
                                    Forms\Components\TextInput::make('subsidi_dp')
                                        ->label('Subsidi DP')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing'])),
                                ])
                                ->columns(3)
                                ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing', 'tunai_bertahap'])),
                            Section::make('Informasi Leasing/Bank')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_leasing_bank')
                                        ->label('Nama Leasing/Bank')
                                        ->required(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing']))
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('kontak_leasing_bank')
                                        ->label('Kontak Leasing/Bank (Sales/PIC)')
                                        ->maxLength(255),
                                    Forms\Components\TextInput::make('tenor_bulan')
                                        ->label('Tenor (Bulan)')
                                        ->numeric()
                                        ->required(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing']))
                                        ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing', 'tunai_bertahap'])),
                                    Forms\Components\TextInput::make('suku_bunga_tahunan_persen')
                                        ->label('Suku Bunga (% per tahun)')
                                        ->numeric()
                                        ->suffix('%'),
                                    Forms\Components\Select::make('jenis_bunga')
                                        ->options(['flat' => 'Flat', 'efektif' => 'Efektif', 'anuitas' => 'Anuitas']),
                                    Forms\Components\TextInput::make('cicilan_per_bulan')
                                        ->label('Angsuran per Bulan')
                                        ->prefix('Rp')
                                        ->numeric(),
                                    Forms\Components\TextInput::make('pokok_hutang_awal')
                                        ->label('Pokok Hutang Awal (Plafond Kredit)')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->helperText('Harga OTR - DP Total'),
                                    Forms\Components\Select::make('angsuran_pertama_dibayar_kapan')
                                        ->label('Pembayaran Angsuran Pertama')
                                        ->options(['addm' => 'ADDM (Di Muka)', 'addb' => 'ADDB (Di Belakang)']),
                                ])
                                ->columns(2)
                                ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing'])),
                            Section::make('Biaya Tambahan (Leasing/Bank)')
                                ->schema([
                                    Forms\Components\TextInput::make('biaya_admin_leasing')
                                        ->label('Biaya Administrasi')
                                        ->prefix('Rp')
                                        ->numeric(),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('biaya_provisi')
                                            ->label('Biaya Provisi')
                                            ->numeric()
                                            ->prefix(fn(Get $get) => $get('tipe_biaya_provisi') === 'persen' ? null : 'Rp')
                                            ->suffix(fn(Get $get) => $get('tipe_biaya_provisi') === 'persen' ? '%' : null),
                                        Forms\Components\Select::make('tipe_biaya_provisi')
                                            ->options(['nominal' => 'Nominal (Rp)', 'persen' => 'Persentase (%)'])
                                            ->default('nominal')
                                            ->label('Tipe Provisi'),
                                    ]),
                                ])
                                ->columns(2)
                                ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing'])),
                            Section::make('Detail Asuransi Kendaraan')
                                ->schema([
                                    Forms\Components\TextInput::make('nama_asuransi')
                                        ->label('Nama Perusahaan Asuransi'),
                                    Forms\Components\Select::make('jenis_asuransi')
                                        ->options(['all_risk' => 'All Risk', 'tlo' => 'TLO (Total Loss Only)']),
                                    Forms\Components\TextInput::make('periode_asuransi_tahun')
                                        ->label('Periode Asuransi (Tahun)')
                                        ->numeric(),
                                    Forms\Components\TextInput::make('premi_asuransi_total')
                                        ->label('Total Premi Asuransi')
                                        ->prefix('Rp')
                                        ->numeric(),
                                    Forms\Components\Select::make('pembayaran_premi_asuransi')
                                        ->label('Pembayaran Premi')
                                        ->options([
                                            'include_angsuran' => 'Termasuk Angsuran',
                                            'bayar_dimuka' => 'Bayar di Muka ke Dealer',
                                            'bayar_langsung_asuransi' => 'Bayar Langsung ke Asuransi',
                                        ]),
                                ])
                                ->columns(2)
                                ->visible(fn(Get $get) => in_array($get('metode_pembayaran'), ['kredit_bank', 'leasing'])),
                        ]),
                    Wizard\Step::make('Konfirmasi')
                        ->icon('heroicon-o-document-check')
                        ->schema([
                            Section::make('Catatan & Status Awal')
                                ->schema([
                                    Forms\Components\Textarea::make('catatan')
                                        ->label('Catatan Tambahan')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    Forms\Components\Hidden::make('status')->default('booking'),
                                    Forms\Components\Hidden::make('no_faktur')
                                        ->default(fn() => Pembelian::generateNoFaktur()),
                                ])->columns(1),
                        ]),
                ])
                    ->columnSpanFull()
                    ->persistStepInQueryString()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_faktur')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pembeli')
                    ->label('Pembeli')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stokMobil.mobil.nama')
                    ->label('Mobil')
                    ->sortable(),
                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_bayar')
                    ->label('Total Dibayar')
                    ->money('IDR')
                    ->state(fn(Pembelian $record): float => $record->pembayarans()->sum('jumlah')),
                Tables\Columns\TextColumn::make('sisa_pembayaran_aktual')
                    ->label('Sisa Bayar')
                    ->money('IDR')
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'booking',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'booking' => 'Booking',
                        'in_progress' => 'Pembayaran',
                        'completed' => 'Lunas',
                        'cancelled' => 'Batal',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'booking' => 'Booking',
                        'in_progress' => 'Dalam Pembayaran',
                        'completed' => 'Selesai (Lunas)',
                        'cancelled' => 'Dibatalkan',
                    ]),

                SelectFilter::make('karyawan_id')
                    ->label('Sales')
                    ->relationship('karyawan', 'nama'),

                Filter::make('tanggal_pembelian')
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
                                fn(Builder $query) => $query->whereDate('tanggal_pembelian', '>=', $data['dari_tanggal'])
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn(Builder $query) => $query->whereDate('tanggal_pembelian', '<=', $data['sampai_tanggal'])
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Label')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Ringkasan Pembelian')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('no_faktur'),
                                                Infolists\Components\TextEntry::make('tanggal_pembelian')->date(),
                                                Infolists\Components\TextEntry::make('status')->badge()->color(fn(string $state): string => match ($state) {
                                                    'booking' => 'gray',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'gray',
                                                })->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                                            ]),
                                        Infolists\Components\Grid::make(3)
                                            ->schema([
                                                Infolists\Components\TextEntry::make('harga_jual')->money('IDR'),
                                                Infolists\Components\TextEntry::make('total_bayar')->money('IDR'),
                                                Infolists\Components\TextEntry::make('sisa_pembayaran_aktual')->label('Sisa Pembayaran')->money('IDR')->color(fn($state) => $state > 0 ? 'warning' : 'success'),
                                            ]),
                                        Infolists\Components\TextEntry::make('karyawan.nama')->label('Sales'),
                                        Infolists\Components\TextEntry::make('metode_pembayaran')->label('Metode Pembelian')->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                                    ]),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Detail Mobil')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('stokMobil.mobil.nama')->label('Mobil'),
                                        Infolists\Components\TextEntry::make('stokMobil.varian.nama')->label('Varian'),
                                        Infolists\Components\TextEntry::make('stokMobil.warna')->label('Warna'),
                                        Infolists\Components\TextEntry::make('stokMobil.tahun')->label('Tahun'),
                                        Infolists\Components\TextEntry::make('stokMobil.no_rangka')->label('No. Rangka'),
                                        Infolists\Components\TextEntry::make('stokMobil.no_mesin')->label('No. Mesin'),
                                    ])->columns(2),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Data Pembeli')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Infolists\Components\Section::make()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama_pembeli'),
                                        Infolists\Components\TextEntry::make('nik_pembeli'),
                                        Infolists\Components\TextEntry::make('telepon_pembeli'),
                                        Infolists\Components\TextEntry::make('email_pembeli'),
                                        Infolists\Components\TextEntry::make('alamat_pembeli')->columnSpanFull(),
                                        Infolists\Components\TextEntry::make('tanggal_lahir_pembeli')->date(),
                                        Infolists\Components\TextEntry::make('jenis_kelamin_pembeli')->formatStateUsing(fn($state) => $state === 'L' ? 'Laki-laki' : 'Perempuan'),
                                        Infolists\Components\TextEntry::make('pekerjaan_pembeli'),
                                    ])->columns(2),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Detail Kredit/Leasing')
                            ->icon('heroicon-o-banknotes')
                            ->visible(fn(Pembelian $record) => in_array($record->metode_pembayaran, ['kredit_bank', 'leasing']))
                            ->schema([
                                Infolists\Components\Section::make('Informasi Leasing/Bank')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama_leasing_bank'),
                                        Infolists\Components\TextEntry::make('kontak_leasing_bank'),
                                        Infolists\Components\TextEntry::make('tenor_bulan')->suffix(' bulan'),
                                        Infolists\Components\TextEntry::make('suku_bunga_tahunan_persen')->suffix('%'),
                                        Infolists\Components\TextEntry::make('jenis_bunga')->formatStateUsing(fn($state) => ucfirst($state)),
                                        Infolists\Components\TextEntry::make('cicilan_per_bulan')->money('IDR'),
                                        Infolists\Components\TextEntry::make('pokok_hutang_awal')->money('IDR'),
                                        Infolists\Components\TextEntry::make('angsuran_pertama_dibayar_kapan')->formatStateUsing(fn($state) => strtoupper($state)),
                                    ])->columns(2),
                                Infolists\Components\Section::make('Detail DP')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('dp')->label('DP Total')->money('IDR'),
                                        Infolists\Components\TextEntry::make('dp_murni')->money('IDR'),
                                        Infolists\Components\TextEntry::make('subsidi_dp')->money('IDR'),
                                    ])->columns(3),
                                Infolists\Components\Section::make('Biaya Tambahan')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('biaya_admin_leasing')->money('IDR'),
                                        Infolists\Components\TextEntry::make('biaya_provisi')->money(fn(Pembelian $record) => $record->tipe_biaya_provisi !== 'persen' ? 'IDR' : null)->suffix(fn(Pembelian $record) => $record->tipe_biaya_provisi === 'persen' ? '%' : null),
                                    ])->columns(2),
                                Infolists\Components\Section::make('Asuransi')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('nama_asuransi'),
                                        Infolists\Components\TextEntry::make('jenis_asuransi')->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                                        Infolists\Components\TextEntry::make('periode_asuransi_tahun')->suffix(' tahun'),
                                        Infolists\Components\TextEntry::make('premi_asuransi_total')->money('IDR'),
                                        Infolists\Components\TextEntry::make('pembayaran_premi_asuransi')->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                                    ])->columns(2),
                            ]),
                        Infolists\Components\Tabs\Tab::make('Catatan')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Infolists\Components\TextEntry::make('catatan')->html(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PembayaransRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            // 'view' => Pages\ViewPembelian::route('/{record}'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}
