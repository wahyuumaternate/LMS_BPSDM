@extends('layouts.main')

@section('title', 'Detail Ujian')
@section('page-title', 'Detail Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Ujian</h5>
                        <div>
                            <a href="{{ route('ujians.edit', $ujian->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Ujian
                            </a>
                            <a href="{{ route('soal-ujian.by-ujian', $ujian->id) }}" class="btn btn-success">
                                <i class="bi bi-list-check"></i> Kelola Soal
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Kursus</th>
                                    <td>{{ $ujian->kursus->nama_kursus ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Judul Ujian</th>
                                    <td>{{ $ujian->judul_ujian }}</td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td>{{ $ujian->deskripsi ?? 'Tidak ada deskripsi' }}</td>
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
                                    <th>Durasi</th>
                                    <td>{{ $ujian->durasi_menit }} menit ({{ $ujian->getDurationFormatted() }})</td>
                                </tr>
                                <tr>
                                    <th>Passing Grade</th>
                                    <td>{{ $ujian->passing_grade }}%</td>
                                </tr>
                                <tr>
                                    <th>Bobot Nilai</th>
                                    <td>{{ $ujian->bobot_nilai }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Soal</th>
                                    <td>{{ $ujian->jumlah_soal }}</td>
                                </tr>
                                <tr>
                                    <th>Pengaturan Tambahan</th>
                                    <td>
                                        <div class="mb-1">
                                            <i
                                                class="bi {{ $ujian->random_soal ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                                            Soal ditampilkan secara acak
                                        </div>
                                        <div>
                                            <i
                                                class="bi {{ $ujian->tampilkan_hasil ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}"></i>
                                            Tampilkan hasil setelah selesai ujian
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Aturan Ujian</th>
                                    <td>{{ $ujian->aturan_ujian ?? 'Tidak ada aturan khusus' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span
                                            class="badge bg-{{ $ujian->isActive() ? 'success' : ($ujian->hasStarted() ? 'danger' : 'warning') }}">
                                            {{ $ujian->getStatusText() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Dibuat pada</th>
                                    <td>{{ $ujian->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Diperbarui pada</th>
                                    <td>{{ $ujian->updated_at->format('d M Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Statistik Ujian</h5>
                                    <div class="d-flex flex-column gap-3">
                                        <div>
                                            <h6>Jumlah Peserta</h6>
                                            <h3>{{ $ujian->ujianResults->count() }} <small
                                                    class="text-muted">peserta</small></h3>
                                        </div>
                                        <div>
                                            <h6>Jumlah Lulus</h6>
                                            <h3>{{ $ujian->ujianResults->where('is_passed', true)->count() }} <small
                                                    class="text-muted">peserta</small></h3>
                                        </div>
                                        <div>
                                            <h6>Rata-rata Nilai</h6>
                                            <h3>{{ number_format($ujian->ujianResults->avg('nilai'), 2) }} <small
                                                    class="text-muted">dari 100</small></h3>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <a href="{{ route('hasil-ujian.ujian-overview', $ujian->id) }}"
                                            class="btn btn-info w-100">
                                            <i class="bi bi-bar-chart"></i> Lihat Detail Hasil
                                        </a>
                                    </div>
                                    <hr>
                                    <div>
                                        <a href="{{ route('ujians.simulate', $ujian->id) }}"
                                            class="btn btn-success w-100 mb-2">
                                            <i class="bi bi-play-circle"></i> Simulasi Ujian
                                        </a>
                                        <div class="text-muted small">
                                            Anda dapat melakukan simulasi ujian untuk melihat tampilan ujian dari sisi
                                            peserta
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('ujians.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
