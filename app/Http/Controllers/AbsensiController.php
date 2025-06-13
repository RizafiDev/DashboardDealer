<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Karyawan;
use App\Models\PengaturanKantor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    public function index()
    {
        $karyawan = Karyawan::where('email', auth()->user()->email)->first();
        if (!$karyawan) {
            // Show error in the same page
            session()->flash('error', 'Data karyawan tidak ditemukan');
            return view('absensi.index', [
                'karyawan' => null,
                'presensiHariIni' => null,
                'pengaturanKantor' => PengaturanKantor::where('aktif', true)->first(),
                'rekapBulanIni' => null
            ]);
        }

        $presensiHariIni = $karyawan->presensiHariIni();
        $pengaturanKantor = PengaturanKantor::where('aktif', true)->first();
        
        $rekapBulanIni = $karyawan->rekapBulanan();

        return view('absensi.index', compact('karyawan', 'presensiHariIni', 'pengaturanKantor', 'rekapBulanIni'));
    }

    public function absen(Request $request)
    {
        try {
            \Log::info('Absensi request received', [
                'user' => auth()->user()->email,
                'data' => $request->except('foto')
            ]);

            // Validasi input
            $validated = $request->validate([
                'foto' => 'required|file|mimes:jpeg,jpg,png|max:2048',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'alamat' => 'required|string',
                'tipe' => 'required|in:masuk,pulang'
            ]);

            \Log::info('Validation passed', ['validated' => $validated]);

            // Cek karyawan
            $karyawan = Karyawan::where('email', auth()->user()->email)->first();
            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'error' => 'Data karyawan tidak ditemukan'
                ], 404);
            }

            // Cek pengaturan kantor
            $pengaturanKantor = PengaturanKantor::where('aktif', true)->first();
            if (!$pengaturanKantor) {
                return response()->json([
                    'success' => false,
                    'error' => 'Pengaturan kantor belum dikonfigurasi'
                ], 404);
            }

            // Validasi jarak dengan lokasi kantor
            $jarak = $this->hitungJarak(
                $request->latitude,
                $request->longitude,
                $pengaturanKantor->latitude,
                $pengaturanKantor->longitude
            );

            if ($jarak > $pengaturanKantor->radius_meter) {
                return response()->json([
                    'success' => false,
                    'error' => 'Anda berada diluar radius kantor. Jarak: ' . round($jarak) . ' meter (maksimal: ' . $pengaturanKantor->radius_meter . ' meter)'
                ], 400);
            }

            // Cek presensi hari ini
            $presensiHariIni = $karyawan->presensiHariIni();
            
            // Proses foto
            $foto = $request->file('foto');
            if (!$foto || !$foto->isValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'File foto tidak valid'
                ], 400);
            }

            // Generate nama file unik
            $namaFile = 'presensi_' . $karyawan->id . '_' . now()->format('Y-m-d_H-i-s') . '_' . Str::random(8) . '.' . $foto->getClientOriginalExtension();
            
            // Simpan foto
            $fotoPath = $foto->storeAs('presensi', $namaFile, 'public');

            // Data lokasi
            $dataLokasi = [
                'lat' => (float) $request->latitude,
                'lng' => (float) $request->longitude,
                'alamat' => $request->alamat,
                'jarak_meter' => round($jarak, 2)
            ];

            if ($request->tipe === 'masuk') {
                // Validasi: belum ada presensi hari ini
                if ($presensiHariIni) {
                    // Hapus foto yang sudah diupload karena tidak jadi digunakan
                    Storage::disk('public')->delete($fotoPath);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Anda sudah melakukan absen masuk hari ini'
                    ], 400);
                }

                // Buat presensi baru untuk absen masuk
                $presensi = new Presensi();
                $presensi->karyawan_id = $karyawan->id;
                $presensi->tanggal = now()->toDateString();
                $presensi->jam_masuk = now();
                $presensi->foto_masuk = $fotoPath;
                $presensi->lokasi_masuk = $dataLokasi;
                
                // Cek keterlambatan
                $presensi->cekTerlambat();
                
                $presensi->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Absen masuk berhasil dicatat pada ' . now()->format('H:i:s'),
                    'data' => [
                        'id' => $presensi->id,
                        'jam_masuk' => $presensi->jam_masuk->format('H:i:s'),
                        'status' => $presensi->status,
                        'terlambat' => $presensi->terlambat,
                        'menit_terlambat' => $presensi->menit_terlambat
                    ]
                ]);

            } elseif ($request->tipe === 'pulang') {
                // Validasi: harus sudah ada presensi masuk
                if (!$presensiHariIni) {
                    // Hapus foto yang sudah diupload
                    Storage::disk('public')->delete($fotoPath);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Anda belum melakukan absen masuk hari ini'
                    ], 400);
                }

                // Validasi: belum absen pulang
                if ($presensiHariIni->jam_pulang) {
                    // Hapus foto yang sudah diupload
                    Storage::disk('public')->delete($fotoPath);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Anda sudah melakukan absen pulang hari ini'
                    ], 400);
                }

                // Update presensi untuk absen pulang
                $presensiHariIni->jam_pulang = now();
$presensiHariIni->foto_pulang = $fotoPath;
$presensiHariIni->lokasi_pulang = $dataLokasi;
                
                // Jam kerja will be calculated automatically via model events
                $presensiHariIni->save();
                // Refresh model untuk mendapatkan data terbaru setelah save
$presensiHariIni->refresh();

                return response()->json([
    'success' => true,
    'message' => 'Absen pulang berhasil dicatat pada ' . now()->format('H:i:s'),
    'data' => [
        'id' => $presensiHariIni->id,
        'jam_masuk' => $presensiHariIni->jam_masuk->format('H:i:s'),
        'jam_pulang' => $presensiHariIni->jam_pulang->format('H:i:s'),
        'jam_kerja' => $presensiHariIni->jam_kerja,
        'jam_kerja_formatted' => $presensiHariIni->getJamKerjaFormatted(),
        'total_menit_kerja' => $presensiHariIni->getTotalMenitKerja(),
        'debug_info' => [
            'jam_masuk_timestamp' => $presensiHariIni->jam_masuk->timestamp,
            'jam_pulang_timestamp' => $presensiHariIni->jam_pulang->timestamp,
            'selisih_detik' => $presensiHariIni->jam_pulang->diffInSeconds($presensiHariIni->jam_masuk)
        ]
    ]
]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Absensi error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Hitung jarak antara dua koordinat menggunakan formula Haversine
     */
    private function hitungJarak($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);
            
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $R * $c;
        
        return $distance;
    }

    /**
     * Method untuk mendapatkan histori presensi (opsional)
     */
    public function histori()
    {
        $karyawan = Karyawan::where('email', auth()->user()->email)->first();
        
        if (!$karyawan) {
            return redirect()->route('absensi.index')->with('error', 'Data karyawan tidak ditemukan');
        }

        $presensi = $karyawan->presensi()
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return view('absensi.histori', compact('karyawan', 'presensi'));
    }
}