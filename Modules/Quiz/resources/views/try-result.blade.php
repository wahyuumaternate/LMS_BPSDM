@extends('layouts.main')

@section('title', 'Hasil Uji Coba Quiz: ' . $quiz->judul_quiz)
@section('page-title', 'Hasil Uji Coba Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $quiz->judul_quiz }} <span class="badge bg-warning text-dark">Hasil Uji
                                Coba</span></h5>
                        <div>
                            <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Kembali ke Quiz
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p><strong>Catatan Instruktur:</strong> Ini adalah hasil dari uji coba quiz. Hasil ini tidak
                            disimpan ke database dan hanya ditampilkan untuk Anda sebagai instruktur.</p>
                    </div>

                    <!-- Result Summary -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-3 text-muted">Nilai Anda</h6>
                                    <h1 class="display-1">{{ number_format($result['nilai'], 1) }}</h1>
                                    <div class="mb-3">
                                        @if ($result['is_passed'])
                                            <span class="badge bg-success fs-6 px-4 py-2">LULUS</span>
                                        @else
                                            <span class="badge bg-danger fs-6 px-4 py-2">TIDAK LULUS</span>
                                        @endif
                                    </div>

                                    <div class="d-flex justify-content-center">
                                        <div class="position-relative">
                                            <div class="progress" style="height: 10px; width: 200px;">
                                                <div class="progress-bar {{ $result['is_passed'] ? 'bg-success' : 'bg-danger' }}"
                                                    role="progressbar" style="width: {{ $result['nilai'] }}%;"
                                                    aria-valuenow="{{ $result['nilai'] }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                            <div class="position-absolute"
                                                style="top: -10px; left: {{ $quiz->passing_grade }}%;">
                                                <div class="border-start border-dark" style="height: 30px;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted mt-1">
                                        <small>Passing Grade: {{ $quiz->passing_grade }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-3 text-muted text-center">Rincian Hasil</h6>
                                    <div class="row text-center">
                                        <div class="col">
                                            <div class="border rounded p-3 mb-3">
                                                <h2 class="text-success">{{ $result['jumlah_benar'] }}</h2>
                                                <small>Jawaban Benar</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="border rounded p-3 mb-3">
                                                <h2 class="text-danger">{{ $result['jumlah_salah'] }}</h2>
                                                <small>Jawaban Salah</small>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="border rounded p-3 mb-3">
                                                <h2 class="text-warning">{{ $result['total_tidak_jawab'] }}</h2>
                                                <small>Tidak Dijawab</small>
                                            </div>
                                        </div>
                                    </div>

                                    <table class="table table-sm mt-3">
                                        <tr>
                                            <th>Jumlah Soal</th>
                                            <td>{{ $quiz->jumlah_soal }}</td>
                                        </tr>
                                        <tr>
                                            <th>Waktu Selesai</th>
                                            <td>{{ \Carbon\Carbon::parse($result['waktu_selesai'])->format('d M Y, H:i:s') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Jawaban -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Detail Jawaban</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="answersAccordion">
                                @foreach ($quiz->soalQuiz as $index => $soal)
                                    @php
                                        $detailJawaban = $result['detail_jawaban'][$soal->id] ?? null;
                                        $statusClass = '';
                                        $statusBadge = '';

                                        if (!$detailJawaban || $detailJawaban['jawaban_peserta'] === null) {
                                            $statusClass = 'warning';
                                            $statusBadge = '<span class="badge bg-warning">Tidak Dijawab</span>';
                                        } elseif ($detailJawaban['is_correct']) {
                                            $statusClass = 'success';
                                            $statusBadge = '<span class="badge bg-success">Benar</span>';
                                        } else {
                                            $statusClass = 'danger';
                                            $statusBadge = '<span class="badge bg-danger">Salah</span>';
                                        }
                                    @endphp

                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $soal->id }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $soal->id }}"
                                                aria-expanded="false" aria-controls="collapse{{ $soal->id }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <span>Soal #{{ $index + 1 }}</span>
                                                    {!! $statusBadge !!}
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $soal->id }}" class="accordion-collapse collapse"
                                            aria-labelledby="heading{{ $soal->id }}"
                                            data-bs-parent="#answersAccordion">
                                            <div class="accordion-body">
                                                <div class="question mb-3">
                                                    <strong>Pertanyaan:</strong>
                                                    <div class="p-3 bg-light rounded">
                                                        {!! $soal->pertanyaan !!}
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card h-100 border-{{ $statusClass }}">
                                                            <div class="card-header bg-{{ $statusClass }} text-white">
                                                                <h6 class="mb-0">Jawaban Anda</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                @if (!$detailJawaban || $detailJawaban['jawaban_peserta'] === null)
                                                                    <p class="text-muted"><em>Tidak ada jawaban</em></p>
                                                                @else
                                                                    <p>{{ $detailJawaban['jawaban_peserta'] }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="card h-100 border-success">
                                                            <div class="card-header bg-success text-white">
                                                                <h6 class="mb-0">Jawaban Benar</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                @foreach ($soal->options as $option)
                                                                    @if ($option->is_jawaban_benar)
                                                                        <p>{{ $option->teks_opsi }}</p>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($soal->pembahasan)
                                                    <div class="pembahasan mt-3">
                                                        <div class="card border-info">
                                                            <div class="card-header bg-info text-white">
                                                                <h6 class="mb-0">Pembahasan</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                {!! $soal->pembahasan !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="all-options mt-3">
                                                    <h6>Semua Opsi Jawaban:</h6>
                                                    <ul class="list-group">
                                                        @foreach ($soal->options as $option)
                                                            <li
                                                                class="list-group-item 
                                                            @if ($option->is_jawaban_benar) list-group-item-success 
                                                            @elseif(isset($detailJawaban['jawaban_peserta']) &&
                                                                    $detailJawaban['jawaban_peserta'] == $option->teks_opsi &&
                                                                    !$detailJawaban['is_correct']
                                                            )
                                                                list-group-item-danger @endif
                                                        ">
                                                                {{ $option->teks_opsi }}
                                                                @if ($option->is_jawaban_benar)
                                                                    <span class="badge bg-success float-end">Benar</span>
                                                                @elseif(isset($detailJawaban['jawaban_peserta']) && $detailJawaban['jawaban_peserta'] == $option->teks_opsi)
                                                                    <span class="badge bg-danger float-end">Jawaban
                                                                        Anda</span>
                                                                @endif
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('quizzes.try', $quiz->id) }}" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat"></i> Coba Quiz Lagi
                        </a>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Detail Quiz
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Automatically open first question with wrong answer if any
            const firstWrongAnswer = document.querySelector('.accordion-item .badge.bg-danger');
            if (firstWrongAnswer) {
                const accordionButton = firstWrongAnswer.closest('.accordion-item').querySelector(
                    '.accordion-button');
                accordionButton.click();
            } else {
                // If no wrong answer, open the first unanswered question
                const firstUnanswered = document.querySelector('.accordion-item .badge.bg-warning');
                if (firstUnanswered) {
                    const accordionButton = firstUnanswered.closest('.accordion-item').querySelector(
                        '.accordion-button');
                    accordionButton.click();
                }
            }
        });
    </script>
@endpush
