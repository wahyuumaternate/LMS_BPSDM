@extends('layouts.main')

@section('title', 'Statistik Hasil Ujian')
@section('page-title', 'Statistik Hasil Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Statistik Hasil Ujian: {{ $ujian->judul_ujian }}</h5>
                        <div>
                            <a href="{{ route('ujians.show', $ujian->id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Detail Ujian
                            </a>
                        </div>
                    </div>

                    <!-- Statistik Ujian -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Ujian</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th style="width: 30%">Kursus</th>
                                                <td>{{ $ujian->kursus->nama_kursus }}</td>
                                            </tr>
                                            <tr>
                                                <th>Judul Ujian</th>
                                                <td>{{ $ujian->judul_ujian }}</td>
                                            </tr>
                                            <tr>
                                                <th>Waktu Pelaksanaan</th>
                                                <td>
                                                    @if ($ujian->waktu_mulai && $ujian->waktu_selesai)
                                                        {{ $ujian->waktu_mulai->format('d M Y H:i') }} -
                                                        {{ $ujian->waktu_selesai->format('d M Y H:i') }}
                                                    @else
                                                        Tidak dibatasi
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Passing Grade</th>
                                                <td>{{ $ujian->passing_grade }}%</td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Soal</th>
                                                <td>{{ $ujian->jumlah_soal }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Jumlah Peserta</h6>
                                            <h1 class="display-4">{{ $participantCount }}</h1>
                                            <p class="mb-0">Total Peserta Ujian</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center p-3">
                                            <h6 class="card-title">Lulus</h6>
                                            <h3>{{ $passedCount }}</h3>
                                            <p class="mb-0">
                                                ({{ $participantCount > 0 ? number_format(($passedCount / $participantCount) * 100, 1) : 0 }}%)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center p-3">
                                            <h6 class="card-title">Tidak Lulus</h6>
                                            <h3>{{ $participantCount - $passedCount }}</h3>
                                            <p class="mb-0">
                                                ({{ $participantCount > 0 ? number_format((($participantCount - $passedCount) / $participantCount) * 100, 1) : 0 }}%)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Rata-rata Nilai</h6>
                                            <h2>{{ number_format($avgScore, 2) }}</h2>
                                            <p class="mb-0">dari total 100</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribusi Nilai -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Distribusi Nilai</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="scoreDistributionChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Peserta -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Daftar Hasil Peserta</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">Peringkat</th>
                                            <th scope="col">Peserta</th>
                                            <th scope="col">Waktu Pengerjaan</th>
                                            <th scope="col">Durasi</th>
                                            <th scope="col">Nilai</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($hasil as $key => $h)
                                            <tr class="{{ $h->is_simulation ? 'table-info' : '' }}">
                                                <td scope="row">{{ $hasil->firstItem() + $key }}</td>
                                                <td>
                                                    @if ($h->is_simulation)
                                                        <span class="badge bg-info">SIMULASI</span><br>
                                                        {{ $h->user->name ?? 'Admin/Instruktur' }}
                                                    @else
                                                        {{ $h->peserta->user->name ?? 'Peserta #' . $h->peserta_id }}
                                                    @endif
                                                </td>
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
                                                <td colspan="7" class="text-center">Tidak ada data hasil ujian</td>
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
            // Distribusi Nilai Chart
            const ctx = document.getElementById('scoreDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['91-100', '81-90', '71-80', '61-70', '51-60', '0-50'],
                    datasets: [{
                        label: 'Jumlah Peserta',
                        data: [
                            {{ $scoreRanges['91-100'] }},
                            {{ $scoreRanges['81-90'] }},
                            {{ $scoreRanges['71-80'] }},
                            {{ $scoreRanges['61-70'] }},
                            {{ $scoreRanges['51-60'] }},
                            {{ $scoreRanges['0-50'] }}
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(201, 203, 207, 0.8)'
                        ],
                        borderColor: [
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 206, 86)',
                            'rgb(255, 159, 64)',
                            'rgb(255, 99, 132)',
                            'rgb(201, 203, 207)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribusi Nilai Peserta',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush
