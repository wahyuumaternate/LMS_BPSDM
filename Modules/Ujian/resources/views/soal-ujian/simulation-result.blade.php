@extends('layouts.main')

@section('title', 'Hasil Simulasi Ujian')
@section('page-title', 'Hasil Simulasi Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Hasil Simulasi Ujian</h5>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Ujian Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="alert-heading">{{ $ujian->judul_ujian }}</h6>
                                <p class="mb-1"><strong>Kursus:</strong> {{ $ujian->kursus->nama_kursus }}</p>
                                <p class="mb-1"><strong>Waktu Pengerjaan:</strong>
                                    {{ $ujianResult->waktu_mulai->format('d M Y H:i') }} -
                                    {{ $ujianResult->waktu_selesai->format('d M Y H:i') }}
                                </p>
                                <p class="mb-0"><strong>Durasi Pengerjaan:</strong> {{ $ujianResult->getDurationTaken() }}
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h3 class="mb-0">
                                    Nilai: {{ number_format($ujianResult->nilai, 2) }}
                                    <small class="text-muted">/ 100</small>
                                </h3>
                                <span class="badge bg-{{ $ujianResult->is_passed ? 'success' : 'danger' }} fs-6">
                                    {{ $ujianResult->is_passed ? 'Lulus' : 'Tidak Lulus' }}
                                </span>
                                <p class="mt-2 mb-0 small">Passing Grade: {{ $ujian->passing_grade }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Score Breakdown -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Ringkasan Hasil</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <h6>Jumlah Soal</h6>
                                    <h3>{{ $soalUjians->count() }}</h3>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <h6>Jawaban Benar</h6>
                                    <h3>{{ $ujianResult->getCorrectAnswersCount() }}</h3>
                                </div>
                                <div class="col-md-3 mb-3 mb-md-0">
                                    <h6>Jawaban Salah</h6>
                                    <h3>{{ $ujianResult->getIncorrectAnswersCount() }}</h3>
                                </div>
                                <div class="col-md-3">
                                    <h6>Nilai Akhir</h6>
                                    <h3>{{ number_format($ujianResult->nilai, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Jawaban -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Detail Jawaban</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="accordionAnswers">
                                @foreach ($soalUjians as $index => $soal)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $soal->id }}">
                                            <button
                                                class="accordion-button {{ isset($jawaban[$soal->id]['benar']) && $jawaban[$soal->id]['benar'] ? '' : 'collapsed' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse{{ $soal->id }}"
                                                aria-expanded="{{ isset($jawaban[$soal->id]['benar']) && $jawaban[$soal->id]['benar'] ? 'true' : 'false' }}"
                                                aria-controls="collapse{{ $soal->id }}">
                                                <div class="d-flex justify-content-between w-100 align-items-center pe-3">
                                                    <span>
                                                        <span class="fw-bold">Soal {{ $index + 1 }}</span>
                                                        <span
                                                            class="badge bg-{{ $soal->getTypeBadgeClass() }} ms-2">{{ $soal->getFormattedType() }}</span>
                                                    </span>
                                                    @if (isset($jawaban[$soal->id]['benar']))
                                                        @if ($jawaban[$soal->id]['benar'])
                                                            <span class="badge bg-success">Benar
                                                                (+{{ $jawaban[$soal->id]['poin'] ?? $soal->poin }}
                                                                poin)</span>
                                                        @else
                                                            <span class="badge bg-danger">Salah (0 poin)</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">Tidak Dijawab</span>
                                                    @endif
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $soal->id }}"
                                            class="accordion-collapse collapse {{ isset($jawaban[$soal->id]['benar']) && $jawaban[$soal->id]['benar'] ? 'show' : '' }}"
                                            aria-labelledby="heading{{ $soal->id }}"
                                            data-bs-parent="#accordionAnswers">
                                            <div class="accordion-body">
                                                <div class="mb-3">
                                                    <h6 class="mb-2">Pertanyaan:</h6>
                                                    <div class="card bg-light">
                                                        <div class="card-body">
                                                            {!! $soal->pertanyaan !!}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Pilihan Jawaban -->
                                                @if ($soal->tipe_soal == 'pilihan_ganda')
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Pilihan Jawaban:</h6>
                                                        <div class="ms-3">
                                                            <div
                                                                class="mb-1 {{ $soal->jawaban_benar == 'A' ? 'fw-bold text-success' : '' }}">
                                                                A: {!! $soal->pilihan_a !!}
                                                                @if ($soal->jawaban_benar == 'A')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </div>
                                                            <div
                                                                class="mb-1 {{ $soal->jawaban_benar == 'B' ? 'fw-bold text-success' : '' }}">
                                                                B: {!! $soal->pilihan_b !!}
                                                                @if ($soal->jawaban_benar == 'B')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </div>
                                                            <div
                                                                class="mb-1 {{ $soal->jawaban_benar == 'C' ? 'fw-bold text-success' : '' }}">
                                                                C: {!! $soal->pilihan_c !!}
                                                                @if ($soal->jawaban_benar == 'C')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </div>
                                                            <div
                                                                class="mb-1 {{ $soal->jawaban_benar == 'D' ? 'fw-bold text-success' : '' }}">
                                                                D: {!! $soal->pilihan_d !!}
                                                                @if ($soal->jawaban_benar == 'D')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($soal->tipe_soal == 'benar_salah')
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">Pilihan Jawaban:</h6>
                                                        <div class="ms-3">
                                                            <div
                                                                class="mb-1 {{ $soal->jawaban_benar == 'Benar' ? 'fw-bold text-success' : '' }}">
                                                                Benar
                                                                @if ($soal->jawaban_benar == 'Benar')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </div>
                                                            <div
                                                                class="mb-1 {{ $soal->jawaban_benar == 'Salah' ? 'fw-bold text-success' : '' }}">
                                                                Salah
                                                                @if ($soal->jawaban_benar == 'Salah')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-1"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Jawaban Anda -->
                                                <div class="mb-3">
                                                    <h6 class="mb-2">Jawaban Anda:</h6>
                                                    <div class="ms-3">
                                                        @if (isset($jawaban[$soal->id]['jawaban']) && !empty($jawaban[$soal->id]['jawaban']))
                                                            @if ($soal->tipe_soal == 'essay')
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        {!! nl2br($jawaban[$soal->id]['jawaban']) !!}
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div
                                                                    class="{{ isset($jawaban[$soal->id]['benar']) && $jawaban[$soal->id]['benar'] ? 'text-success' : 'text-danger' }}">
                                                                    {{ $jawaban[$soal->id]['jawaban'] }}
                                                                    @if (isset($jawaban[$soal->id]['benar']) && $jawaban[$soal->id]['benar'])
                                                                        <i class="bi bi-check-circle-fill ms-1"></i>
                                                                    @else
                                                                        <i class="bi bi-x-circle-fill ms-1"></i>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @else
                                                            <em class="text-muted">Tidak ada jawaban</em>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Pembahasan -->
                                                @if ($soal->pembahasan)
                                                    <div class="mb-0">
                                                        <h6 class="mb-2">Pembahasan:</h6>
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                {!! $soal->pembahasan !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('ujians.show', $ujian->id) }}" class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Detail Ujian
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
