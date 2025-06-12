<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanCutiResource\Pages;
use App\Filament\Resources\PengajuanCutiResource\RelationManagers;
use App\Models\PengajuanCuti;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanCutiResource\Pages;
use App\Models\PengajuanCuti;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PengajuanCutiResource extends Resource
{
    protected static ?string $model = PengajuanCuti::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Pengajuan Cuti';
    protected static ?string $pluralLabel = 'Pengajuan Cuti';
    protected static ?string $navigationGroup = 'Karyawan & Presensi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pengajuan')
                    ->schema([
                        Forms\Components\Select::make('karyawan_id')
                            ->label('Karyawan')
                            ->relationship('karyawan', 'nama')
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('jenis')
                            ->options([
                                'sakit' => 'Sakit',
                                'cuti_tahunan' => 'Cuti Tahunan',
                                'izin_pribadi' => 'Izin Pribadi',
                                'cuti_melahirkan' => 'Cuti Melahirkan',
                                'darurat' => 'Darurat',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_mulai')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $tanggalSelesai = $get('tanggal_selesai');
                                if ($state && $tanggalSelesai) {
                                    $mulai = \Carbon\Carbon::parse($state);
                                    $selesai = \Carbon\Carbon::parse($tanggalSelesai);
                                    $jumlahHari = $mulai->diffInDays($selesai) + 1;
                                    $set('jumlah_hari', $jumlahHari);
                                }
                            }),
                        Forms\Components\DatePicker::make('tanggal_selesai')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state, callable $get) {
                                $tanggalMulai = $get('tanggal_mulai');
                                if ($state && $tanggalMulai) {
                                    $mulai = \Carbon\Carbon::parse($tanggalMulai);
                                    $selesai = \Carbon\Carbon::parse($state);
                                    $jumlahHari = $mulai->diffInDays($selesai) + 1;
                                    $set('jumlah_hari', $jumlahHari);
                                }
                            }),
                        Forms\Components\TextInput::make('jumlah_hari')
                            ->label('Jumlah Hari')
                            ->numeric()
                            ->required()
                            ->readOnly(),
                        Forms\Components\Textarea::make('alasan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('lampiran')
                            ->label('Lampiran (Surat Dokter, dll)')
                            ->directory('cuti-lampiran')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status Persetujuan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'menunggu' => 'Menunggu Persetujuan',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->default('menunggu')
                            ->required(),
                        Forms\Components\Select::make('disetujui_oleh')
                            ->label('Disetujui Oleh')
                            ->relationship('disetujuiOleh', 'name')
                            ->searchable()
                            ->visible(fn (callable $get): bool => $get('status') !== 'menunggu'),
                        Forms\Components\DateTimePicker::make('tanggal_disetujui')
                            ->visible(fn (callable $get): bool => $get('status') !== 'menunggu'),
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->rows(3)
                            ->visible(fn (callable $get): bool => $get('status') === 'ditolak')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('karyawan.nip')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('jenis')
                    ->colors([
                        'danger' => 'sakit',
                        'success' => 'cuti_tahunan',
                        'warning' => 'izin_pribadi',
                        'info' => 'cuti_melahirkan',
                        'secondary' => 'darurat',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'sakit' => 'Sakit',
                            'cuti_tahunan' => 'Cuti Tahunan',
                            'izin_pribadi' => 'Izin Pribadi',
                            'cuti_melahirkan' => 'Cuti Melahirkan',
                            'darurat' => 'Darurat',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_hari')
                    ->label('Hari')
                    ->numeric(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'menunggu',
                        'success' => 'disetujui',
                        'danger' => 'ditolak',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'menunggu' => 'Menunggu',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                            default => $state,
                        };
                    }),
                Tables\Columns\TextColumn::make('disetujuiOleh.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'sakit' => 'Sakit',
                        'cuti_tahunan' => 'Cuti Tahunan',
                        'izin_pribadi' => 'Izin Pribadi',
                        'cuti_melahirkan' => 'Cuti Melahirkan',
                        'darurat' => 'Darurat',
                    ]),
                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_selesai', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === 'menunggu')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'disetujui',
                            'disetujui_oleh' => auth()->id(),
                            'tanggal_disetujui' => now(),
                        ]);
                        
                        // Update saldo cuti
                        $saldoCuti = $record->karyawan->saldoCuti;
                        if ($saldoCuti && $record->jenis === 'cuti_tahunan') {
                            $saldoCuti->increment('cuti_tahunan_terpakai', $record->jumlah_hari);
                        } elseif ($saldoCuti && $record->jenis === 'sakit') {
                            $saldoCuti->increment('cuti_sakit_terpakai', $record->jumlah_hari);
                        }
                    }),
                
                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record): bool => $record->status === 'menunggu')
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'alasan_penolakan' => $data['alasan_penolakan'],
                            'disetujui_oleh' => auth()->id(),
                            'tanggal_disetujui' => now(),
                        ]);
                    }),
                
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanCutis::route('/'),
            'create' => Pages\CreatePengajuanCuti::route('/create'),
            // 'view' => Pages\ViewPengajuanCuti::route('/{record}'),
            'edit' => Pages\EditPengajuanCuti::route('/{record}/edit'),
        ];
    }
}