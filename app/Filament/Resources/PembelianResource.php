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
                    // Step 1: Pilih Mobil & Sales
                    Wizard\Step::make('Pilih Mobil & Sales')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            Section::make('Informasi Sales & Mobil')
                                ->description('Pilih sales yang melayani dan mobil yang akan dijual')
                                ->schema([
                                    Forms\Components\Select::make('karyawan_id')
                                        ->label('Sales Yang Melayani')
                                        ->options(Karyawan::where('status', 'aktif')->pluck('nama', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('mobil_id')
                                        ->label('Pilih Mobil')
                                        ->options(
                                            Mobil::with('merek')->get()
                                                ->mapWithKeys(fn($mobil) => [
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
                                        ->options(fn (Get $get): Collection => 
                                            Varian::where('mobil_id', $get('mobil_id'))
                                                ->where('is_active', true)
                                                ->get()
                                                ->filter(fn($varian) => !empty($varian->nama))
                                                ->pluck('nama', 'id')
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('stok_mobil_id', null);
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\Select::make('stok_mobil_id')
                                        ->label('Pilih Unit Mobil')
                                        ->options(fn (Get $get): Collection => 
                                            StokMobil::where('mobil_id', $get('mobil_id'))
                                                ->where('varian_id', $get('varian_id'))
                                                ->where('status', 'ready') // <--- Ganti dari 'available' ke 'ready'
                                                ->get()
                                                ->filter(fn($stok) => !empty($stok->warna) && !empty($stok->no_rangka))
                                                ->mapWithKeys(fn ($stok) => [
                                                    $stok->id => "{$stok->warna} - {$stok->no_rangka} - Rp " . number_format($stok->harga_jual, 0, ',', '.')
                                                ])
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                            if ($state) {
                                                $stok = StokMobil::find($state);
                                                if ($stok) {
                                                    $set('harga_jual', $stok->harga_jual);
                                                }
                                            }
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('harga_jual')
                                        ->label('Harga Jual')
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

                    // Step 2: Data Pembeli
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

                    // Step 3: Detail Pembayaran
                    Wizard\Step::make('Detail Pembayaran')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Section::make('Metode Pembayaran')
                                ->schema([
                                    Forms\Components\Select::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->options([
                                            'cash' => 'Tunai',
                                            'kredit' => 'Kredit Bank',
                                            'leasing' => 'Leasing',
                                        ])
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            if ($state === 'cash') {
                                                $set('dp', null);
                                                $set('bank_kredit', null);
                                                $set('tenor_bulan', null);
                                                $set('cicilan_per_bulan', null);
                                            }
                                        })
                                        ->columnSpan(2),

                                    Forms\Components\TextInput::make('dp')
                                        ->label('Down Payment (DP)')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->default(0)
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                            $hargaJual = $get('harga_jual') ?? 0;
                                            $dp = $state ?? 0;
                                            $sisa = $hargaJual - $dp;
                                            $set('sisa_pembayaran', $sisa);
                                            
                                            $tenor = $get('tenor_bulan');
                                            if ($tenor && $sisa > 0) {
                                                $set('cicilan_per_bulan', $sisa / $tenor);
                                            }
                                        })
                                        ->columnSpan(1)
                                        ->hidden(fn (Get $get) => $get('metode_pembayaran') === 'cash'),

                                    Forms\Components\TextInput::make('sisa_pembayaran')
                                        ->label('Sisa Pembayaran')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated()
                                        ->default(0) // Tambahkan default(0)
                                        ->columnSpan(1)
                                        ->hidden(fn (Get $get) => $get('metode_pembayaran') === 'cash'),

                                    Forms\Components\TextInput::make('bank_kredit')
                                        ->label('Bank/Leasing')
                                        ->maxLength(255)
                                        ->columnSpan(1)
                                        ->visible(fn (Get $get) => in_array($get('metode_pembayaran'), ['kredit', 'leasing'])),

                                    Forms\Components\Select::make('tenor_bulan')
                                        ->label('Tenor (Bulan)')
                                        ->options([
                                            12 => '12 Bulan',
                                            24 => '24 Bulan',
                                            36 => '36 Bulan',
                                            48 => '48 Bulan',
                                            60 => '60 Bulan',
                                        ])
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                            $sisa = $get('sisa_pembayaran') ?? 0;
                                            if ($state && $sisa > 0) {
                                                $set('cicilan_per_bulan', $sisa / $state);
                                            }
                                        })
                                        ->columnSpan(1)
                                        ->visible(fn (Get $get) => in_array($get('metode_pembayaran'), ['kredit', 'leasing'])),

                                    Forms\Components\TextInput::make('cicilan_per_bulan')
                                        ->label('Cicilan per Bulan')
                                        ->prefix('Rp')
                                        ->numeric()
                                        ->readOnly()
                                        ->dehydrated()
                                        ->columnSpan(1)
                                        ->visible(fn (Get $get) => in_array($get('metode_pembayaran'), ['kredit', 'leasing'])),
                                ])
                                ->columns(2),
                        ]),

                    // Step 4: Konfirmasi & Catatan
                    Wizard\Step::make('Konfirmasi')
                        ->icon('heroicon-o-document-check')
                        ->schema([
                            Section::make('Catatan & Dokumen')
                                ->schema([
                                    Forms\Components\Textarea::make('catatan')
                                        ->label('Catatan Tambahan')
                                        ->rows(3)
                                        ->columnSpan(2),

                                    Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'pending' => 'Menunggu Pembayaran',
                                            'dp_paid' => 'DP Dibayar',
                                            'completed' => 'Selesai',
                                        ])
                                        ->default('pending')
                                        ->required()
                                        ->columnSpan(1),

                                    Forms\Components\Hidden::make('no_faktur')
                                        ->default(fn () => Pembelian::generateNoFaktur()),
                                ])
                                ->columns(2),

                            Section::make('Ringkasan Pembelian')
                                ->schema([
                                    Forms\Components\Placeholder::make('ringkasan')
                                        ->label('')
                                        ->content(function (Get $get) {
                                            $mobilId = $get('mobil_id');
                                            $varianId = $get('varian_id');
                                            $stokId = $get('stok_mobil_id');
                                            $harga = $get('harga_jual');
                                            $dp = $get('dp') ?? 0;
                                            
                                            if (!$mobilId || !$varianId || !$stokId) {
                                                return 'Silakan lengkapi data mobil terlebih dahulu.';
                                            }
                                            
                                            $mobil = Mobil::find($mobilId);
                                            $varian = Varian::find($varianId);
                                            $stok = StokMobil::find($stokId);
                                            
                                            return view('filament.forms.components.pembelian-summary', [
                                                'mobil' => $mobil,
                                                'varian' => $varian,
                                                'stok' => $stok,
                                                'harga' => $harga,
                                                'dp' => $dp,
                                                'pembeli' => $get('nama_pembeli'),
                                                'telepon' => $get('telepon_pembeli'),
                                                'metode' => $get('metode_pembayaran'),
                                            ]);
                                        }),
                                ]),
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

                Tables\Columns\TextColumn::make('stokMobil.varian.nama')
                    ->label('Varian')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stokMobil.warna')
                    ->label('Warna')
                    ->badge(),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Sales')
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga_jual')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'dp_paid',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'dp_paid' => 'DP Dibayar',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
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
                        'pending' => 'Menunggu Pembayaran',
                        'dp_paid' => 'DP Dibayar',
                        'completed' => 'Selesai',
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
                                fn (Builder $query) => $query->whereDate('tanggal_pembelian', '>=', $data['dari_tanggal'])
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query) => $query->whereDate('tanggal_pembelian', '<=', $data['sampai_tanggal'])
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
                Infolists\Components\Section::make('Informasi Pembelian')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('no_faktur')
                                    ->label('Nomor Faktur'),
                                Infolists\Components\TextEntry::make('tanggal_pembelian')
                                    ->label('Tanggal Pembelian')
                                    ->date(),
                                Infolists\Components\TextEntry::make('karyawan.nama')
                                    ->label('Sales'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'dp_paid' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                    }),
                            ]),
                    ]),

                Infolists\Components\Section::make('Detail Mobil')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('stokMobil.mobil.nama')
                                    ->label('Mobil'),
                                Infolists\Components\TextEntry::make('stokMobil.varian.nama')
                                    ->label('Varian'),
                                Infolists\Components\TextEntry::make('stokMobil.warna')
                                    ->label('Warna'),
                                Infolists\Components\TextEntry::make('stokMobil.no_rangka')
                                    ->label('Nomor Rangka'),
                                Infolists\Components\TextEntry::make('stokMobil.no_mesin')
                                    ->label('Nomor Mesin'),
                                Infolists\Components\TextEntry::make('stokMobil.tahun')
                                    ->label('Tahun'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Data Pembeli')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_pembeli')
                                    ->label('Nama Lengkap'),
                                Infolists\Components\TextEntry::make('nik_pembeli')
                                    ->label('NIK'),
                                Infolists\Components\TextEntry::make('telepon_pembeli')
                                    ->label('Telepon'),
                                Infolists\Components\TextEntry::make('email_pembeli')
                                    ->label('Email'),
                                Infolists\Components\TextEntry::make('alamat_pembeli')
                                    ->label('Alamat')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Detail Pembayaran')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('harga_jual')
                                    ->label('Harga Jual')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'cash' => 'Tunai',
                                        'kredit' => 'Kredit Bank',
                                        'leasing' => 'Leasing',
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('dp')
                                    ->label('Down Payment')
                                    ->money('IDR'),
                                Infolists\Components\TextEntry::make('sisa_pembayaran')
                                    ->label('Sisa Pembayaran')
                                    ->money('IDR'),
                            ]),
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
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            // 'view' => Pages\ViewPembelian::route('/{record}'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
        ];
    }
}