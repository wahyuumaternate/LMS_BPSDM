@extends('layouts.main')

@section('title', 'Statistik Quiz - ' . $quiz->judul_quiz)
@section('page-title', 'Statistik Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Statistik Quiz: {{ $quiz->judul_quiz }}</h5>
                        <div>
                            <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-primary">
                                <i class="bi bi-file-text"></i> Detail Quiz
                            </a>
                            <a href="{{ route('hasil-quiz.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Hasil
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Quiz Information -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Modul:</strong> {{ $quiz->modul->nama_modul ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Durasi:</strong> {{ $quiz->durasi_menit }} menit</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Jumlah Soal:</strong> {{ $quiz->jumlah_soal }}</p>
                                <p class="mb-1"><strong>Passing Grade:</strong> {{ $quiz->passing_grade }}%</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Bobot Nilai:</strong> {{ $quiz->bobot_nilai }}</p>
                                <p class="mb-1"><strong>Max Attempt:</strong>
                                    {{ $quiz->max_attempt > 0 ? $quiz->max_attempt : 'Tidak Terbatas' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Total Attempts</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4">{{ $stats['total_attempts'] }}</h2>
                                    <p class="text-muted">kali percobaan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Pass Rate</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4">{{ number_format($stats['pass_rate'], 1) }}%</h2>
                                    <p class="text-muted">tingkat kelulusan</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Average Score</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4">{{ number_format($stats['average_score'], 1) }}</h2>
                                    <p class="text-muted">rata-rata nilai</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-secondary h-100">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Distribusi Nilai</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 250px;">
                                        <canvas id="scoreDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-secondary h-100">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Statistik Lainnya</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th>Nilai Tertinggi</th>
                                            <td>{{ number_format($stats['highest_score'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nilai Terendah</th>
                                            <td>{{ number_format($stats['lowest_score'], 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Rata-rata Durasi</th>
                                            <td>{{ number_format($stats['average_duration'], 0) }} menit</td>
                                        </tr>
                                        <tr>
                                            <th>Standar Deviasi</th>
                                            <td>
                                                @php
                                                    // Calculate standard deviation (simplified)
                                                    $scores = $results->pluck('nilai')->toArray();
                                                    $mean = array_sum($scores) / count($scores);
                                                    $variance = 0;

                                                    foreach ($scores as $score) {
                                                        $variance += pow($score - $mean, 2);
                                                    }

                                                    $stdDev = sqrt($variance / count($scores));
                                                @endphp
                                                {{ number_format($stdDev, 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Hasil</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">No.</th>
                                            <th scope="col">Peserta</th>
                                            <th scope="col">NIP</th>
                                            <th scope="col">Attempt</th>
                                            <th scope="col">Nilai</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Durasi</th>
                                            <th scope="col">Tanggal</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($results as $key => $result)
                                            <tr>
                                                <th scope="row">{{ $results->firstItem() + $key }}</th>
                                                <td>
                                                    <a
                                                        href="{{ route('hasil-quiz.peserta-overview', $result->peserta_id) }}">
                                                        {{ $result->peserta->nama_lengkap ?? 'Peserta #' . $result->peserta_id }}
                                                    </a>
                                                </td>
                                                <td>{{ $result->peserta->nip ?? 'N/A' }}</td>
                                                <td>{{ $result->attempt }}</td>
                                                <td>{{ number_format($result->nilai, 2) }}</td>
                                                <td>
                                                    @if ($result->is_passed)
                                                        <span class="badge bg-success">Lulus</span>
                                                    @else
                                                        <span class="badge bg-danger">Tidak Lulus</span>
                                                    @endif
                                                </td>
                                                <td>{{ $result->durasi_pengerjaan_menit }} menit</td>
                                                <td>{{ $result->created_at->format('d M Y, H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('hasil-quiz.show', $result->id) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Tidak ada data hasil quiz</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{ $results->withQueryString()->links() }}
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
            // Score distribution chart
            const scoreDistCtx = document.getElementById('scoreDistributionChart').getContext('2d');

            const scoreDistribution = @json($scoreDistribution);
            const labels = scoreDistribution.map(item => item.range);
            const data = scoreDistribution.map(item => item.count);

            new Chart(scoreDistCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Peserta',
                        data: data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(255, 159, 64, 0.6)',
                            'rgba(255, 205, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                        ],
                        borderColor: [
                            'rgb(255, 99, 132)',
                            'rgb(255, 159, 64)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)',
                            'rgb(54, 162, 235)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Nilai'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
