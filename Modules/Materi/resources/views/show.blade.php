
@extends('layouts.main')

@section('title', 'Detail Materi')
@section('page-title', 'Detail Materi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Materi</h5>
                        <div>
                            <a href="{{ route('materi.edit', $materi->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('materi.destroy', $materi->id) }}" method="POST"
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
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Judul Materi</th>
                                    <td>{{ $materi->judul_materi }}</td>
                                </tr>
                                <tr>
                                    <th>Modul</th>
                                    <td>
                                        <a href="{{ route('materi.index', ['modul_id' => $materi->modul_id]) }}">
                                            {{ $materi->modul->nama_modul ?? 'N/A' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipe Konten</th>
                                    <td><span class="badge bg-secondary">{{ ucfirst($materi->tipe_konten) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Urutan</th>
                                    <td>{{ $materi->urutan }}</td>
                                </tr>
                                <tr>
                                    <th>Durasi</th>
                                    <td>{{ $materi->durasi_menit ? $materi->durasi_menit . ' menit' : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Ukuran File</th>
                                    <td>{{ $materi->ukuran_file ? number_format($materi->ukuran_file) . ' KB' : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Materi Wajib</th>
                                    <td>
                                        @if ($materi->is_wajib)
                                            <span class="badge bg-success">Ya</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if ($materi->published_at)
                                            <span class="badge bg-success">Dipublikasikan</span>
                                            <small class="d-block">{{ $materi->published_at->format('d M Y H:i') }}</small>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Pratinjau Konten</h5>
                                </div>
                                <div class="card-body">
                                    @if ($materi->tipe_konten == 'link')
                                        <p>URL: <a href="{{ $materi->file_path }}"
                                                target="_blank">{{ $materi->file_path }}</a></p>
                                        <div class="ratio ratio-16x9 mt-3">
                                            <iframe src="{{ $materi->file_path }}" allowfullscreen></iframe>
                                        </div>
                                    @elseif($materi->tipe_konten == 'gambar' && $materi->file_path)
                                        <img src="{{ Storage::url('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path) }}"
                                            alt="{{ $materi->judul_materi }}" class="img-fluid rounded">
                                    @elseif($materi->tipe_konten == 'video' && $materi->file_path)
                                        <div class="ratio ratio-16x9">
                                            <video controls>
                                                <source
                                                    src="{{ Storage::url('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path) }}"
                                                    type="video/mp4">
                                                Browser Anda tidak mendukung tag video.
                                            </video>
                                        </div>
                                    @elseif($materi->tipe_konten == 'audio' && $materi->file_path)
                                        <audio controls class="w-100">
                                            <source
                                                src="{{ Storage::url('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path) }}"
                                                type="audio/mpeg">
                                            Browser Anda tidak mendukung tag audio.
                                        </audio>
                                    @elseif(in_array($materi->tipe_konten, ['pdf', 'doc']) && $materi->file_path)
                                        <div class="text-center">
                                            <p class="mb-3">
                                                <i class="bi bi-file-earmark-text fs-1"></i>
                                            </p>
                                            <a href="{{ Storage::url('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path) }}"
                                                class="btn btn-primary" target="_blank">
                                                <i class="bi bi-download"></i> Download File
                                            </a>
                                        </div>
                                    @elseif($materi->tipe_konten == 'scorm' && $materi->file_path)
                                        <div class="text-center">
                                            <p class="mb-3">
                                                <i class="bi bi-file-earmark-zip fs-1"></i>
                                            </p>
                                            <p>File SCORM: {{ $materi->file_path }}</p>
                                            <a href="{{ Storage::url('public/materi/files/' . $materi->tipe_konten . '/' . $materi->file_path) }}"
                                                class="btn btn-primary" target="_blank">
                                                <i class="bi bi-download"></i> Download Paket SCORM
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            Tidak ada file yang tersedia
                                        </div>
                                    @endif

                                    <div class="mt-4">
                                        <h6>Deskripsi:</h6>
                                        <p class="mb-0">{{ $materi->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('materi.index', ['modul_id' => $materi->modul_id]) }}"
                                    class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                                </a>
                                <form action="{{ route('materi.toggle-publish', $materi->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        class="btn {{ $materi->published_at ? 'btn-warning' : 'btn-success' }}">
                                        @if ($materi->published_at)
                                            <i class="bi bi-x-circle"></i> Batalkan Publikasi
                                        @else
                                            <i class="bi bi-check-circle"></i> Publikasikan
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
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
                            'Apakah Anda yakin ingin menghapus materi ini? Tindakan ini tidak dapat dibatalkan.'
                            )) {
                        this.submit();
                    }
                });
            }
        });
    </script>
@endpush
