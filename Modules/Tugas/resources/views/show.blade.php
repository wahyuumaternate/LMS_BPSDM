@extends('layouts.main')

@section('title', 'Detail Tugas')
@section('page-title', 'Detail Tugas')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Tugas</h5>
                        <div>
                            <a href="{{ route('tugas.submissions', $tugas->id) }}" class="btn btn-info">
                                <i class="bi bi-list-check"></i> Lihat Pengumpulan
                            </a>
                            <a href="{{ route('tugas.edit', $tugas->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('tugas.destroy', $tugas->id) }}" method="POST"
                                class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
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
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h4>{{ $tugas->judul }}</h4>

                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="badge bg-secondary">Modul:
                                            {{ $tugas->modul->nama_modul ?? 'N/A' }}</span>
                                        @if ($tugas->is_published)
                                            <span class="badge bg-success">Dipublikasikan</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif

                                        <form action="{{ route('tugas.toggle-publish', $tugas->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $tugas->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                @if ($tugas->is_published)
                                                    <i class="bi bi-x-circle"></i> Batalkan Publikasi
                                                @else
                                                    <i class="bi bi-check-circle"></i> Publikasikan
                                                @endif
                                            </button>
                                        </form>
                                    </div>

                                    <div class="mt-4">
                                        <h6>Deskripsi Tugas:</h6>
                                        <div class="p-3 bg-light rounded">
                                            {!! nl2br(e($tugas->deskripsi)) !!}
                                        </div>
                                    </div>

                                    @if ($tugas->petunjuk)
                                        <div class="mt-4">
                                            <h6>Petunjuk:</h6>
                                            <div class="p-3 bg-light rounded">
                                                {!! nl2br(e($tugas->petunjuk)) !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($tugas->file_tugas)
                                        <div class="mt-4">
                                            <h6>File Tugas:</h6>
                                            <a href="{{ Storage::url('public/' . $tugas->file_tugas) }}"
                                                class="btn btn-primary" target="_blank">
                                                <i class="bi bi-file-earmark-arrow-down"></i> Download File Tugas
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informasi Tugas</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Tanggal Mulai
                                            <span>{{ $tugas->tanggal_mulai ? \Carbon\Carbon::parse($tugas->tanggal_mulai)->format('d M Y') : 'Tidak ditentukan' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Deadline
                                            <span>
                                                {{ $tugas->tanggal_deadline ? \Carbon\Carbon::parse($tugas->tanggal_deadline)->format('d M Y') : 'Tidak ditentukan' }}
                                                @if ($tugas->tanggal_deadline)
                                                    @if (\Carbon\Carbon::parse($tugas->tanggal_deadline)->isPast())
                                                        <span class="badge bg-danger">Lewat</span>
                                                    @elseif(\Carbon\Carbon::parse($tugas->tanggal_deadline)->diffInDays(\Carbon\Carbon::now()) <= 7)
                                                        <span class="badge bg-warning">Segera</span>
                                                    @endif
                                                @endif
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Nilai Maksimal
                                            <span>{{ $tugas->nilai_maksimal ?? 100 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Bobot Nilai
                                            <span>{{ $tugas->bobot_nilai ?? 1 }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Status
                                            <span>
                                                @if ($tugas->is_published)
                                                    <span class="badge bg-success">Dipublikasikan</span><br>
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($tugas->published_at)->format('d M Y H:i') }}</small>
                                                @else
                                                    <span class="badge bg-warning">Draft</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Dibuat Pada
                                            <span>{{ \Carbon\Carbon::parse($tugas->created_at)->format('d M Y H:i') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Terakhir Diperbarui
                                            <span>{{ \Carbon\Carbon::parse($tugas->updated_at)->format('d M Y H:i') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Statistik Pengumpulan</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $totalSubmissions = $tugas->submissions->count();
                                        $completedSubmissions = $tugas->submissions
                                            ->where('status', 'dinilai')
                                            ->count();
                                        $pendingSubmissions = $tugas->submissions
                                            ->where('status', 'menunggu_penilaian')
                                            ->count();
                                        $averageScore =
                                            $tugas->submissions->where('status', 'dinilai')->avg('nilai') ?? 0;
                                    @endphp

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Total Pengumpulan</span>
                                            <span class="badge bg-primary">{{ $totalSubmissions }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Sudah Dinilai</span>
                                            <span class="badge bg-success">{{ $completedSubmissions }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Menunggu Penilaian</span>
                                            <span class="badge bg-warning">{{ $pendingSubmissions }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Nilai Rata-Rata</span>
                                            <span class="badge bg-info">{{ number_format($averageScore, 1) }}</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('tugas.submissions', $tugas->id) }}" class="btn btn-primary w-100">
                                        <i class="bi bi-list-check"></i> Kelola Pengumpulan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('tugas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Tugas
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
            // Delete confirmation
            const deleteForm = document.querySelector('.delete-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm(
                            'Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua pengumpulan terkait.'
                            )) {
                        this.submit();
                    }
                });
            }
        });
    </script>
@endpush
