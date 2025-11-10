@extends('layouts.main')

@section('title', 'Detail Hasil Ujian')
@section('page-title', 'Detail Hasil Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Hasil Ujian</h5>
                        <div>
                            <a href="{{ route('hasil-ujian.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Ujian & Peserta Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6>Informasi Ujian dan Peserta</h6>
                                    <table class="table table-bordered mt-3">
                                        <tr>
                                            <th style="width: 30%">Ujian</th>
                                            <td>{{ $hasil->ujian->judul_ujian }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kursus</th>
                                            <td>{{ $hasil->ujian->kursus->nama_kursus }}</td>
                                        </tr>
                                        <tr>
                                            <th>Peserta</th>
                                            <td>
                                                @if ($hasil->is_simulation)
                                                    <span class="badge bg-info">SIMULASI</span>
                                                    {{ $hasil->user->name ?? 'Admin/Instruktur' }}
                                                @else
                                                    {{ $hasil->peserta->user->name ?? 'Peserta #' . $hasil->peserta_id }}
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Waktu Pengerjaan</th>
                                            <td>
                                                {{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d M Y H:i') : '-' }} -
                                                {{ $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d M Y H:i') : 'Belum Selesai' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Durasi Pengerjaan</th>
                                            <td>{{ $hasil->getDurationTaken() }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <div class="card h-100 bg-light">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Hasil Ujian</h6>
                                            <div class="mt-4">
                                                <h1 class="display-4">{{ number_format($hasil->nilai, 2) }}</h1>
                                                <p class="mb-1">Passing Grade: {{ $hasil->ujian->passing_grade }}%</p>
                                                <div class="mt-3">
                                                    <span
                                                        class="badge fs-6 bg-{{ $hasil->is_passed ? 'success' : 'danger' }}">
                                                        {{ $hasil->is_passed ? 'LULUS' : 'TIDAK LULUS' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse{{ $soal->id }}"
                                                aria-expanded="false" aria-controls="collapse{{ $soal->id }}">
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
                                        <div id="collapse{{ $soal->id }}" class="accordion-collapse collapse"
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

                                                <!-- Jawaban Peserta -->
                                                <div class="mb-3">
                                                    <h6 class="mb-2">Jawaban Peserta:</h6>
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
                </div>
            </div>
        </div>
    </div>
@endsection
