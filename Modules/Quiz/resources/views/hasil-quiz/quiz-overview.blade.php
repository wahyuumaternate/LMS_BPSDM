@extends('layouts.main')

@section('title', 'Overview Quiz - ' . $quiz->judul_quiz)
@section('page-title', 'Overview Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('hasil-quiz.index', $quiz->id) }}">Hasil Quiz: {{ $quiz->judul_quiz }}</a></li>
                    <li class="breadcrumb-item active">Statistik Overview</li>
                </ol>
            </nav>

            <!-- Quiz Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Informasi Quiz</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Judul Quiz</th>
                                    <td><strong>{{ $quiz->judul_quiz }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Modul</th>
                                    <td>{{ $quiz->modul->nama_modul ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Passing Grade</th>
                                    <td><span class="badge bg-warning">{{ $quiz->passing_grade ?? 70 }}%</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Total Soal</th>
                                    <td>{{ $quiz->soalQuiz->count() ?? 0 }} soal</td>
                                </tr>
                                <tr>
                                    <th>Durasi</th>
                                    <td>{{ $quiz->durasi ?? 0 }} menit</td>
                                </tr>
                                <tr>
                                    <th>Max Attempts</th>
                                    <td>{{ $quiz->max_attempts ?? 'Unlimited' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white">Total Attempts</h6>
                            <h2>{{ $stats['total_attempts'] }}</h2>
                            <small>Semua percobaan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white">Unique Participants</h6>
                            <h2>{{ $stats['unique_participants'] }}</h2>
                            <small>Peserta unik</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white">Pass Rate</h6>
                            <h2>{{ number_format($stats['pass_rate'], 1) }}%</h2>
                            <small>Tingkat kelulusan</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white">Average Score</h6>
                            <h2>{{ number_format($stats['average_score'], 1) }}</h2>
                            <small>Rata-rata nilai</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="bi bi-trophy text-success" style="font-size: 2rem;"></i>
                            <h3 class="text-success mt-2">{{ number_format($stats['highest_score'], 2) }}</h3>
                            <p class="text-muted mb-0">Nilai Tertinggi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-down text-danger" style="font-size: 2rem;"></i>
                            <h3 class="text-danger mt-2">{{ number_format($stats['lowest_score'], 2) }}</h3>
                            <p class="text-muted mb-0">Nilai Terendah</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="bi bi-clock text-info" style="font-size: 2rem;"></i>
                            <h3 class="text-info mt-2">{{ number_format($stats['average_duration'], 1) }}</h3>
                            <p class="text-muted mb-0">Rata-rata Durasi (menit)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Score Distribution Chart -->
            @if($scoreDistribution->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Distribusi Nilai</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($scoreDistribution as $dist)
                                @php
                                    $percentage = $stats['unique_participants'] > 0 
                                        ? ($dist->count / $stats['unique_participants']) * 100 
                                        : 0;
                                    
                                    // Determine color based on score range
                                    $bgClass = 'bg-secondary';
                                    if ($dist->score_range == '81-100') $bgClass = 'bg-success';
                                    elseif ($dist->score_range == '61-80') $bgClass = 'bg-info';
                                    elseif ($dist->score_range == '41-60') $bgClass = 'bg-warning';
                                    elseif ($dist->score_range == '21-40') $bgClass = 'bg-orange';
                                    else $bgClass = 'bg-danger';
                                @endphp
                                <div class="col-md-2">
                                    <div class="text-center mb-3">
                                        <h6>{{ $dist->score_range }}</h6>
                                        <div class="progress" style="height: 100px; width: 50px; margin: 0 auto;">
                                            <div class="progress-bar {{ $bgClass }}" 
                                                 role="progressbar" 
                                                 style="height: {{ $percentage }}%;"
                                                 aria-valuenow="{{ $percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                        <p class="mt-2 mb-0">
                                            <strong>{{ $dist->count }}</strong> peserta<br>
                                            <small class="text-muted">({{ number_format($percentage, 1) }}%)</small>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Participants Results Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Hasil Peserta (Nilai Terbaik)</h5>
                    <span class="badge bg-secondary">{{ $results->total() }} Peserta</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Peserta</th>
                                    <th>NIP</th>
                                    <th>Total Attempts</th>
                                    <th>Nilai Terbaik</th>
                                    <th>Status</th>
                                    <th>Durasi</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $index => $result)
                                    @php
                                        $totalAttempts = \Modules\Quiz\Entities\QuizResult::where('peserta_id', $result->peserta_id)
                                            ->where('quiz_id', $result->quiz_id)
                                            ->count();
                                    @endphp
                                    <tr>
                                        <td>{{ $results->firstItem() + $index }}</td>
                                        <td>
                                            <a href="{{ route('hasil-quiz.peserta-overview', $result->peserta_id) }}">
                                                {{ $result->peserta->nama_lengkap ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $result->peserta->nip ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $totalAttempts }}x</span>
                                        </td>
                                        <td>
                                            <strong class="text-primary">{{ number_format($result->nilai, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($result->is_passed)
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
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Belum ada peserta yang mengerjakan quiz ini</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $results->links() }}
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-3 mb-4">
                <a href="{{ route('hasil-quiz.index', $quiz->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Hasil
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .progress {
        transform: rotate(180deg);
        writing-mode: vertical-lr;
    }
    .progress-bar {
        writing-mode: horizontal-tb;
    }
    .bg-orange {
        background-color: #fd7e14 !important;
    }
</style>
@endpush