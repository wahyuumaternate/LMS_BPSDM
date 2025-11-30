@extends('layouts.main')

@section('title', $berita->judul)

@section('content')
<div class="pagetitle">
    <h1>Detail Berita</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('berita.index') }}">Berita</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($berita->judul, 50) }}</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                        <div>
                            {!! $berita->status_badge !!}
                            @if($berita->is_featured)
                                <span class="badge bg-warning text-dark ms-1">
                                    <i class="bi bi-star-fill"></i> Featured
                                </span>
                            @endif
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('berita.edit', $berita->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            
                            @if($berita->status === 'draft')
                                <form action="{{ route('berita.publish', $berita->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle"></i> Publish
                                    </button>
                                </form>
                            @endif
                            
                            @if($berita->status === 'published')
                                <form action="{{ route('berita.archive', $berita->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-secondary">
                                        <i class="bi bi-archive"></i> Archive
                                    </button>
                                </form>
                            @endif
                            
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete()">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                            
                            <form id="delete-form" action="{{ route('berita.destroy', $berita->id) }}" 
                                  method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>

                    <!-- Title -->
                    <h2 class="mb-3">{{ $berita->judul }}</h2>

                    <!-- Meta Info -->
                    <div class="mb-4">
                        <div class="row g-2 text-muted">
                            <div class="col-auto">
                                <i class="bi bi-person"></i>
                                @if($berita->penulis)
                                    {{ $berita->penulis->nama_lengkap }}
                                @else
                                    <span class="text-muted">Admin</span>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar"></i>
                                @if($berita->published_at)
                                    {{ $berita->formatted_published_at }}
                                @else
                                    {{ $berita->formatted_created_at }}
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-eye"></i>
                                {{ number_format($berita->view_count) }} views
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-clock"></i>
                                {{ $berita->reading_time }}
                            </div>
                            <div class="col-auto">
                                <span class="badge {{ $berita->kategori->badge_color_class }}">
                                    <i class="{{ $berita->kategori->icon }}"></i>
                                    {{ $berita->kategori->nama_kategori }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    @if($berita->gambar_utama)
                    <div class="mb-4">
                        <img src="{{ $berita->gambar_utama_url }}" 
                             alt="{{ $berita->judul }}" 
                             class="img-fluid rounded">
                        @if($berita->sumber_gambar)
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-camera"></i> Sumber: {{ $berita->sumber_gambar }}
                            </small>
                        @endif
                    </div>
                    @endif

                    <!-- Ringkasan -->
                    @if($berita->ringkasan)
                    <div class="alert alert-info mb-4">
                        <strong><i class="bi bi-info-circle"></i> Ringkasan:</strong>
                        <p class="mb-0 mt-2">{{ $berita->ringkasan }}</p>
                    </div>
                    @endif

                    <!-- Content -->
                    <div class="content-body">
                        {!! $berita->konten !!}
                    </div>

                    <hr class="my-4">

                    <!-- Footer Meta -->
                    <div class="row g-3 text-muted small">
                        <div class="col-md-6">
                            <strong>Dibuat:</strong> {{ $berita->formatted_created_at }}
                        </div>
                        <div class="col-md-6">
                            <strong>Terakhir Update:</strong> {{ $berita->updated_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Berita -->
            @if($related->count() > 0)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Berita Terkait</h5>
                    <div class="row g-3">
                        @foreach($related as $item)
                        <div class="col-md-4">
                            <div class="card h-100">
                                @if($item->gambar_utama)
                                <img src="{{ $item->gambar_utama_url }}" 
                                     class="card-img-top" 
                                     alt="{{ $item->judul }}"
                                     style="height: 150px; object-fit: cover;">
                                @endif
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="{{ route('berita.show', $item->id) }}" class="text-dark">
                                            {{ Str::limit($item->judul, 60) }}
                                        </a>
                                    </h6>
                                    <p class="card-text small text-muted">
                                        {{ Str::limit(strip_tags($item->excerpt), 80) }}
                                    </p>
                                    <a href="{{ route('berita.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                        Baca Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Navigation -->
            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('berita.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informasi Berita</h5>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Status:</label>
                        <div>{!! $berita->status_badge !!}</div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Kategori:</label>
                        <div>
                            <span class="badge {{ $berita->kategori->badge_color_class }}">
                                <i class="{{ $berita->kategori->icon }}"></i>
                                {{ $berita->kategori->nama_kategori }}
                            </span>
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Penulis:</label>
                        <div>
                            @if($berita->penulis)
                                <i class="bi bi-person-circle"></i>
                                {{ $berita->penulis->nama_lengkap }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Tanggal Publish:</label>
                        <div>
                            @if($berita->published_at)
                                <i class="bi bi-calendar-check"></i>
                                {{ $berita->formatted_published_at }}
                            @else
                                <span class="text-muted">Belum dipublish</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Views:</label>
                        <div>
                            <i class="bi bi-eye"></i>
                            {{ number_format($berita->view_count) }}
                        </div>
                    </div>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Waktu Baca:</label>
                        <div>
                            <i class="bi bi-clock"></i>
                            {{ $berita->reading_time }}
                        </div>
                    </div>

                    @if($berita->is_featured)
                    <div class="info-item mb-3">
                        <label class="fw-bold">Featured:</label>
                        <div>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> Yes
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- SEO Info -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">SEO Information</h5>

                    <div class="info-item mb-3">
                        <label class="fw-bold">Meta Title:</label>
                        <div class="text-muted small">
                            {{ $berita->meta_title ?: $berita->judul }}
                        </div>
                    </div>

                    @if($berita->meta_description)
                    <div class="info-item mb-3">
                        <label class="fw-bold">Meta Description:</label>
                        <div class="text-muted small">
                            {{ $berita->meta_description }}
                        </div>
                    </div>
                    @endif

                    @if($berita->meta_keywords)
                    <div class="info-item mb-3">
                        <label class="fw-bold">Keywords:</label>
                        <div>
                            @foreach(explode(',', $berita->meta_keywords) as $keyword)
                                <span class="badge bg-secondary me-1">{{ trim($keyword) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="info-item">
                        <label class="fw-bold">Slug:</label>
                        <div>
                            <code class="small">{{ $berita->slug }}</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    
                    <div class="d-grid gap-2">
                        <a href="{{ route('berita.edit', $berita->id) }}" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Edit Berita
                        </a>

                        @if($berita->status === 'draft')
                        <form action="{{ route('berita.publish', $berita->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Publish Sekarang
                            </button>
                        </form>
                        @endif

                        @if($berita->status === 'published')
                        <form action="{{ route('berita.archive', $berita->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-archive"></i> Arsipkan
                            </button>
                        </form>
                        @endif

                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="bi bi-trash"></i> Hapus Berita
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.content-body {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #333;
}

.content-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 20px 0;
}

.content-body p {
    margin-bottom: 1.5rem;
}

.content-body h1, .content-body h2, .content-body h3 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.content-body ul, .content-body ol {
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}

.content-body blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #666;
}

.info-item label {
    display: block;
    margin-bottom: 0.25rem;
    color: #666;
    font-size: 0.9rem;
}
</style>
@endpush

@push('scripts')

<script>
function confirmDelete() {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: 'Apakah Anda yakin ingin menghapus berita <strong>{{ $berita->judul }}</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit();
        }
    });
}
</script>
@endpush