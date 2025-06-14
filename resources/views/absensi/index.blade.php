@extends('layouts.absensi')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Welcome Card --}}
            <div class="gradient-border mb-8">
                <div class="gradient-border-content p-6">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {{-- Menggunakan $karyawan jika ada, jika tidak $akunKaryawan (instance AkunKaryawan) --}}
                                Selamat Datang,
                                {{ $karyawan ? $karyawan->nama : ($akunKaryawan ? $akunKaryawan->username : 'Pengguna') }}!
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300">Sistem Absensi Digital - Dashboard Utama</p>
                        </div>
                        <div class="flex items-center space-x-4 mt-4 md:mt-0">
                            @if($karyawan)
                                <button id="openCutiModalBtn"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-transform duration-300 hover:scale-105">
                                    Ajukan Cuti / Izin
                                </button>
                            @endif
                            <div>
                                <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400" id="current-time">00:00:00
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 text-center">
                                    {{ now()->translatedFormat('l, d F Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <strong class="font-bold">Terjadi Kesalahan!</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!$akunKaryawan) {{-- Jika tidak ada akunKaryawan sama sekali (seharusnya tidak terjadi jika middleware
                auth:karyawan bekerja) --}}
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">Sesi tidak valid. Silakan login kembali.</span>
                </div>
            @elseif(!$karyawan && $akunKaryawan) {{-- Alert jika karyawan tidak ditemukan tapi akunKaryawan ada --}}
                <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded" role="alert">
                    <strong class="font-bold">Perhatian!</strong>
                    <span class="block sm:inline">Data detail karyawan untuk akun {{ $akunKaryawan->username }} tidak ditemukan.
                        Fitur absensi tidak dapat digunakan. Silakan hubungi administrator.</span>
                </div>
            @endif


            {{-- Main Content Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Kolom Kiri (Absensi) --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Stats Cards --}}
                    @if($karyawan && $rekapBulanIni)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {{-- Stat cards disederhanakan untuk layout --}}
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-center space-x-3">
                                <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900/50"><svg class="w-6 h-6 text-blue-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 10l-3 3m0 0l-3-3m3 3v7m-6-4h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v4a2 2 0 002 2z">
                                        </path>
                                    </svg></div>
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-white">Hadir</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $rekapBulanIni['hari_hadir'] ?? 0 }} Hari</p>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-center space-x-3">
                                <div class="p-2 rounded-full bg-yellow-100 dark:bg-yellow-900/50"><svg
                                        class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg></div>
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-white">Terlambat</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                                        {{ $rekapBulanIni['hari_terlambat'] ?? 0 }} Hari</p>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-center space-x-3">
                                <div class="p-2 rounded-full bg-red-100 dark:bg-red-900/50"><svg class="w-6 h-6 text-red-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                        </path>
                                    </svg></div>
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-white">Absen</p>
                                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $rekapBulanIni['hari_tidak_hadir'] ?? 0 }} Hari</p>
                                        </div>
                                    </div>
                                </div>
                    @endif

                        {{-- Quick Actions (Absensi) --}}
                        @if($karyawan && $pengaturanKantor)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Absensi Cepat</h3>
                                    {{-- Konten Absensi Cepat (kamera, map, dll) diletakkan di sini --}}
                                    <div class="flex flex-col space-y-4" id="main-buttons">
                                        @if(!$presensiHariIni)
                                            <button type="button" onclick="mulaiAbsen('masuk')"
                                                class="w-full flex items-center justify-center px-4 py-3 bg-blue-100 hover:bg-blue-300 dark:bg-blue-900 dark:text-blue-600 text-blue-600 rounded-lg transition-colors duration-200 font-semibold">Absen
                                                Masuk</button>
                                        @elseif(!$presensiHariIni->jam_pulang)
                                            <button type="button" onclick="mulaiAbsen('pulang')"
                                                class="w-full flex items-center justify-center px-4 py-3 bg-green-100 hover:bg-green-300 dark:bg-green-900 dark:text-green-600 text-green-600 rounded-lg transition-colors duration-200 font-semibold">Absen
                                                Pulang</button>
                                        @else
                                            <div class="text-gray-500 text-center p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">Anda
                                                sudah absen masuk & pulang hari ini.</div>
                                        @endif
                                    </div>
                                    <div class="flex justify-center mt-4">
                                        <div id="camera-container" class="hidden"><video id="camera-stream" autoplay playsinline
                                                class="w-64 h-48 object-cover rounded-lg border-2"></video><canvas id="photo-canvas"
                                                class="hidden"></canvas></div>
                                        <img id="photo-preview" class="hidden w-64 h-48 object-cover rounded-lg border-2">
                                    </div>
                                    <div id="camera-controls" class="hidden flex flex-wrap justify-center gap-2 mt-4">
                                        <button type="button" id="capture-btn"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg">Ambil Foto</button>
                                        <button type="button" id="retake-btn"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-lg">Foto Ulang</button>
                                        <button type="button" id="stop-camera-btn"
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg">Tutup</button>
                                    </div>
                                    <div id="submit-controls" class="hidden flex flex-wrap justify-center gap-2 mt-4">
                                        <button type="button" id="submit-attendance"
                                            class="px-6 py-3 bg-green-600 text-white rounded-lg">Kirim Absensi</button>
                                        <button type="button" id="cancel-submit"
                                            class="px-4 py-2 bg-gray-600 text-white rounded-lg">Batal</button>
                                    </div>
                                    <div class="mt-6">
                                        <div id="map" class="w-full h-[250px] rounded-lg shadow-inner border-2"></div>
                                    </div>
                                </div>
                            </div>
                        @elseif($karyawan)
                            <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 rounded-md" role="alert">
                                <p>Pengaturan kantor belum aktif. Fitur absensi tidak dapat digunakan.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Kolom Kanan (Cuti & Riwayat) --}}
                    <div class="space-y-8">
                        {{-- Status Pengajuan Cuti/Izin --}}
                        @if($karyawan)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Pengajuan Terakhir
                                    </h3>
                                    <div class="space-y-4">
                                        @php
                                            $statusClasses = [
                                                'menunggu' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                                'disetujui' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                                'ditolak' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                            ];
                                        @endphp
                                        @forelse($pengajuanTerakhir as $pengajuan)
                                            <div
                                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                                <div>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-200">
                                                        {{ ucfirst(str_replace('_', ' ', $pengajuan->jenis)) }}</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $pengajuan->tanggal_mulai->format('d M') }} -
                                                        {{ $pengajuan->tanggal_selesai->format('d M Y') }}</p>
                                                </div>
                                                <span
                                                    class="px-3 py-1 text-xs font-bold rounded-full {{ $statusClasses[$pengajuan->status] ?? '' }}">
                                                    {{ ucfirst($pengajuan->status) }}
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-center text-gray-500 dark:text-gray-400 py-4">Belum ada riwayat pengajuan.
                                            </p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Riwayat Absensi Terakhir --}}
                        @if($karyawan)
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Absensi</h3>
                                        <a href="{{ route('absensi.histori') }}"
                                            class="text-sm font-medium text-blue-600 hover:underline">Lihat Semua</a>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full">
                                            {{-- Tabel riwayat absensi --}}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Form Pengajuan Cuti --}}
        <div id="cutiModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-lg m-4 max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Formulir Pengajuan Cuti / Izin</h3>
                    <button id="closeCutiModalBtn"
                        class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-300">&times;</button>
                </div>
                <form action="{{ route('absensi.ajukan-cuti') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="jenis" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis
                            Pengajuan</label>
                        <select id="jenis" name="jenis" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="cuti_tahunan">Cuti Tahunan</option>
                            <option value="sakit">Sakit</option>
                            <option value="izin_pribadi">Izin Pribadi</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_mulai"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="tanggal_selesai"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="alasan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alasan</label>
                        <textarea id="alasan" name="alasan" rows="3" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <div>
                        <label for="lampiran" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lampiran
                            (Opsional)</label>
                        <input type="file" id="lampiran" name="lampiran"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maks. 2MB. Format: JPG, PNG, PDF.</p>
                    </div>
                    <div class="pt-4 flex justify-end space-x-3">
                        <button type="button" id="cancelCutiModalBtn"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Kirim
                            Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
@endsection

@push('styles')
    <style>
        .location-marker {
            font-size: 1.2rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .calendar-day {
            min-height: 2.5rem;
            /* Adjust as needed */
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@push('scripts')
    {{-- Script Absensi yang sudah ada --}}
    <script>
        // Update current time (selalu dijalankan)
        function updateCurrentTime() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime(); // Panggil sekali saat load

        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM Content Loaded');

            // Kondisi utama untuk menjalankan script absensi
            @if($karyawan && $pengaturanKantor)
                console.log('Karyawan and PengaturanKantor are available. Initializing attendance script.');

                // Global variables untuk absensi
                let map, userMarker, officeMarker, radiusCircle, watchId, stream;
                let currentAttendanceType = null;
                let capturedPhoto = null;
                let currentLocation = null; // {lat, lng, accuracy, alamat}

                const kantorLat = parseFloat("{{ $pengaturanKantor->latitude }}");
                const kantorLng = parseFloat("{{ $pengaturanKantor->longitude }}");
                const radiusMeter = parseInt("{{ $pengaturanKantor->radius_meter }}", 10);

                // Elemen UI
                const mainButtonsDiv = document.getElementById('main-buttons');
                const cameraContainerDiv = document.getElementById('camera-container');
                const cameraStreamVideo = document.getElementById('camera-stream');
                const photoCanvas = document.getElementById('photo-canvas');
                const photoPreviewImg = document.getElementById('photo-preview');
                const cameraControlsDiv = document.getElementById('camera-controls');
                const captureBtn = document.getElementById('capture-btn');
                const retakeBtn = document.getElementById('retake-btn');
                const stopCameraBtn = document.getElementById('stop-camera-btn');
                const submitControlsDiv = document.getElementById('submit-controls');
                const submitAttendanceBtn = document.getElementById('submit-attendance');
                const cancelSubmitBtn = document.getElementById('cancel-submit');
                const mapDiv = document.getElementById('map');


                function initMap() {
                    if (!mapDiv) {
                        console.error("Map container 'map' not found.");
                        return;
                    }
                    map = L.map(mapDiv).setView([kantorLat, kantorLng], 16);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    officeMarker = L.marker([kantorLat, kantorLng], {
                        icon: L.divIcon({ className: 'location-marker', html: '<span style="color: #ef4444;">🏢</span> Kantor' })
                    }).addTo(map).bindPopup('Lokasi Kantor');

                    radiusCircle = L.circle([kantorLat, kantorLng], {
                        radius: radiusMeter, color: '#22c55e', fillColor: '#22c55e', fillOpacity: 0.1, weight: 1
                    }).addTo(map);

                    userMarker = L.marker([0, 0], { // Posisi awal, akan diupdate
                        icon: L.divIcon({ className: 'location-marker', html: '<span style="color: #3b82f6;">📍</span> Anda' })
                    });

                    watchUserPosition();
                }

                function watchUserPosition() {
                    if (!navigator.geolocation) {
                        showError('Geolocation tidak didukung oleh browser ini.');
                        return;
                    }
                    showLocationLoading(true, 'Mencari lokasi Anda...');

                    const options = { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 };

                    watchId = navigator.geolocation.watchPosition(
                        async position => {
                            showLocationLoading(false);
                            const pos = {
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                                accuracy: position.coords.accuracy
                            };

                            // Dapatkan alamat hanya jika lokasi berubah signifikan atau belum ada
                            if (!currentLocation || calculateDistance(pos.lat, pos.lng, currentLocation.lat, currentLocation.lng) > 10 || !currentLocation.alamat) {
                                pos.alamat = await getAlamatDariKoordinat(pos.lat, pos.lng);
                            } else {
                                pos.alamat = currentLocation.alamat; // Gunakan alamat yang sudah ada
                            }
                            currentLocation = pos;


                            if (!userMarker._map) userMarker.addTo(map);
                            userMarker.setLatLng([pos.lat, pos.lng]);

                            const distance = calculateDistance(pos.lat, pos.lng, kantorLat, kantorLng);
                            userMarker.setPopupContent(
                                `<div class="text-sm"><b>Lokasi Anda</b><br>
                                    ${currentLocation.alamat || `Lat: ${pos.lat.toFixed(5)}, Lng: ${pos.lng.toFixed(5)}`}<br>
                                    Jarak: ${Math.round(distance)}m (Akurasi: ±${Math.round(pos.accuracy)}m)</div>`
                            ).openPopup();

                            updateLocationStatus(distance);

                            if (!map.initialZoomDone) {
                                map.setView([pos.lat, pos.lng], 17);
                                map.initialZoomDone = true;
                            }
                        },
                        error => {
                            showLocationLoading(false);
                            handleLocationError(error);
                            updateLocationStatus(Infinity); // Anggap di luar radius jika error
                        }, options
                    );
                }

                function updateLocationStatus(distance) {
                    const absenButtons = document.querySelectorAll('button[onclick^="mulaiAbsen"]');
                    const inRadius = distance <= radiusMeter;

                    absenButtons.forEach(button => {
                        button.disabled = !inRadius;
                        button.classList.toggle('opacity-50', !inRadius);
                        button.classList.toggle('cursor-not-allowed', !inRadius);
                        button.title = inRadius ? `Anda berada dalam radius kantor (${Math.round(distance)}m)`
                            : `Anda ${Math.round(distance)}m dari kantor (maks. ${radiusMeter}m). Tidak bisa absen.`;
                    });
                }


                function handleLocationError(error) {
                    let msg = 'Gagal mendapatkan lokasi: ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED: msg += 'Izin lokasi ditolak.'; break;
                        case error.POSITION_UNAVAILABLE: msg += 'Informasi lokasi tidak tersedia.'; break;
                        case error.TIMEOUT: msg += 'Waktu mendapatkan lokasi habis.'; break;
                        default: msg += error.message;
                    }
                    showError(msg);
                }

                async function mulaiAbsen(tipe) {
                    currentAttendanceType = tipe;
                    if (!currentLocation) {
                        showError('Lokasi belum terdeteksi. Mohon tunggu dan pastikan GPS aktif.');
                        return;
                    }
                    const distance = calculateDistance(currentLocation.lat, currentLocation.lng, kantorLat, kantorLng);
                    if (distance > radiusMeter) {
                        showError(`Anda berada ${Math.round(distance)}m dari kantor (maks. ${radiusMeter}m). Tidak bisa absen dari lokasi ini.`);
                        return;
                    }
                    await startCamera();
                }

                async function startCamera() {
                    try {
                        const constraints = { video: { facingMode: "user", width: { ideal: 320 }, height: { ideal: 320 } } }; // Coba kamera depan dulu
                        stream = await navigator.mediaDevices.getUserMedia(constraints);
                        cameraStreamVideo.srcObject = stream;
                        mainButtonsDiv.classList.add('hidden');
                        cameraContainerDiv.classList.remove('hidden');
                        cameraControlsDiv.classList.remove('hidden');
                        submitControlsDiv.classList.add('hidden');
                        photoPreviewImg.classList.add('hidden');
                    } catch (err) {
                        console.error("Error starting camera:", err);
                        showError('Tidak dapat mengakses kamera. Pastikan izin diberikan. Error: ' + err.message);
                        // Fallback ke kamera belakang jika kamera depan gagal atau tidak ada
                        try {
                            const fallbackConstraints = { video: { facingMode: { ideal: "environment" }, width: { ideal: 320 }, height: { ideal: 320 } } };
                            stream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
                            cameraStreamVideo.srcObject = stream;
                            mainButtonsDiv.classList.add('hidden');
                            cameraContainerDiv.classList.remove('hidden');
                            cameraControlsDiv.classList.remove('hidden');
                            submitControlsDiv.classList.add('hidden');
                            photoPreviewImg.classList.add('hidden');
                        } catch (fallbackErr) {
                            console.error("Error starting fallback camera:", fallbackErr);
                            showError('Gagal mengakses kamera depan maupun belakang. Error: ' + fallbackErr.message);
                        }
                    }
                }

                function capturePhoto() {
                    if (!cameraStreamVideo.srcObject) {
                        showError('Kamera belum aktif.');
                        return;
                    }
                    photoCanvas.width = cameraStreamVideo.videoWidth;
                    photoCanvas.height = cameraStreamVideo.videoHeight;
                    const ctx = photoCanvas.getContext('2d');
                    ctx.drawImage(cameraStreamVideo, 0, 0, photoCanvas.width, photoCanvas.height);

                    photoCanvas.toBlob(blob => {
                        if (!blob) {
                            showError('Gagal mengambil foto. Coba lagi.');
                            return;
                        }
                        capturedPhoto = blob;
                        photoPreviewImg.src = URL.createObjectURL(blob);
                        photoPreviewImg.classList.remove('hidden');
                        cameraContainerDiv.classList.add('hidden'); // Sembunyikan video stream
                        // cameraControlsDiv.classList.add('hidden'); // Sembunyikan tombol capture
                        retakeBtn.classList.remove('hidden'); // Tampilkan tombol retake
                        captureBtn.classList.add('hidden'); // Sembunyikan tombol capture
                        submitControlsDiv.classList.remove('hidden'); // Tampilkan tombol submit

                        // Hentikan stream video setelah foto diambil untuk hemat baterai
                        if (stream) {
                            stream.getTracks().forEach(track => track.stop());
                            cameraStreamVideo.srcObject = null; // Hapus srcObject
                        }

                    }, 'image/jpeg', 0.9);
                }

                async function retakePhoto() {
                    capturedPhoto = null;
                    photoPreviewImg.classList.add('hidden');
                    photoPreviewImg.src = ''; // Hapus src
                    URL.revokeObjectURL(photoPreviewImg.src); // Revoke URL lama

                    // Mulai ulang kamera
                    await startCamera();

                    // Sesuaikan visibilitas tombol
                    cameraContainerDiv.classList.remove('hidden'); // Tampilkan video stream
                    // cameraControlsDiv.classList.remove('hidden'); // Tampilkan tombol capture
                    captureBtn.classList.remove('hidden'); // Tampilkan tombol capture
                    retakeBtn.classList.add('hidden'); // Sembunyikan tombol retake
                    submitControlsDiv.classList.add('hidden');
                }

                function stopCameraAndResetUI() {
                    if (stream) {
                        stream.getTracks().forEach(track => track.stop());
                        cameraStreamVideo.srcObject = null;
                    }
                    stream = null;
                    capturedPhoto = null;
                    currentAttendanceType = null;

                    cameraContainerDiv.classList.add('hidden');
                    cameraControlsDiv.classList.add('hidden');
                    submitControlsDiv.classList.add('hidden');
                    photoPreviewImg.classList.add('hidden');
                    photoPreviewImg.src = '';
                    mainButtonsDiv.classList.remove('hidden');

                    // Reset tombol capture dan retake ke state awal
                    captureBtn.classList.remove('hidden');
                    retakeBtn.classList.remove('hidden'); // Atau sesuaikan logika awal
                }

                async function submitAttendance() {
                    if (!currentAttendanceType || !currentLocation || !capturedPhoto) {
                        showError('Data tidak lengkap. Pastikan lokasi terdeteksi dan foto sudah diambil.');
                        return;
                    }
                    if (!validatePhoto()) return;

                    showLoading(true, 'Mengirim absensi...');
                    const formData = new FormData();
                    formData.append('foto', capturedPhoto, `absen_${Date.now()}.jpg`);
                    formData.append('latitude', currentLocation.lat.toString());
                    formData.append('longitude', currentLocation.lng.toString());
                    formData.append('alamat', currentLocation.alamat || `Lat: ${currentLocation.lat.toFixed(5)}, Lng: ${currentLocation.lng.toFixed(5)}`);
                    formData.append('tipe', currentAttendanceType);

                    try {
                        const response = await fetch("{{ route('absensi.absen') }}", {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: formData
                        });
                        const result = await response.json();
                        if (result.success) {
                            showSuccess(result.message);
                            stopCameraAndResetUI();
                            setTimeout(() => window.location.reload(), 1500);
                        } else {
                            showError(result.error || (result.errors ? Object.values(result.errors).flat().join(', ') : 'Gagal mengirim absensi.'));
                        }
                    } catch (err) {
                        console.error("Submit error:", err);
                        showError('Terjadi kesalahan jaringan atau server: ' + err.message);
                    } finally {
                        showLoading(false);
                    }
                }

                function validatePhoto() {
                    if (!capturedPhoto) return false;
                    if (capturedPhoto.size > 2 * 1024 * 1024) { // Max 2MB
                        showError('Ukuran foto terlalu besar (maks 2MB).'); return false;
                    }
                    if (!capturedPhoto.type.startsWith('image/')) {
                        showError('File harus berupa gambar.'); return false;
                    }
                    return true;
                }

                async function getAlamatDariKoordinat(lat, lng) {
                    try {
                        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1&accept-language=id`);
                        if (!response.ok) throw new Error('Nominatim request failed');
                        const data = await response.json();
                        return data.display_name || `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;
                    } catch (error) {
                        console.warn('Error getting address from Nominatim:', error);
                        return `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`; // Fallback
                    }
                }

                function calculateDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371000;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                        Math.sin(dLon / 2) * Math.sin(dLon / 2);
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                }

                // UI Helpers
                let loadingToast = null;
                function showLocationLoading(show, message = 'Memproses...') {
                    const existingToast = document.getElementById('location-loading-toast');
                    if (show) {
                        if (existingToast) {
                            existingToast.textContent = message;
                            return;
                        }
                        loadingToast = document.createElement('div');
                        loadingToast.id = 'location-loading-toast';
                        loadingToast.className = 'fixed top-5 left-1/2 -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded-md shadow-lg z-50';
                        loadingToast.textContent = message;
                        document.body.appendChild(loadingToast);
                    } else if (existingToast) {
                        existingToast.remove();
                        loadingToast = null;
                    }
                }

                function showLoading(show, message = 'Memproses...') {
                    if (submitAttendanceBtn) {
                        submitAttendanceBtn.disabled = show;
                        submitAttendanceBtn.innerHTML = show ? `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> ${message}` : '✅ Kirim Absensi';
                    }
                }

                function showError(message) {
                    alert(`❌ Error: ${message}`); // Simple alert, bisa diganti toast custom
                }
                function showSuccess(message) {
                    alert(`✅ Sukses: ${message}`); // Simple alert
                }

                // Event Listeners
                if (captureBtn) captureBtn.addEventListener('click', capturePhoto);
                if (retakeBtn) retakeBtn.addEventListener('click', retakePhoto);
                if (stopCameraBtn) stopCameraBtn.addEventListener('click', stopCameraAndResetUI);
                if (submitAttendanceBtn) submitAttendanceBtn.addEventListener('click', submitAttendance);
                if (cancelSubmitBtn) cancelSubmitBtn.addEventListener('click', stopCameraAndResetUI); // Atau retakePhoto jika lebih sesuai

                // Inisialisasi Peta
                initMap();

                // Expose fungsi yang dipanggil dari HTML (onclick)
                window.mulaiAbsen = mulaiAbsen;

            @else
                console.warn('Data Karyawan atau Pengaturan Kantor tidak tersedia. Fitur absensi tidak diinisialisasi.');
                // Anda bisa menambahkan pesan di UI jika diperlukan
                const quickActionsDiv = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-6.mb-8');
                if (quickActionsDiv) {
                    // quickActionsDiv.innerHTML = '<div class="col-span-full text-center text-gray-500 p-4 bg-yellow-50 rounded-lg">Fitur absensi tidak tersedia karena data karyawan atau pengaturan kantor tidak lengkap.</div>';
                }
            @endif
        }); // Ini adalah penutup yang benar untuk DOMContentLoaded
    </script>

    {{-- Script untuk Modal Cuti --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cutiModal = document.getElementById('cutiModal');
            const openCutiModalBtn = document.getElementById('openCutiModalBtn');
            const closeCutiModalBtn = document.getElementById('closeCutiModalBtn');
            const cancelCutiModalBtn = document.getElementById('cancelCutiModalBtn');

            if (openCutiModalBtn) {
                openCutiModalBtn.addEventListener('click', () => {
                    cutiModal.classList.remove('hidden');
                });
            }

            const closeModal = () => {
                cutiModal.classList.add('hidden');
            };

            if (closeCutiModalBtn) closeCutiModalBtn.addEventListener('click', closeModal);
            if (cancelCutiModalBtn) cancelCutiModalBtn.addEventListener('click', closeModal);

            // Klik di luar modal untuk menutup
            cutiModal.addEventListener('click', (event) => {
                if (event.target === cutiModal) {
                    closeModal();
                }
            });

            // Jika ada error validasi, tampilkan kembali modalnya
            @if ($errors->any())
                cutiModal.classList.remove('hidden');
            @endif
    });
    </script>
@endpush