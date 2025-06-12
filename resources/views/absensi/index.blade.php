@extends('layouts.absensi')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Welcome Card --}}
        <div class="gradient-border mb-8">
            <div class="gradient-border-content p-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Selamat Datang, {{ Auth::user()->name }}!</h2>
                        <p class="text-gray-600 dark:text-gray-300">Sistem Absensi Digital - Dashboard Utama</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400" id="current-time">00:00:00</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 text-center">{{ now()->format('l, d F Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert Error --}}
        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Alert jika karyawan tidak ditemukan --}}
        @if(!$karyawan)
            <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded" role="alert">
                <strong class="font-bold">Perhatian!</strong>
                <span class="block sm:inline">Data karyawan tidak ditemukan. Silakan hubungi administrator.</span>
            </div>
        @endif

        {{-- Stats Cards --}}
        @if($karyawan && $rekapBulanIni)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                <div class="p-6 flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/50 mr-4">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kehadiran</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $rekapBulanIni['hari_hadir'] ?? 0 }} Hari</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                <div class="p-6 flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/50 mr-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Keterlambatan</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $rekapBulanIni['hari_terlambat'] ?? 0 }} Hari</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                <div class="p-6 flex items-center">
                    <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/50 mr-4">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Jam Kerja</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $rekapBulanIni['total_jam_kerja'] ?? 0 }} Jam</p>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:scale-105">
                <div class="p-6 flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/50 mr-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Hari Ini</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $presensiHariIni ? ucfirst($presensiHariIni->status) : 'Belum Absen' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        @if($karyawan && $pengaturanKantor)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            {{-- Attendance Action --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Absensi Cepat</h3>
                    <div class="flex flex-col space-y-4" id="main-buttons">
                        @if(!$presensiHariIni)
                            <button type="button" onclick="mulaiAbsen('masuk')" class="w-full flex items-center justify-between px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200">
                                <span>Absen Masuk</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        @elseif(!$presensiHariIni->jam_pulang)
                            <button type="button" onclick="mulaiAbsen('pulang')" class="w-full flex items-center justify-between px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200">
                                <span>Absen Pulang</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3v1"/>
                                </svg>
                            </button>
                        @else
                            <div class="text-gray-500 text-center">
                                Anda sudah melakukan absen masuk dan pulang hari ini
                            </div>
                        @endif
                    </div>
                    {{-- Camera Preview --}}
                    <div class="flex justify-center mt-4">
                        <div id="camera-container" class="hidden">
                            <video id="camera-stream" autoplay playsinline class="w-64 h-64 object-cover rounded-lg border-2 border-gray-200"></video>
                            <canvas id="photo-canvas" class="hidden"></canvas>
                        </div>
                        <img id="photo-preview" class="hidden w-64 h-64 object-cover rounded-lg border-2 border-gray-200">
                    </div>
                    {{-- Camera Controls --}}
                    <div id="camera-controls" class="hidden flex justify-center gap-4 mt-4">
                        <button type="button" id="capture-btn" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                            📷 Ambil Foto
                        </button>
                        <button type="button" id="retake-btn" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg">
                            🔄 Foto Ulang
                        </button>
                        <button type="button" id="stop-camera-btn" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">
                            ❌ Tutup Kamera
                        </button>
                    </div>
                    {{-- Submit Controls --}}
                    <div id="submit-controls" class="hidden flex justify-center gap-4 mt-4">
                        <button type="button" id="submit-attendance" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg">
                            ✅ Kirim Absensi
                        </button>
                        <button type="button" id="cancel-submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg">
                            ❌ Batal
                        </button>
                    </div>
                    {{-- Map --}}
                    <div class="mt-6">
                        <div id="map" class="w-full h-[300px] rounded-lg shadow-inner border-2 border-gray-200 dark:border-gray-700"></div>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center">
                            <span class="inline-flex items-center">
                                <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                Lokasi Kantor
                            </span>
                            <span class="inline-flex items-center ml-4">
                                <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                                Lokasi Anda
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Terakhir</h3>
                    <div class="space-y-4">
                        @if($presensiHariIni)
                            <div class="flex items-start">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Absen Masuk</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $presensiHariIni->jam_masuk ? $presensiHariIni->jam_masuk->format('H:i - d M Y') : '-' }}</p>
                                </div>
                            </div>
                            @if($presensiHariIni->jam_pulang)
                            <div class="flex items-start">
                                <div class="p-2 bg-green-100 dark:bg-green-900/50 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3v1"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Absen Pulang</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $presensiHariIni->jam_pulang->format('H:i - d M Y') }}</p>
                                </div>
                            </div>
                            @endif
                            @if($presensiHariIni->terlambat)
                            <div class="flex items-start">
                                <div class="p-2 bg-yellow-100 dark:bg-yellow-900/50 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">Terlambat</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $presensiHariIni->menit_terlambat }} menit</p>
                                </div>
                            </div>
                            @endif
                        @else
                            <div class="text-gray-500">Belum ada aktivitas hari ini.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Attendance Calendar --}}
        @if($karyawan && $rekapBulanIni)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kalender Kehadiran</h3>
                    <div class="flex space-x-2">
                        <button class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600" disabled>
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ now()->format('F Y') }}</span>
                        <button class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600" disabled>
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-2 mb-4">
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">M</div>
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">S</div>
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">S</div>
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">R</div>
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">K</div>
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">J</div>
                    <div class="text-center text-sm font-medium text-gray-500 dark:text-gray-400">S</div>
                </div>
                <div class="grid grid-cols-7 gap-2">
                    @php
                        $daysInMonth = now()->daysInMonth;
                        $today = now()->day;
                        $presensiPerTanggal = $karyawan ? $karyawan->presensis()->whereMonth('tanggal', now()->month)->get()->keyBy(function($p) { return (int)date('j', strtotime($p->tanggal)); }) : collect();
                    @endphp
                    @for ($i = 1; $i <= $daysInMonth; $i++)
                        @php
                            $presensi = $presensiPerTanggal[$i] ?? null;
                        @endphp
                        @if ($presensi)
                            @if ($presensi->terlambat)
                                <div class="text-center p-2 rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200 font-medium calendar-day" title="Terlambat">
                                    {{ $i }}
                                </div>
                            @elseif ($presensi->status === 'hadir')
                                <div class="text-center p-2 rounded-full bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200 font-medium calendar-day" title="Hadir">
                                    {{ $i }}
                                </div>
                            @else
                                <div class="text-center p-2 rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200 font-medium calendar-day" title="Tidak Hadir">
                                    {{ $i }}
                                </div>
                            @endif
                        @else
                            <div class="text-center p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 calendar-day">
                                {{ $i }}
                            </div>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Attendance Records --}}
        @if($karyawan)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Absensi Terakhir</h3>
                    <a href="{{ route('absensi.histori') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jam Masuk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jam Pulang</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Jam</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($karyawan->presensis()->orderBy('tanggal', 'desc')->limit(5)->get() as $presensi)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($presensi->tanggal)->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $presensi->jam_masuk ? $presensi->jam_masuk->format('H:i') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $presensi->jam_pulang ? $presensi->jam_pulang->format('H:i') : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($presensi->terlambat)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">Terlambat</span>
                                        @elseif ($presensi->status === 'hadir')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">Hadir</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">Tidak Hadir</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $presensi->jam_kerja ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if($pengaturanKantor)
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" async defer></script>
@endif
<script>
// Update current time
function updateCurrentTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('id-ID');
    }
}
setInterval(updateCurrentTime, 1000);
updateCurrentTime();

@if($pengaturanKantor)
// Global variables
let map, marker, watchId, stream;
let currentAttendanceType = null;
let capturedPhoto = null;
let currentLocation = null;

const kantorLat = {{ $pengaturanKantor->latitude }};
const kantorLng = {{ $pengaturanKantor->longitude }};
const radiusMeter = {{ $pengaturanKantor->radius_meter }};

// Inisialisasi Map
function initMap() {
    // Inisialisasi map dengan style yang lebih baik
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: kantorLat, lng: kantorLng },
        zoom: 17,
        mapTypeId: 'roadmap',
        disableDefaultUI: true,
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });

    // Marker kantor dengan info window
    const officeMarker = new google.maps.Marker({
        position: { lat: kantorLat, lng: kantorLng },
        map: map,
        title: 'Lokasi Kantor',
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
        }
    });

    // Radius area kantor
    const radiusCircle = new google.maps.Circle({
        map: map,
        center: { lat: kantorLat, lng: kantorLng },
        radius: radiusMeter,
        fillColor: '#4CAF50',
        fillOpacity: 0.15,
        strokeColor: '#4CAF50',
        strokeOpacity: 0.5,
        strokeWeight: 2
    });

    // Marker posisi user dengan info window
    marker = new google.maps.Marker({
        map: map,
        title: 'Lokasi Anda',
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        }
    });

    // Info windows
    const officeInfo = new google.maps.InfoWindow({
        content: '<div class="p-2"><b>Lokasi Kantor</b></div>'
    });

    const userInfo = new google.maps.InfoWindow();

    // Event listeners untuk markers
    officeMarker.addListener('click', () => {
        officeInfo.open(map, officeMarker);
    });

    // Watch posisi user
    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            position => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Update marker position
                marker.setPosition(pos);
                currentLocation = pos;

                // Hitung & tampilkan jarak
                const distance = calculateDistance(pos.lat, pos.lng, kantorLat, kantorLng);
                
                // Update info window dengan jarak
                userInfo.setContent(`
                    <div class="p-2">
                        <b>Lokasi Anda</b><br>
                        Jarak ke kantor: ${Math.round(distance)}m
                    </div>
                `);
                userInfo.open(map, marker);

                // Update status tombol absen
                updateLocationStatus(distance);

                // Center map ke user position saat pertama kali
                if (!map.get('initialized')) {
                    map.setCenter(pos);
                    map.set('initialized', true);
                }
            },
            error => {
                console.error('Error getting location:', error);
                showError('Tidak dapat mengakses lokasi Anda. Pastikan GPS aktif dan berikan izin lokasi.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        showError('Geolocation tidak didukung oleh browser ini');
    }
}

// Expose initMap to global scope for Google Maps callback
window.initMap = initMap;

// Update status lokasi
function updateLocationStatus(distance) {
    const buttons = document.querySelectorAll('button[onclick^="mulaiAbsen"]');
    buttons.forEach(button => {
        if (distance > radiusMeter) {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            button.title = `Anda berada ${Math.round(distance)}m dari kantor (maksimal ${radiusMeter}m)`;
        } else {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');
            button.title = `Anda berada dalam radius kantor (${Math.round(distance)}m)`;
        }
    });
}

// Fungsi untuk mulai absen
async function mulaiAbsen(tipe) {
    try {
        currentAttendanceType = tipe;
        
        // Validasi lokasi terlebih dahulu
        if (!currentLocation) {
            throw new Error('Lokasi belum didapatkan. Tunggu sebentar dan coba lagi.');
        }

        const distance = calculateDistance(currentLocation.lat, currentLocation.lng, kantorLat, kantorLng);
        if (distance > radiusMeter) {
            throw new Error(`Anda berada diluar radius kantor. Jarak: ${Math.round(distance)} meter (maksimal: ${radiusMeter} meter)`);
        }

        // Buka kamera
        await startCamera();

    } catch (error) {
        showError(error.message);
    }
}

// Fungsi untuk memulai kamera
async function startCamera() {
    try {
        // Minta akses kamera dengan constraint khusus untuk kamera belakang
        const constraints = {
            video: {
                facingMode: { ideal: 'environment' }, // Kamera belakang
                width: { ideal: 640 },
                height: { ideal: 640 }
            }
        };

        stream = await navigator.mediaDevices.getUserMedia(constraints);
        
        const video = document.getElementById('camera-stream');
        video.srcObject = stream;
        
        // Tampilkan interface kamera
        document.getElementById('main-buttons').classList.add('hidden');
        document.getElementById('camera-container').classList.remove('hidden');
        document.getElementById('camera-controls').classList.remove('hidden');
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        showError('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
    }
}

// Fungsi untuk menangkap foto
function capturePhoto() {
    try {
        console.log('capturePhoto function called'); // Debug log
        
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('photo-canvas');
        const preview = document.getElementById('photo-preview');
        
        if (!video || !canvas || !preview) {
            throw new Error('Element video, canvas, atau preview tidak ditemukan');
        }
        
        // Set canvas size sama dengan video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Gambar video ke canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Konversi ke blob dengan kualitas JPEG yang baik
        canvas.toBlob(blob => {
            if (!blob) {
                showError('Gagal mengambil foto. Silakan coba lagi.');
                return;
            }
            
            capturedPhoto = blob;
            console.log('Photo captured successfully', blob.size); // Debug log
            
            // Tampilkan preview
            const url = URL.createObjectURL(blob);
            preview.src = url;
            preview.classList.remove('hidden');
            
            // Sembunyikan kamera, tampilkan kontrol submit
            document.getElementById('camera-container').classList.add('hidden');
            document.getElementById('camera-controls').classList.add('hidden');
            document.getElementById('submit-controls').classList.remove('hidden');
            
            // Bersihkan URL object yang lama
            preview.onload = () => URL.revokeObjectURL(url);
            
        }, 'image/jpeg', 0.85); // Kualitas 85%
        
    } catch (error) {
        console.error('Error in capturePhoto:', error);
        showError('Terjadi kesalahan saat mengambil foto: ' + error.message);
    }
}

// Fungsi untuk foto ulang
function retakePhoto() {
    try {
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('camera-container').classList.remove('hidden');
        document.getElementById('submit-controls').classList.add('hidden');
        document.getElementById('camera-controls').classList.remove('hidden');
        capturedPhoto = null;
    } catch (error) {
        console.error('Error in retakePhoto:', error);
    }
}

// Fungsi untuk menghentikan kamera
function stopCamera() {
    try {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        // Reset UI
        document.getElementById('camera-container').classList.add('hidden');
        document.getElementById('camera-controls').classList.add('hidden');
        document.getElementById('submit-controls').classList.add('hidden');
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('main-buttons').classList.remove('hidden');
        
        capturedPhoto = null;
        currentAttendanceType = null;
    } catch (error) {
        console.error('Error in stopCamera:', error);
    }
}

// Fungsi untuk submit absensi
async function submitAttendance() {
    try {
        // Validasi data
        if (!currentAttendanceType) {
            throw new Error('Tipe absensi tidak valid');
        }
        
        if (!currentLocation) {
            throw new Error('Lokasi belum didapatkan. Tunggu sebentar dan coba lagi.');
        }
        
        if (!capturedPhoto) {
            throw new Error('Foto belum diambil');
        }
        
        if (!validatePhoto()) {
            return; // Error sudah ditampilkan di validatePhoto()
        }

        showLoading(true);

        // Dapatkan alamat
        const alamat = await getAlamatDariKoordinat(currentLocation.lat, currentLocation.lng);

        // Buat FormData
        const formData = new FormData();
        formData.append('foto', capturedPhoto, `attendance_${Date.now()}.jpg`);
        formData.append('latitude', currentLocation.lat.toString());
        formData.append('longitude', currentLocation.lng.toString());
        formData.append('alamat', alamat);
        formData.append('tipe', currentAttendanceType);
        
        const response = await fetch('{{ route("absensi.absen") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        // Parse response
        let result;
        try {
            result = await response.json();
        } catch (parseError) {
            console.error('Failed to parse JSON response:', parseError);
            const responseText = await response.text();
            console.error('Response text:', responseText);
            throw new Error('Server mengembalikan response yang tidak valid. Silakan cek koneksi dan coba lagi.');
        }

        // Handle response
        if (result.success) {
            showSuccess(result.message);
            stopCamera();
            setTimeout(() => location.reload(), 2000);
        } else {
            let errorMessage = result.error || 'Terjadi kesalahan';
            
            if (result.errors) {
                const validationErrors = Object.values(result.errors).flat();
                errorMessage = validationErrors.join(', ');
            }
            
            throw new Error(errorMessage);
        }

    } catch (error) {
        console.error('Submit error:', error);
        if (error.name === 'AbortError') {
            showError('Request timeout. Silakan periksa koneksi internet dan coba lagi.');
        } else {
            showError(error.message || 'Terjadi kesalahan saat mengirim data.');
        }
    } finally {
        showLoading(false);
    }
}

// Validasi foto
function validatePhoto() {
    if (!capturedPhoto) {
        return false;
    }
    
    // Validasi ukuran file (max 2MB)
    if (capturedPhoto.size > 2048 * 1024) {
        showError('Ukuran foto terlalu besar. Maksimal 2MB.');
        return false;
    }
    
    // Validasi tipe file
    if (!capturedPhoto.type.startsWith('image/')) {
        showError('File harus berupa gambar.');
        return false;
    }
    
    return true;
}

// Helper functions
async function getAlamatDariKoordinat(lat, lng) {
    try {
        const response = await fetch(
            `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key={{ config('services.google.maps_api_key') }}&language=id`
        );
        
        if (!response.ok) {
            throw new Error('Gagal mengakses service geocoding');
        }
        
        const data = await response.json();
        
        if (data.status === 'OK' && data.results?.[0]) {
            return data.results[0].formatted_address;
        } else if (data.status === 'ZERO_RESULTS') {
            return `Koordinat: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        } else {
            throw new Error(data.error_message || 'Geocoding gagal');
        }
        
    } catch (error) {
        console.error('Error getting address:', error);
        // Fallback ke koordinat jika geocoding gagal
        return `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Radius bumi dalam meter
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
        
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;
    
    return distance;
}

function deg2rad(deg) {
    return deg * (Math.PI/180);
}

// UI Helper functions
function showError(message) {
    alert('❌ ' + message);
}

function showSuccess(message) {
    alert('✅ ' + message);
}

function showLoading(show) {
    const submitBtn = document.getElementById('submit-attendance');
    if (submitBtn) {
        if (show) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Mengirim...';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '✅ Kirim Absensi';
        }
    }
}

// Event listeners - Setup dengan multiple methods untuk memastikan compatibility
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded'); // Debug log
    
    // Method 1: addEventListener
    const captureBtn = document.getElementById('capture-btn');
    const retakeBtn = document.getElementById('retake-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const submitBtn = document.getElementById('submit-attendance');
    const cancelBtn = document.getElementById('cancel-submit');
    
    if (captureBtn) {
        captureBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Capture button clicked via addEventListener'); // Debug log
            capturePhoto();
        });
        
        // Method 2: onclick sebagai backup
        captureBtn.onclick = function(e) {
            e.preventDefault();
            console.log('Capture button clicked via onclick'); // Debug log
            capturePhoto();
        };
    }
    
    if (retakeBtn) {
        retakeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            retakePhoto();
        });
        retakeBtn.onclick = function(e) {
            e.preventDefault();
            retakePhoto();
        };
    }
    
    if (stopCameraBtn) {
        stopCameraBtn.addEventListener('click', function(e) {
            e.preventDefault();
            stopCamera();
        });
        stopCameraBtn.onclick = function(e) {
            e.preventDefault();
            stopCamera();
        };
    }
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitAttendance();
        });
        submitBtn.onclick = function(e) {
            e.preventDefault();
            submitAttendance();
        };
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault();
            stopCamera();
        });
        cancelBtn.onclick = function(e) {
            e.preventDefault();
            stopCamera();
        };
    }
    
    console.log('Event listeners attached'); // Debug log
});

// Expose functions to global scope sebagai backup
window.mulaiAbsen = mulaiAbsen;
window.capturePhoto = capturePhoto;
window.retakePhoto = retakePhoto;
window.stopCamera = stopCamera;
window.submitAttendance = submitAttendance;

@endif
</script>
@endpush