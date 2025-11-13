@extends('layouts.main')

@section('title', 'Hasil Simulasi Ujian')
@section('page-title', 'Hasil Simulasi Ujian: ' . $ujian->judul_ujian)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Ujian Info -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Info Ujian</h6>
                        <p class="mb-1"><strong>Judul Ujian:</strong> {{ $ujian->judul_ujian }}</p>
                        <p class="mb-1"><strong>Kursus:</strong> {{ $ujian->kursus->nama_kursus }}</p>
                        <p class="mb-1"><strong>Durasi:</strong> {{ $ujian->durasi_menit }} menit</p>
                        <p class="mb-1"><strong>Total Soal:</strong> {{ $soalUjians->count() }} soal</p>
                        <p class="mb-0"><strong>Passing Grade:</strong> {{ $ujian->passing_grade }}%</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Hasil Simulasi -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Hasil Simulasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div
                                        class="card {{ $ujianResult->is_passed ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                        <div class="card-body text-center">
                                            <h4 class="card-title">Status</h4>
                                            <h2 class="display-4 mt-3 mb-3">
                                                <i
                                                    class="bi {{ $ujianResult->is_passed ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                                {{ $ujianResult->is_passed ? 'LULUS' : 'TIDAK LULUS' }}
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h4 class="card-title">Nilai</h4>
                                            <h2 class="display-4 mt-3 mb-3">
                                                {{ number_format($ujianResult->nilai, 1) }}%
                                            </h2>
                                            <p class="card-text text-muted">
                                                Batas kelulusan: {{ $ujian->passing_grade }}%
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Waktu Pengerjaan</h5>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td width="50%">Mulai</td>
                                                    <td>:
                                                        {{ \Carbon\Carbon::parse($ujianResult->waktu_mulai)->format('d M Y, H:i:s') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Selesai</td>
                                                    <td>:
                                                        {{ \Carbon\Carbon::parse($ujianResult->waktu_selesai)->format('d M Y, H:i:s') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Durasi</td>
                                                    <td>:
                                                        {{ \Carbon\Carbon::parse($ujianResult->waktu_mulai)->diffForHumans(\Carbon\Carbon::parse($ujianResult->waktu_selesai), true) }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Ringkasan Jawaban</h5>
                                            <div class="d-flex justify-content-around text-center">
                                                <div>
                                                    <h4 class="text-success mb-0">
                                                        {{ collect($jawaban)->where('benar', true)->count() }}</h4>
                                                    <small class="text-muted">Benar</small>
                                                </div>
                                                <div>
                                                    <h4 class="text-danger mb-0">
                                                        {{ $soalUjians->count() - collect($jawaban)->where('benar', true)->count() }}
                                                    </h4>
                                                    <small class="text-muted">Salah/Kosong</small>
                                                </div>
                                                <div>
                                                    <h4 class="text-primary mb-0">{{ $soalUjians->count() }}</h4>
                                                    <small class="text-muted">Total Soal</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Soal dan Jawaban -->
                    <h5 class="mt-4 mb-3">Detail Soal dan Jawaban</h5>
                    <div class="accordion" id="accordionJawaban">
                        @foreach ($soalUjians as $index => $soal)
                            @php
                                $jawabanPeserta = isset($jawaban[$soal->id]['jawaban'])
                                    ? $jawaban[$soal->id]['jawaban']
                                    : '';
                                $benar = isset($jawaban[$soal->id]['benar']) ? $jawaban[$soal->id]['benar'] : false;
                                $poin = isset($jawaban[$soal->id]['poin']) ? $jawaban[$soal->id]['poin'] : 0;
                            @endphp
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $soal->id }}">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $soal->id }}"
                                        aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                        aria-controls="collapse{{ $soal->id }}">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <span>Soal {{ $index + 1 }}</span>
                                            <span class="badge bg-{{ $benar ? 'success' : 'danger' }} ms-2">
                                                {{ $benar ? 'Benar' : 'Salah' }} ({{ $poin }} /
                                                {{ $soal->poin }} poin)
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $soal->id }}"
                                    class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                    aria-labelledby="heading{{ $soal->id }}" data-bs-parent="#accordionJawaban">
                                    <div class="accordion-body">
                                        <div class="mb-3">
                                            {!! $soal->pertanyaan !!}
                                        </div>

                                        @if ($soal->tipe_soal == 'pilihan_ganda')
                                            <div class="mb-4">
                                                <div class="list-group">
                                                    <div
                                                        class="list-group-item {{ $jawabanPeserta == 'A' ? ($benar ? 'list-group-item-success' : 'list-group-item-danger') : ($soal->jawaban_benar == 'A' ? 'list-group-item-success' : '') }}">
                                                        <div class="d-flex w-100">
                                                            <span class="me-3">A.</span>
                                                            <div>{!! $soal->pilihan_a !!}</div>
                                                            @if ($soal->jawaban_benar == 'A')
                                                                <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                                            @endif
                                                            @if ($jawabanPeserta == 'A' && $jawabanPeserta != $soal->jawaban_benar)
                                                                <i class="bi bi-x-circle-fill text-danger ms-auto"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="list-group-item {{ $jawabanPeserta == 'B' ? ($benar ? 'list-group-item-success' : 'list-group-item-danger') : ($soal->jawaban_benar == 'B' ? 'list-group-item-success' : '') }}">
                                                        <div class="d-flex w-100">
                                                            <span class="me-3">B.</span>
                                                            <div>{!! $soal->pilihan_b !!}</div>
                                                            @if ($soal->jawaban_benar == 'B')
                                                                <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                                            @endif
                                                            @if ($jawabanPeserta == 'B' && $jawabanPeserta != $soal->jawaban_benar)
                                                                <i class="bi bi-x-circle-fill text-danger ms-auto"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="list-group-item {{ $jawabanPeserta == 'C' ? ($benar ? 'list-group-item-success' : 'list-group-item-danger') : ($soal->jawaban_benar == 'C' ? 'list-group-item-success' : '') }}">
                                                        <div class="d-flex w-100">
                                                            <span class="me-3">C.</span>
                                                            <div>{!! $soal->pilihan_c !!}</div>
                                                            @if ($soal->jawaban_benar == 'C')
                                                                <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                                            @endif
                                                            @if ($jawabanPeserta == 'C' && $jawabanPeserta != $soal->jawaban_benar)
                                                                <i class="bi bi-x-circle-fill text-danger ms-auto"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="list-group-item {{ $jawabanPeserta == 'D' ? ($benar ? 'list-group-item-success' : 'list-group-item-danger') : ($soal->jawaban_benar == 'D' ? 'list-group-item-success' : '') }}">
                                                        <div class="d-flex w-100">
                                                            <span class="me-3">D.</span>
                                                            <div>{!! $soal->pilihan_d !!}</div>
                                                            @if ($soal->jawaban_benar == 'D')
                                                                <i class="bi bi-check-circle-fill text-success ms-auto"></i>
                                                            @endif
                                                            @if ($jawabanPeserta == 'D' && $jawabanPeserta != $soal->jawaban_benar)
                                                                <i class="bi bi-x-circle-fill text-danger ms-auto"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-3">
                                                    <span class="fw-bold">Jawaban Anda: </span>
                                                    @if ($jawabanPeserta)
                                                        <span
                                                            class="{{ $benar ? 'text-success' : 'text-danger' }}">{{ $jawabanPeserta }}</span>
                                                    @else
                                                        <span class="text-muted">(Tidak dijawab)</span>
                                                    @endif
                                                </div>
                                                <div class="mt-1">
                                                    <span class="fw-bold">Jawaban Benar: </span>
                                                    <span class="text-success">{{ $soal->jawaban_benar }}</span>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($soal->pembahasan)
                                            <div class="card mt-3 bg-light">
                                                <div class="card-header">
                                                    <h6 class="mb-0">Pembahasan</h6>
                                                </div>
                                                <div class="card-body">
                                                    {!! $soal->pembahasan !!}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('ujians.show', $ujian->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Detail Ujian
                        </a>
                        <a href="{{ route('ujians.simulate', $ujian->id) }}" class="btn btn-primary">
                            <i class="bi bi-arrow-repeat"></i> Simulasi Lagi
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
            // Optional JS for handling UI interactions
        });
    </script>
@endpush
