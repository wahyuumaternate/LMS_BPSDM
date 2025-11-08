@extends('layouts.main')

@section('title', 'Manajemen Tugas')
@section('page-title', 'Manajemen Tugas')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Daftar Tugas</h5>
                    <a href="{{ route('tugas.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Tugas
                    </a>
                </div>

                <!-- Filter Form -->
                <form action="{{ route('tugas.index') }}" method="GET" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="modul_id" class="form-label">Filter berdasarkan Modul</label>
                        <select class="form-select" name="modul_id" id="modul_id" onchange="this.form.submit()">
                            <option value="">-- Semua Modul --</option>
                            @foreach($moduls as $modul)
                                <option value="{{ $modul->id }}" {{ request('modul_id') == $modul->id ? 'selected' : '' }}>
                                    {{ $modul->nama_modul }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="is_published" class="form-label">Filter berdasarkan Status</label>
                        <select class="form-select" name="is_published" id="is_published" onchange="this.form.submit()">
                            <option value="">-- Semua Status --</option>
                            <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Dipublikasikan</option>
                            <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                </form>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Modul</th>
                                <th>Judul Tugas</th>
                                <th>Tanggal Mulai</th>
                                <th>Deadline</th>
                                <th>Nilai Maks</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugas as $key => $item)
                                <tr>
                                    <td>{{ $tugas->firstItem() + $key }}</td>
                                    <td>{{ $item->modul->nama_modul ?? 'N/A' }}</td>
                                    <td>{{ $item->judul }}</td>
                                    <td>{{ $item->tanggal_mulai ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($item->tanggal_deadline)
                                            {{ \Carbon\Carbon::parse($item->tanggal_deadline)->format('d M Y') }}
                                            @if(\Carbon\Carbon::parse($item->tanggal_deadline)->isPast())
                                                <span class="badge bg-danger">Lewat</span>
                                            @elseif(\Carbon\Carbon::parse($item->tanggal_deadline)->diffInDays(\Carbon\Carbon::now()) <= 7)
                                                <span class="badge bg-warning">Segera</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->nilai_maksimal ?? 100 }}</td>
                                    <td>
                                        @if($item->is_published)
                                            <span class="badge bg-success">Dipublikasikan</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Aksi
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="{{ route('tugas.show', $item->id) }}">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('tugas.submissions', $item->id) }}">
                                                    <i class="bi bi-list-check"></i> Pengumpulan
                                                </a></li>
                                                <li><a class="dropdown-item" href="{{ route('tugas.edit', $item->id) }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a></li>
                                                <li>
                                                    <form action="{{ route('tugas.toggle-publish', $item->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            @if($item->is_published)
                                                                <i class="bi bi-x-circle"></i> Batalkan Publikasi
                                                            @else
                                                                <i class="bi bi-check-circle"></i> Publikasikan
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('tugas.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
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
                                    <td colspan="8" class="text-center">Tidak ada data tugas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{ $tugas->withQueryString()->links() }}
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
                if (confirm('Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan dan akan menghapus semua pengumpulan terkait.')) {
                    this.submit();
                }
            });
        });
        
        // Select2 for better dropdowns (if available)
        if (typeof $.fn.select2 !== 'undefined') {
            $('#modul_id').select2({
                placeholder: "-- Semua Modul --",
                allowClear: true
            });
        }
    });
</script>
@endpush