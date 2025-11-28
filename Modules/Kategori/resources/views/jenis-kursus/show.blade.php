@extends('layouts.main')

@section('title', 'Detail Jenis Kursus')
@section('page-title', 'Detail Jenis Kursus')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Detail Jenis Kursus</h5>
                        <a href="{{ route('kategori.jenis-kursus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>

                    <div class="row mb-3">
    <div class="col-md-3"><strong>Kode Jenis:</strong></div>
    <div class="col-md-9">
        <span class="badge bg-primary fs-6">{{ $jenisKursus->kode_jenis }}</span>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><strong>Nama Jenis:</strong></div>
    <div class="col-md-9">{{ $jenisKursus->nama_jenis }}</div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><strong>Kategori:</strong></div>
    <div class="col-md-9">
        @if($jenisKursus->kategoriKursus)
            <span class="badge bg-info text-white fs-6">
                <i class="bi {{ $jenisKursus->kategoriKursus->icon }}"></i>
                {{ $jenisKursus->kategoriKursus->nama_kategori }}
            </span>
        @endif
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><strong>Deskripsi:</strong></div>
    <div class="col-md-9">{{ $jenisKursus->deskripsi ?? '-' }}</div>
</div>

<div class="row mb-3">
    <div class="col-md-3"><strong>Status:</strong></div>
    <div class="col-md-9">
        @if($jenisKursus->is_active)
            <span class="badge bg-success fs-6">Aktif</span>
        @else
            <span class="badge bg-secondary fs-6">Nonaktif</span>
        @endif
    </div>
</div>

<!-- HAPUS bagian Kuota dan Durasi -->

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Urutan Tampilan:</strong></div>
                        <div class="col-md-9">{{ $jenisKursus->urutan }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Dibuat Pada:</strong></div>
                        <div class="col-md-9">{{ $jenisKursus->created_at->format('d M Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Terakhir Diubah:</strong></div>
                        <div class="col-md-9">{{ $jenisKursus->updated_at->format('d M Y H:i') }}</div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('kategori.jenis-kursus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                        </a>
                        <button type="button" class="btn btn-warning text-white"
                            onclick="window.location='{{ route('kategori.jenis-kursus.index') }}'">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection