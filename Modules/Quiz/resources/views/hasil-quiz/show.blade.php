@extends('layouts.main')

@section('title', 'Detail Hasil Quiz')
@section('page-title', 'Detail Hasil Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Hasil Quiz</h5>
                        <div>
                            <a href="{{ route('hasil-quiz.quiz-overview', $result->quiz_id) }}" class="btn btn-primary">
                                <i class="bi bi-bar-chart"></i> Statistik Quiz
                            </a>
                            <a href="{{ route('hasil-quiz.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Informasi Quiz dan Peserta -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Informasi Quiz</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th style="width: 40%">Judul Quiz</th>
                                            <td>{{ $result->quiz->judul_quiz }}</td>
                                        </tr>
                                        <tr>
                                            <th>Modul</th>
                                            <td>{{ $result->quiz->modul->nama_modul ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Durasi Quiz</th>
                                            <td>{{ $result->quiz->durasi_menit }} menit</td>
                                        </tr>
                                        <tr>
                                            <th>Passing Grade</th>
                                            <td>{{ $result->quiz->passing_grade }}%</td>
                                        </tr>
                                        <tr>
                                            <th>Jumlah Soal</th>
                                            <td>{{ $result->quiz->jumlah_soal }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Informasi Peserta</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <tr>
                                            <th style="width: 40%">Nama</th>
                                            <td>{{ $result->peserta->nama_lengkap ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>NIP</th>
                                            <td>{{ $result->peserta->nip ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $result->peserta->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Attempt</th>
                                            <td>{{ $result->attempt }}</td>
                                        </tr>
                                        <tr>
                                            <th>Waktu Mulai</th>
                                            <td>{{ $result->waktu_mulai ? date('d M Y, H:i:s', strtotime($result->waktu_mulai)) : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Waktu Selesai</th>
                                            <td>{{ $result->waktu_selesai ? date('d M Y, H:i:s', strtotime($result->waktu_selesai)) : 'N/A' }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil Quiz -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Hasil Quiz</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-4">
                                        <div class="position-relative d-inline-block">
                                            <div class="progress" style="height: 10px; width: 250px;">
                                                <div class="progress-bar {{ $result->is_passed ? 'bg-success' : 'bg-danger' }}"
                                                    role="progressbar" style="width: {{ $result->nilai }}%;"
                                                    aria-valuenow="{{ $result->nilai }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <div class="position-absolute"
                                                style="top: -25px; left: {{ $result->quiz->passing_grade }}%;">
                                                <small class="text-muted">PG</small>
                                                <div class="border-start border-dark" style="height: 35px;"></div>
                                            </div>
                                        </div>
                                        <h1 class="display-4 mt-2">{{ number_format($result->nilai, 2) }}</h1>
                                        <span
                                            class="badge {{ $result->is_passed ? 'bg-success' : 'bg-danger' }} fs-6 px-4 py-2">
                                            {{ $result->is_passed ? 'LULUS' : 'TIDAK LULUS' }}
                                        </span>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="border rounded p-3 mb-3">
                                                <h4 class="text-success">{{ $result->jumlah_benar }}</h4>
                                                <small>Jawaban Benar</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="border rounded p-3 mb-3">
                                                <h4 class="text-danger">{{ $result->jumlah_salah }}</h4>
                                                <small>Jawaban Salah</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="border rounded p-3 mb-3">
                                                <h4 class="text-warning">{{ $result->total_tidak_jawab ?? 0 }}</h4>
                                                <small>Tidak Dijawab</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <p><i class="bi bi-clock"></i> Durasi Pengerjaan:
                                            <strong>{{ $result->durasi_pengerjaan_menit }} menit</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-warning h-100">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0">Statistik Perbandingan</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        // Get statistics for this quiz
                                        $avgScore =
                                            \Modules\Quiz\Entities\QuizResult::where('quiz_id', $result->quiz_id)->avg(
                                                'nilai',
                                            ) ?? 0;
                                        $maxScore =
                                            \Modules\Quiz\Entities\QuizResult::where('quiz_id', $result->quiz_id)->max(
                                                'nilai',
                                            ) ?? 0;
                                        $minScore =
                                            \Modules\Quiz\Entities\QuizResult::where('quiz_id', $result->quiz_id)->min(
                                                'nilai',
                                            ) ?? 0;
                                        $totalAttempts = \Modules\Quiz\Entities\QuizResult::where(
                                            'quiz_id',
                                            $result->quiz_id,
                                        )->count();

                                        // Get percentile rank
                                        $belowCount = \Modules\Quiz\Entities\QuizResult::where(
                                            'quiz_id',
                                            $result->quiz_id,
                                        )
                                            ->where('nilai', '<', $result->nilai)
                                            ->count();
                                        $percentile = $totalAttempts > 0 ? ($belowCount / $totalAttempts) * 100 : 0;
                                    @endphp

                                    <p class="text-center mb-3">
                                        Peringkat kamu lebih tinggi dari
                                        <strong>{{ number_format($percentile, 1) }}%</strong> peserta lain
                                    </p>

                                    <table class="table">
                                        <tr>
                                            <th>Nilai Rata-rata</th>
                                            <td>
                                                {{ number_format($avgScore, 2) }}
                                                @if ($result->nilai > $avgScore)
                                                    <span class="text-success"><i class="bi bi-arrow-up"></i>
                                                        {{ number_format($result->nilai - $avgScore, 2) }}</span>
                                                @elseif($result->nilai < $avgScore)
                                                    <span class="text-danger"><i class="bi bi-arrow-down"></i>
                                                        {{ number_format($avgScore - $result->nilai, 2) }}</span>
                                                @else
                                                    <span class="text-secondary"><i class="bi bi-dash"></i> 0</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Nilai Tertinggi</th>
                                            <td>
                                                {{ number_format($maxScore, 2) }}
                                                @if ($result->nilai == $maxScore)
                                                    <span class="badge bg-success">Top Score!</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Nilai Terendah</th>
                                            <td>{{ number_format($minScore, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Peserta</th>
                                            <td>{{ $totalAttempts }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Jawaban -->
                    <div class="card border-secondary mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Detail Jawaban</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th scope="col">No.</th>
                                            <th scope="col">Pertanyaan</th>
                                            <th scope="col">Jawaban Peserta</th>
                                            <th scope="col">Jawaban Benar</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($result->quiz->soalQuiz as $index => $soal)
                                            @php
                                                $jawabanPeserta = $jawaban[$soal->id] ?? '-';

                                                // Find correct answer
                                                $jawabanBenar = null;
                                                $jawabanBenarText = null;
                                                foreach ($soal->options as $option) {
                                                    if ($option->is_jawaban_benar) {
                                                        $jawabanBenarText = $option->teks_opsi;
                                                        break;
                                                    }
                                                }

                                                // Determine if correct
                                                $isCorrect = false;
                                                if (isset($jawaban[$soal->id])) {
                                                    $isCorrect = $soal->isAnswerCorrect($jawabanPeserta);
                                                }
                                            @endphp
                                            <tr>
                                                <th scope="row">{{ $index + 1 }}</th>
                                                <td>{{ \Illuminate\Support\Str::limit(strip_tags($soal->pertanyaan), 100) }}
                                                </td>
                                                <td>
                                                    @if (isset($jawaban[$soal->id]))
                                                        @php
                                                            $jawabanText = '';
                                                            foreach ($soal->options as $option) {
                                                                if (
                                                                    $option->urutan == $jawabanPeserta ||
                                                                    $option->id == $jawabanPeserta
                                                                ) {
                                                                    $jawabanText = $option->teks_opsi;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $jawabanText }}
                                                    @else
                                                        <span class="text-muted">Tidak Dijawab</span>
                                                    @endif
                                                </td>
                                                <td>{{ $jawabanBenarText }}</td>
                                                <td>
                                                    @if (!isset($jawaban[$soal->id]))
                                                        <span class="badge bg-warning">Tidak Dijawab</span>
                                                    @elseif($isCorrect)
                                                        <span class="badge bg-success">Benar</span>
                                                    @else
                                                        <span class="badge bg-danger">Salah</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('hasil-quiz.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Hasil
                        </a>
                        <form action="{{ route('hasil-quiz.destroy', $result->id) }}" method="POST"
                            class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Hapus Hasil Quiz
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            const deleteForm = document.querySelector('.delete-form');
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm(
                        'Apakah Anda yakin ingin menghapus hasil quiz ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                    this.submit();
                }
            });
        });
    </script>
@endpush
