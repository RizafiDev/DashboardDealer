<div>
    <strong>Mobil:</strong> {{ $mobil?->merek?->nama ?? '' }} {{ $mobil?->nama ?? '' }}<br>
    <strong>Varian:</strong> {{ $varian?->nama ?? '-' }}<br>
    <strong>Unit:</strong> {{ $stok?->warna ?? '-' }} / {{ $stok?->no_rangka ?? '-' }}<br>
    <strong>Harga Jual:</strong> Rp {{ number_format($harga ?? 0, 0, ',', '.') }}<br>
    <strong>DP:</strong> Rp {{ number_format($dp ?? 0, 0, ',', '.') }}<br>
    <strong>Pembeli:</strong> {{ $pembeli ?? '-' }}<br>
    <strong>Telepon:</strong> {{ $telepon ?? '-' }}<br>
    <strong>Metode:</strong> {{ $metode ?? '-' }}
</div>