@extends('layouts.main')

@section('title', 'Daftar Materi')
@section('page-title', 'Daftar Materi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Materi Kursus</h5>
                        <div>
                            <a href="{{ route('materi.create', request()->query()) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Materi
                            </a>
                            @if (request()->has('modul_id'))
                                <a href="{{ route('materi.reorder.form', ['modul_id' => request('modul_id')]) }}"
                                    class="btn btn-secondary">
                                    <i class="bi bi-arrow-down-up"></i> Atur Urutan
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('materi.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="modul_id" class="form-label">Filter berdasarkan Modul</label>
                            <select class="form-select" name="modul_id" id="modul_id" onchange="this.form.submit()">
                                <option value="">-- Semua Modul --</option>
                                @foreach (\Modules\Modul\Entities\Modul::orderBy('nama_modul')->get() as $modul)
                                    <option value="{{ $modul->id }}"
                                        {{ request('modul_id') == $modul->id ? 'selected' : '' }}>
                                        {{ $modul->nama_modul }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tipe_konten" class="form-label">Filter berdasarkan Tipe</label>
                            <select class="form-select" name="tipe_konten" id="tipe_konten" onchange="this.form.submit()">
                                <option value="">-- Semua Tipe --</option>
                                @foreach (['pdf', 'doc', 'video', 'audio', 'gambar', 'link', 'scorm'] as $tipe)
                                    <option value="{{ $tipe }}"
                                        {{ request('tipe_konten') == $tipe ? 'selected' : '' }}>
                                        {{ ucfirst($tipe) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="is_published" class="form-label">Filter berdasarkan Status</label>
                            <select class="form-select" name="is_published" id="is_published" onchange="this.form.submit()">
                                <option value="">-- Semua Status --</option>
                                <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>
                                    Dipublikasikan</option>
                                <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft
                                </option>
                            </select>
                        </div>
                    </form>

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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Modul</th>
                                    <th scope="col">Judul Materi</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Urutan</th>
                                    <th scope="col">Durasi</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($materis as $key => $materi)
                                    <tr>
                                        <th scope="row">{{ $materis->firstItem() + $key }}</th>
                                        <td>{{ $materi->modul->nama_modul ?? 'N/A' }}</td>
                                        <td>{{ $materi->judul_materi }}</td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($materi->tipe_konten) }}
                                            </span>
                                        </td>
                                        <td>{{ $materi->urutan }}</td>
                                        <td>{{ $materi->durasi_menit ?? '-' }} {{ $materi->durasi_menit ? 'menit' : '' }}
                                        </td>
                                        <td>
                                            @if ($materi->published_at)
                                                <span class="badge bg-success">Dipublikasikan</span>
                                            @else
                                                <span class="badge bg-warning">Draft</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('materi.show', $materi->id) }}">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('materi.edit', $materi->id) }}">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a></li>
                                                    <li>
                                                        <form action="{{ route('materi.toggle-publish', $materi->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item">
                                                                @if ($materi->published_at)
                                                                    <i class="bi bi-x-circle"></i> Batalkan Publikasi
                                                                @else
                                                                    <i class="bi bi-check-circle"></i> Publikasikan
                                                                @endif
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('materi.destroy', $materi->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data materi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $materis->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm(
                            'Apakah Anda yakin ingin menghapus materi ini? Tindakan ini tidak dapat dibatalkan.'
                            )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
