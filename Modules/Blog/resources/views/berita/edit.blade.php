@extends('layouts.main')

@section('title', 'Edit Berita')

@section('content')
<div class="pagetitle">
    <h1>Edit Berita</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('berita.index') }}">Berita</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Berita</h5>

                    <form action="{{ route('berita.update', $berita->id) }}" method="POST" enctype="multipart/form-data" id="beritaForm">
                        @csrf
                        @method('PUT')

                        <!-- Judul -->
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Berita <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                   id="judul" name="judul" value="{{ old('judul', $berita->judul) }}" required>
                            @error('judul')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Kategori -->
                        <div class="mb-3">
                            <label for="kategori_berita_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select @error('kategori_berita_id') is-invalid @enderror" 
                                    id="kategori_berita_id" name="kategori_berita_id" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($kategoris as $kat)
                                    <option value="{{ $kat->id }}" 
                                            {{ old('kategori_berita_id', $berita->kategori_berita_id) == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_berita_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ringkasan -->
                        <div class="mb-3">
                            <label for="ringkasan" class="form-label">Ringkasan</label>
                            <textarea class="form-control @error('ringkasan') is-invalid @enderror" 
                                      id="ringkasan" name="ringkasan" rows="3">{{ old('ringkasan', $berita->ringkasan) }}</textarea>
                            @error('ringkasan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Konten -->
                        <div class="mb-3">
                            <label for="konten" class="form-label">Konten Berita <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('konten') is-invalid @enderror" 
                                      id="konten" name="konten" rows="10" required>{{ old('konten', $berita->konten) }}</textarea>
                            @error('konten')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Image -->
                        @if($berita->gambar_utama)
                        <div class="mb-3">
                            <label class="form-label">Gambar Saat Ini</label>
                            <div>
                                <img src="{{ $berita->gambar_utama_url }}" alt="{{ $berita->judul }}" 
                                     class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>
                        @endif

                        <!-- Gambar Utama -->
                        <div class="mb-3">
                            <label for="gambar_utama" class="form-label">Gambar Utama Baru</label>
                            <input type="file" class="form-control @error('gambar_utama') is-invalid @enderror" 
                                   id="gambar_utama" name="gambar_utama" accept="image/*">
                            <div class="form-text">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah.</div>
                            @error('gambar_utama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                        </div>

                        <!-- Sumber Gambar -->
                        <div class="mb-3">
                            <label for="sumber_gambar" class="form-label">Sumber Gambar</label>
                            <input type="text" class="form-control @error('sumber_gambar') is-invalid @enderror" 
                                   id="sumber_gambar" name="sumber_gambar" 
                                   value="{{ old('sumber_gambar', $berita->sumber_gambar) }}">
                            @error('sumber_gambar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('berita.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <div>
                                <button type="submit" name="status" value="draft" class="btn btn-outline-primary">
                                    <i class="bi bi-file-earmark"></i> Simpan sebagai Draft
                                </button>
                                <button type="submit" name="status" value="published" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Update & Publish
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- SEO Settings -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Pengaturan SEO</h5>

                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                               value="{{ old('meta_title', $berita->meta_title) }}" form="beritaForm">
                    </div>

                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                  rows="3" form="beritaForm">{{ old('meta_description', $berita->meta_description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                               value="{{ old('meta_keywords', $berita->meta_keywords) }}" form="beritaForm">
                    </div>
                </div>
            </div>

            <!-- Publishing Options -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Opsi Publikasi</h5>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_featured" 
                                   name="is_featured" value="1" 
                                   {{ old('is_featured', $berita->is_featured) ? 'checked' : '' }} form="beritaForm">
                            <label class="form-check-label" for="is_featured">
                                <i class="bi bi-star"></i> Featured
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="published_at" class="form-label">Tanggal Publish</label>
                        <input type="datetime-local" class="form-control" id="published_at" 
                               name="published_at" 
                               value="{{ old('published_at', $berita->published_at ? $berita->published_at->format('Y-m-d\TH:i') : '') }}" 
                               form="beritaForm">
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Statistik</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-eye"></i> Views: <strong>{{ number_format($berita->view_count) }}</strong></li>
                        <li><i class="bi bi-calendar"></i> Dibuat: {{ $berita->formatted_created_at }}</li>
                        <li><i class="bi bi-clock-history"></i> Update: {{ $berita->updated_at->diffForHumans() }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')

<script>
jQuery(document).ready(function() {
    jQuery('#kategori_berita_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Pilih Kategori',
        allowClear: false,
        width: '100%'
    });

    jQuery('#gambar_utama').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                jQuery('#preview').attr('src', e.target.result);
                jQuery('#imagePreview').show();
            };
            reader.readAsDataURL(file);
        }
    });
});

if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#konten',
        height: 400,
        menubar: false,
        plugins: 'lists link code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code | help',
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
}
</script>
@endpush