@extends('layouts.main')

@section('title', 'Profil Hasil Quiz Peserta')
@section('page-title', 'Profil Hasil Quiz Peserta')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Profil Hasil Quiz: {{ $peserta->nama_lengkap ?? 'Peserta #' . $peserta->id }}
                        </h5>
                        <div>
                            @if(request()->has('from_quiz_id'))
                                <a href="{{ route('hasil-quiz.index', request('from_quiz_id')) }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Hasil Quiz
                                </a>
                            @else
                                <button onclick="window.history.back()" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </button>
                            @endif
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Peserta Information -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center mb-3">
                                <div class="avatar avatar-lg">
                                    <img src="{{ $peserta->foto_profil ? asset('storage/' . $peserta->foto_profil) : asset('images/avatar-default.png') }}"
                                        alt="{{ $peserta->nama_lengkap ?? 'Avatar' }}" class="rounded-circle img-thumbnail"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nama:</strong> {{ $peserta->nama_lengkap ?? 'N/A' }}</p>
                                            <p><strong>NIP:</strong> {{ $peserta->nip ?? 'N/A' }}</p>
                                            <p><strong>Email:</strong> {{ $peserta->email ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Jabatan:</strong> {{ $peserta->jabatan ?? 'N/A' }}</p>
                                            <p><strong>Total Quiz:</strong> {{ $stats['total_quizzes'] }}</p>
                                            <p><strong>Total Percobaan:</strong> {{ $stats['total_attempts'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Rata-rata Nilai</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4">{{ number_format($stats['average_score'], 1) }}</h2>
                                    <p class="text-muted">dari semua quiz</p>
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
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">Nilai Tertinggi</h6>
                                </div>
                                <div class="card-body text-center">
                                    <h2 class="display-4">{{ number_format($stats['highest_score'], 1) }}</h2>
                                    <p class="text-muted">nilai tertinggi</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-secondary h-100">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">Top Quizzes</h6>
                                </div>
                                <div class="card-body">
                                    <div class="list-group">
                                        @forelse($topQuizzes as $index => $topQuiz)
                                            <a href="{{ route('hasil-quiz.quiz-overview', $topQuiz->quiz_id) }}"
                                                class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1">{{ $topQuiz->quiz->judul_quiz ?? 'N/A' }}</h6>
                                                    <span
                                                        class="badge {{ $topQuiz->max_score >= 80 ? 'bg-success' : ($topQuiz->max_score >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ number_format($topQuiz->max_score, 1) }}
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    Modul: {{ $topQuiz->quiz->modul->nama_modul ?? 'N/A' }}
                                                </small>
                                            </a>
                                        @empty
                                            <p class="text-center text-muted">Belum ada data quiz</p>
                                        @endforelse
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
                                    @if ($stats['total_attempts'] > 0)
                                        <!-- Create a progress chart for average score distribution -->
                                        <div class="mb-4">
                                            <h6>Distribusi Nilai:</h6>
                                            @php
                                                $scoreCategories = [
                                                    ['range' => '0-20', 'count' => 0, 'class' => 'danger'],
                                                    ['range' => '21-40', 'count' => 0, 'class' => 'warning'],
                                                    ['range' => '41-60', 'count' => 0, 'class' => 'info'],
                                                    ['range' => '61-80', 'count' => 0, 'class' => 'primary'],
                                                    ['range' => '81-100', 'count' => 0, 'class' => 'success'],
                                                ];

                                                foreach ($results as $result) {
                                                    if ($result->nilai >= 0 && $result->nilai <= 20) {
                                                        $scoreCategories[0]['count']++;
                                                    } elseif ($result->nilai > 20 && $result->nilai <= 40) {
                                                        $scoreCategories[1]['count']++;
                                                    } elseif ($result->nilai > 40 && $result->nilai <= 60) {
                                                        $scoreCategories[2]['count']++;
                                                    } elseif ($result->nilai > 60 && $result->nilai <= 80) {
                                                        $scoreCategories[3]['count']++;
                                                    } else {
                                                        $scoreCategories[4]['count']++;
                                                    }
                                                }

                                                // Calculate percentages
                                                foreach ($scoreCategories as $key => $category) {
                                                    $scoreCategories[$key]['percentage'] =
                                                        ($category['count'] / $stats['total_attempts']) * 100;
                                                }
                                            @endphp

                                            <div class="progress" style="height: 25px;">
                                                @foreach ($scoreCategories as $category)
                                                    @if ($category['count'] > 0)
                                                        <div class="progress-bar bg-{{ $category['class'] }}"
                                                            style="width: {{ $category['percentage'] }}%"
                                                            role="progressbar"
                                                            aria-valuenow="{{ $category['percentage'] }}" aria-valuemin="0"
                                                            aria-valuemax="100" data-bs-toggle="tooltip"
                                                            title="{{ $category['range'] }}: {{ $category['count'] }} quiz">
                                                            {{ $category['count'] > 0 ? $category['count'] : '' }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small>0</small>
                                                <small>100</small>
                                            </div>
                                        </div>

                                        <table class="table">
                                            <tr>
                                                <th>Percobaan Terakhir</th>
                                                <td>{{ \Carbon\Carbon::parse($stats['latest_attempt'])->format('d M Y, H:i') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Quiz Diselesaikan</th>
                                                <td>{{ $stats['total_quizzes'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Total Percobaan</th>
                                                <td>{{ $stats['total_attempts'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Rata-rata Percobaan per Quiz</th>
                                                <td>{{ $stats['total_quizzes'] > 0 ? number_format($stats['total_attempts'] / $stats['total_quizzes'], 1) : 0 }}
                                                </td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-center text-muted">Belum ada data hasil quiz</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Riwayat Hasil Quiz</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">No.</th>
                                            <th scope="col">Quiz</th>
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
                                                    <a href="{{ route('hasil-quiz.index', $result->quiz_id) }}">
                                                        {{ $result->quiz->judul_quiz ?? 'N/A' }}
                                                    </a>
                                                </td>
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
                                                <td colspan="8" class="text-center">Tidak ada data hasil quiz</td>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enable tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
@endpush