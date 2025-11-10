@extends('layouts.main')

@section('title', 'Hasil Ujian Peserta')
@section('page-title', 'Hasil Ujian Peserta')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Hasil Ujian Peserta: {{ $peserta->user->name ?? 'Peserta #' . $peserta->id }}
                        </h5>
                        <div>
                            <a href="{{ route('hasil-ujian.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Hasil
                            </a>
                        </div>
                    </div>

                    <!-- Statistik Peserta -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Peserta</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 30%">Nama</th>
                                                <td>{{ $peserta->user->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $peserta->user->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Ujian Diambil</th>
                                                <td>{{ $totalUjian }}</td>
                                            </tr>
                                            <tr>
                                                <th>Ujian Lulus</th>
                                                <td>{{ $passedCount }}</td>
                                            </tr>
                                            <tr>
                                                <th>Rata-rata Nilai</th>
                                                <td>{{ number_format($avgScore, 2) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Statistik Ringkas</h6>
                                    <div class="mt-3">
                                        <canvas id="resultPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Ujian -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Daftar Hasil Ujian</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">No.</th>
                                            <th scope="col">Ujian</th>
                                            <th scope="col">Kursus</th>
                                            <th scope="col">Waktu Pengerjaan</th>
                                            <th scope="col">Durasi</th>
                                            <th scope="col">Nilai</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hasil as $key => $h)
                                            <tr>
                                                <td scope="row">{{ $hasil->firstItem() + $key }}</td>
                                                <td>{{ $h->ujian->judul_ujian ?? 'N/A' }}</td>
                                                <td>{{ $h->ujian->kursus->nama_kursus ?? 'N/A' }}</td>
                                                <td>{{ $h->waktu_mulai ? $h->waktu_mulai->format('d M Y H:i') : '-' }}</td>
                                                <td>{{ $h->getDurationTaken() }}</td>
                                                <td>{{ number_format($h->nilai, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $h->getStatusBadgeClass() }}">
                                                        {{ $h->getStatusText() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('hasil-ujian.show', $h->id) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data hasil ujian</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if (method_exists($hasil, 'links'))
                                {{ $hasil->withQueryString()->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pie Chart untuk hasil ujian
            const ctx = document.getElementById('resultPieChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Lulus', 'Tidak Lulus'],
                    datasets: [{
                        label: 'Hasil Ujian',
                        data: [{{ $passedCount }}, {{ $totalUjian - $passedCount }}],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                        ],
                        borderColor: [
                            'rgb(75, 192, 192)',
                            'rgb(255, 99, 132)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Persentase Kelulusan',
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
