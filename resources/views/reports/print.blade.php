<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Laporan Monitoring Punch List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-size: 11px;
            color: #333;
        }
        .table-sm th, .table-sm td {
            padding: 4px 6px;
            vertical-align: middle;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-3 text-right">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            Cetak / Simpan PDF
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ml-1">
            Tutup
        </button>
    </div>

    <div class="text-center mb-4">
        <h4 class="font-weight-bold mb-1">LAPORAN MONITORING PUNCH LIST MASA PEMELIHARAAN</h4>
        <h6 class="text-secondary mb-1">PROYEK FASILITAS KONSTRUKSI & KELISTRIKAN</h6>
        <div class="small text-muted">Tanggal Cetak: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</div>
    </div>

    <div class="row mb-3">
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr>
                    <td style="width: 150px;"><strong>Total Catatan:</strong></td>
                    <td>{{ $totalCount }} item</td>
                </tr>
                <tr>
                    <td><strong>Telah Selesai:</strong></td>
                    <td>{{ $completedCount }} item (Tingkat: {{ $rate }}%)</td>
                </tr>
            </table>
        </div>
        <div class="col-6 text-right">
            <table class="table table-borderless table-sm mb-0">
                <tr>
                    <td><strong>Tercatat Terlambat:</strong></td>
                    <td><span class="text-danger font-weight-bold">{{ $overdueCount }} item</span></td>
                </tr>
                <tr>
                    <td><strong>Status Masa:</strong></td>
                    <td>Dalam Masa Pemeliharaan Aktif</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="table table-bordered table-sm table-striped">
        <thead class="thead-dark text-center">
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 85px;">No. Punch</th>
                <th>Judul Catatan & Lokasi</th>
                <th>Kategori</th>
                <th>PIC</th>
                <th>Prioritas</th>
                <th>Temuan</th>
                <th>Tenggat</th>
                <th>Status</th>
                <th>Selesai</th>
                <th>Ket. Keterlambatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($punchLists as $idx => $item)
                @php $deadline = $item->deadline_status; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">{{ $item->punch_code }}</td>
                    <td>
                        <strong>{{ $item->title }}</strong><br>
                        <small>{{ $item->location_detail ?? '-' }}</small>
                    </td>
                    <td>{{ $item->category }}</td>
                    <td>{{ $item->pic?->name ?? 'Belum Ditugaskan' }}</td>
                    <td class="text-center">{{ $item->priority }}</td>
                    <td class="text-center">{{ $item->finding_date->format('d/m/Y') }}</td>
                    <td class="text-center font-weight-bold">{{ $item->due_date->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $item->status }}</td>
                    <td class="text-center">{{ $item->completed_at ? $item->completed_at->format('d/m/Y') : '-' }}</td>
                    <td>{{ $deadline['label'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-3">Tidak ada data punch list.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="row mt-5">
        <div class="col-4 text-center">
            <p>Dibuat Oleh,<br><strong>Tim Pemeliharaan Lapangan</strong></p>
            <br><br><br>
            <p>( .................................................... )</p>
        </div>
        <div class="col-4 text-center">
            <p>Diperiksa Oleh,<br><strong>Site Coordinator</strong></p>
            <br><br><br>
            <p>( .................................................... )</p>
        </div>
        <div class="col-4 text-center">
            <p>Disetujui Oleh,<br><strong>Project Manager (Kontraktor)</strong></p>
            <br><br><br>
            <p>( .................................................... )</p>
        </div>
    </div>
</body>
</html>
