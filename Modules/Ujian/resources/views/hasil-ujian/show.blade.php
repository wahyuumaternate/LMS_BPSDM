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
                        <a href="{{ route('hasil-ujian.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <!-- Ujian Info -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Informasi Ujian</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%">Judul Ujian</td>
                                            <td>: {{ $hasil->ujian->judul_ujian }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kursus</td>
                                            <td>: {{ $hasil->ujian->kursus->nama_kursus }}</td>
                                        </tr>
                                        <tr>
                                            <td>Durasi</td>
                                            <td>: {{ $hasil->ujian->durasi_menit }} menit</td>
                                        </tr>
                                        <tr>
                                            <td>Passing Grade</td>
                                            <td>: {{ $hasil->ujian->passing_grade }}%</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Informasi Peserta</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%">Nama Peserta</td>
                                            <td>: {{ $hasil->peserta->nama_lengkap }}</td>
                                        </tr>
                                        <tr>
                                            <td>NIP</td>
                                            <td>: {{ $hasil->peserta->nip ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Jabatan</td>
                                            <td>: {{ $hasil->peserta->jabatan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td>OPD</td>
                                            <td>: {{ $hasil->peserta->opd->nama_opd ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Hasil Ujian</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h1 class="display-4 {{ $hasil->is_passed ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($hasil->nilai, 1) }}%
                                        </h1>
                                        <span
                                            class="badge {{ $hasil->getStatusBadgeClass() }} fs-6">{{ $hasil->getStatusText() }}</span>
                                    </div>

                                    <table class="table table-borderless">
                                        <tr>
                                            <td>Total Soal</td>
                                            <td>: {{ $soalUjians->count() }}</td>
                                        </tr>
                                        <tr>
                                            <td>Jawaban Benar</td>
                                            <td>: {{ $hasil->getCorrectAnswersCount() }}</td>
                                        </tr>
                                        <tr>
                                            <td>Jawaban Salah</td>
                                            <td>: {{ $hasil->getIncorrectAnswersCount() }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Informasi Waktu</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%">Waktu Mulai</td>
                                            <td>:
                                                {{ $hasil->waktu_mulai ? $hasil->waktu_mulai->format('d M Y, H:i:s') : '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Waktu Selesai</td>
                                            <td>:
                                                {{ $hasil->waktu_selesai ? $hasil->waktu_selesai->format('d M Y, H:i:s') : 'Sedang Berjalan' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Durasi Pengerjaan</td>
                                            <td>: {{ $hasil->getDurationTaken() }}</td>
                                        </tr>
                                        <tr>
                                            <td>Tanggal Dinilai</td>
                                            <td>:
                                                {{ $hasil->tanggal_dinilai ? $hasil->tanggal_dinilai->format('d M Y, H:i:s') : '-' }}
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
                            <h6 class="mb-0">Detail Jawaban</h6>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="accordionJawaban">
                                @foreach ($soalUjians as $index => $soal)
                                    @php
                                        // Pastikan ID soal sebagai string untuk akses array
                                        $soalId = (string) $soal->id;

                                        // Ambil data jawaban untuk soal ini
                                        $jawabanPeserta = '';
                                        $benar = false;
                                        $poin = 0;

                                        if (isset($jawaban[$soalId])) {
                                            $jawabanData = $jawaban[$soalId];
                                            $jawabanPeserta = $jawabanData['jawaban'] ?? '';
                                            $benar = $jawabanData['benar'] ?? false;
                                            $poin = $jawabanData['poin'] ?? 0;
                                        }
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ $soal->id }}">
                                            <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse{{ $soal->id }}"
                                                aria-expanded="{{ $index == 0 ? 'true' : 'false' }}"
                                                aria-controls="collapse{{ $soal->id }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <span>Soal {{ $index + 1 }}</span>
                                                    <span class="badge {{ $benar ? 'bg-success' : 'bg-danger' }} ms-2">
                                                        {{ $benar ? 'Benar' : 'Salah' }} ({{ $poin }} /
                                                        {{ $soal->poin }} poin)
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ $soal->id }}"
                                            class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                            aria-labelledby="heading{{ $soal->id }}"
                                            data-bs-parent="#accordionJawaban">
                                            <div class="accordion-body">
                                                <div class="mb-3">
                                                    {!! $soal->pertanyaan !!}
                                                </div>

                                                <!-- Pilihan Ganda -->
                                                <div class="mb-4">
                                                    <div class="list-group">
                                                        <div
                                                            class="list-group-item {{ $jawabanPeserta == 'A' ? ($benar ? 'list-group-item-success' : 'list-group-item-danger') : ($soal->jawaban_benar == 'A' ? 'list-group-item-success' : '') }}">
                                                            <div class="d-flex w-100">
                                                                <span class="me-3">A.</span>
                                                                <div>{!! $soal->pilihan_a !!}</div>
                                                                @if ($soal->jawaban_benar == 'A')
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-auto"></i>
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
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-auto"></i>
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
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-auto"></i>
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
                                                                    <i
                                                                        class="bi bi-check-circle-fill text-success ms-auto"></i>
                                                                @endif
                                                                @if ($jawabanPeserta == 'D' && $jawabanPeserta != $soal->jawaban_benar)
                                                                    <i class="bi bi-x-circle-fill text-danger ms-auto"></i>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-3">
                                                        <span class="fw-bold">Jawaban Peserta: </span>
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

                                                @if ($soal->pembahasan && ($isInstructor || $hasil->waktu_selesai))
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
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('hasil-ujian.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Hasil
                        </a>
                        {{-- <a href="{{ route('ujian.show', $hasil->ujian_id) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Lihat Detail Ujian
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add JavaScript for enhancing the UI
        });
    </script>
@endpush
