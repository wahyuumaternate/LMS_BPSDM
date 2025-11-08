@extends('layouts.main')

@section('title', 'Detail Pengumpulan Tugas')
@section('page-title', 'Detail Pengumpulan Tugas')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Pengumpulan Tugas</h5>
                        <div>
                            @if ($submission->file_jawaban)
                                <a href="{{ route('submission.download', $submission->id) }}" class="btn btn-info">
                                    <i class="bi bi-download"></i> Download File
                                </a>
                            @endif

                            @if (in_array($submission->status, ['draft', 'returned']))
                                <a href="{{ route('submission.edit', $submission->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            @endif

                            <a href="{{ route('submission.index') }}" class="btn btn-secondary">
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

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informasi Tugas</h5>
                                </div>
                                <div class="card-body">
                                    <h5>{{ $submission->tugas->judul ?? 'N/A' }}</h5>
                                    <p><span
                                            class="badge bg-secondary">{{ $submission->tugas->modul->nama_modul ?? 'N/A' }}</span>
                                    </p>

                                    <div class="mt-3">
                                        <p><strong>Deskripsi Tugas:</strong></p>
                                        <div class="p-3 bg-light rounded">
                                            {!! nl2br(e($submission->tugas->deskripsi ?? 'Tidak ada deskripsi')) !!}
                                        </div>
                                    </div>

                                    @if ($submission->tugas && $submission->tugas->petunjuk)
                                        <div class="mt-3">
                                            <p><strong>Petunjuk:</strong></p>
                                            <div class="p-3 bg-light rounded">
                                                {!! nl2br(e($submission->tugas->petunjuk)) !!}
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-3">
                                        <div class="row">
                                            <div class="col-lg-5 fw-bold">Tanggal Mulai:</div>
                                            <div class="col-lg-7">
                                                {{ $submission->tugas && $submission->tugas->tanggal_mulai
                                                    ? \Carbon\Carbon::parse($submission->tugas->tanggal_mulai)->format('d M Y')
                                                    : 'Tidak ditentukan' }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-5 fw-bold">Deadline:</div>
                                            <div class="col-lg-7">
                                                {{ $submission->tugas && $submission->tugas->tanggal_deadline
                                                    ? \Carbon\Carbon::parse($submission->tugas->tanggal_deadline)->format('d M Y')
                                                    : 'Tidak ditentukan' }}
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-5 fw-bold">Nilai Maksimal:</div>
                                            <div class="col-lg-7">{{ $submission->tugas->nilai_maksimal ?? 100 }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('tugas.show', $submission->tugas_id) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat Detail Tugas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informasi Pengumpulan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-lg-5 fw-bold">ID Pengumpulan:</div>
                                        <div class="col-lg-7">{{ $submission->id }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-lg-5 fw-bold">Nama Peserta:</div>
                                        <div class="col-lg-7">
                                            <a href="{{ route('submission.by-peserta', $submission->peserta_id) }}">
                                                {{ $submission->peserta->nama ?? 'N/A' }}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-lg-5 fw-bold">Status:</div>
                                        <div class="col-lg-7">
                                            @if ($submission->status == 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @elseif($submission->status == 'submitted')
                                                <span class="badge bg-info">Submitted</span>
                                            @elseif($submission->status == 'graded')
                                                <span class="badge bg-success">Sudah Dinilai</span>
                                            @elseif($submission->status == 'returned')
                                                <span class="badge bg-warning">Dikembalikan</span>
                                            @elseif($submission->status == 'late')
                                                <span class="badge bg-danger">Terlambat</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $submission->status }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-lg-5 fw-bold">Tanggal Pengumpulan:</div>
                                        <div class="col-lg-7">
                                            {{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d M Y H:i') : '-' }}
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-lg-5 fw-bold">File Jawaban:</div>
                                        <div class="col-lg-7">
                                            @if ($submission->file_jawaban)
                                                <a href="{{ route('submission.download', $submission->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="bi bi-file-earmark-arrow-down"></i> Download File
                                                </a>
                                            @else
                                                <span class="text-muted">Tidak ada file</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($submission->catatan_peserta)
                                        <div class="mt-3">
                                            <p><strong>Catatan Peserta:</strong></p>
                                            <div class="p-3 bg-light rounded">
                                                {!! nl2br(e($submission->catatan_peserta)) !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($submission->status == 'graded')
                                        <div class="mt-4 p-3 border border-success rounded">
                                            <h5 class="text-success"><i class="bi bi-check-circle"></i> Hasil Penilaian</h5>
                                            <div class="row mb-2">
                                                <div class="col-lg-5 fw-bold">Nilai:</div>
                                                <div class="col-lg-7">
                                                    <span class="badge bg-success fs-6">{{ $submission->nilai }}</span> /
                                                    {{ $submission->tugas->nilai_maksimal ?? 100 }}
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-lg-5 fw-bold">Penilai:</div>
                                                <div class="col-lg-7">{{ $submission->penilai->nama ?? 'N/A' }}</div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-lg-5 fw-bold">Tanggal Penilaian:</div>
                                                <div class="col-lg-7">
                                                    {{ $submission->tanggal_dinilai ? \Carbon\Carbon::parse($submission->tanggal_dinilai)->format('d M Y H:i') : '-' }}
                                                </div>
                                            </div>

                                            @if ($submission->catatan_penilai)
                                                <div class="mt-2">
                                                    <p><strong>Catatan Penilai:</strong></p>
                                                    <div class="p-3 bg-light rounded">
                                                        {!! nl2br(e($submission->catatan_penilai)) !!}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @elseif($submission->status == 'returned')
                                        <div class="mt-4 p-3 border border-warning rounded">
                                            <h5 class="text-warning"><i class="bi bi-arrow-return-left"></i> Dikembalikan
                                                Untuk Revisi</h5>
                                            <div class="row mb-2">
                                                <div class="col-lg-5 fw-bold">Penilai:</div>
                                                <div class="col-lg-7">{{ $submission->penilai->nama ?? 'N/A' }}</div>
                                            </div>

                                            @if ($submission->catatan_penilai)
                                                <div class="mt-2">
                                                    <p><strong>Catatan Perbaikan:</strong></p>
                                                    <div class="p-3 bg-light rounded">
                                                        {!! nl2br(e($submission->catatan_penilai)) !!}
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mt-3">
                                                <a href="{{ route('submission.edit', $submission->id) }}"
                                                    class="btn btn-warning">
                                                    <i class="bi bi-pencil"></i> Edit Pengumpulan
                                                </a>
                                            </div>
                                        </div>
                                    @elseif(in_array($submission->status, ['submitted', 'late']))
                                        <div class="mt-4">
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#gradeModal">
                                                <i class="bi bi-check-circle"></i> Berikan Nilai
                                            </button>
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#returnModal">
                                                <i class="bi bi-arrow-return-left"></i> Kembalikan Untuk Revisi
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1" aria-labelledby="gradeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gradeModalLabel">Berikan Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('submission.grade', $submission->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p><strong>Peserta:</strong> {{ $submission->peserta->nama ?? 'N/A' }}</p>
                            <p><strong>Tugas:</strong> {{ $submission->tugas->judul ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="nilai" class="form-label">Nilai
                                (0-{{ $submission->tugas->nilai_maksimal ?? 100 }})</label>
                            <input type="number" class="form-control" id="nilai" name="nilai" min="0"
                                max="{{ $submission->tugas->nilai_maksimal ?? 100 }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="catatan_penilai" class="form-label">Catatan Penilaian</label>
                            <textarea class="form-control" id="catatan_penilai" name="catatan_penilai" rows="3"></textarea>
                            <div class="form-text">Berikan feedback atau komentar untuk peserta.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Return for Revision Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Kembalikan Untuk Revisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('submission.return', $submission->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p><strong>Peserta:</strong> {{ $submission->peserta->nama ?? 'N/A' }}</p>
                            <p><strong>Tugas:</strong> {{ $submission->tugas->judul ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <label for="return_catatan_penilai" class="form-label">Catatan Untuk Perbaikan <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="return_catatan_penilai" name="catatan_penilai" rows="3" required></textarea>
                            <div class="form-text">Berikan feedback dan petunjuk untuk perbaikan.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Kembalikan Untuk Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
