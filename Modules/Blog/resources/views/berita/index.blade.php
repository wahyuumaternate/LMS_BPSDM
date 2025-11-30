@extends('layouts.main')

@section('title', 'Kelola Berita')

@section('content')
<div class="pagetitle">
    <h1>Kelola Berita</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Berita</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Berita</h5>
                    <a href="{{ route('berita.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Buat Berita
                    </a>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" action="{{ route('berita.index') }}" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Cari berita..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="kategori" id="filterKategori" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach($kategoris as $kat)
                                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
                                            {{ $kat->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" id="filterStatus" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Penulis</th>
                                    <th>Status</th>
                                    <th width="100">Views</th>
                                    <th>Tanggal</th>
                                    <th width="180">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($berita as $item)
                                <tr>
                                    <td>{{ $loop->iteration + ($berita->currentPage() - 1) * $berita->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-start">
                                            @if($item->gambar_utama)
                                                <img src="{{ $item->gambar_utama_url }}" 
                                                     alt="{{ $item->judul }}" 
                                                     class="rounded me-2"
                                                     style="width: 60px; height: 40px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong>{{ Str::limit($item->judul, 50) }}</strong>
                                                @if($item->is_featured)
                                                    <br><span class="badge bg-warning text-dark">
                                                        <i class="bi bi-star-fill"></i> Featured
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $item->kategori->badge_color_class }}">
                                            <i class="{{ $item->kategori->icon }}"></i>
                                            {{ $item->kategori->nama_kategori }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->penulis)
                                            {{ $item->penulis->nama_lengkap }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{!! $item->status_badge !!}</td>
                                    <td>
                                        <i class="bi bi-eye"></i> {{ number_format($item->view_count) }}
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $item->formatted_created_at }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('berita.show', $item->id) }}" 
                                               class="btn btn-info" title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('berita.edit', $item->id) }}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger" 
                                                    onclick="confirmDelete({{ $item->id }})" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        <form id="delete-form-{{ $item->id }}" 
                                              action="{{ route('berita.destroy', $item->id) }}" 
                                              method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada berita</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $berita->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')

<script>
jQuery(document).ready(function() {
    // Init Select2
    jQuery('#filterKategori').select2({
        theme: 'bootstrap-5',
        placeholder: 'Semua Kategori',
        allowClear: true,
        width: '100%'
    });

    jQuery('#filterStatus').select2({
        theme: 'bootstrap-5',
        placeholder: 'Semua Status',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: -1
    });
});

function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus berita ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush