@extends('layouts.main')

@section('title', 'Tambah Materi')
@section('page-title', 'Tambah Materi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Form Tambah Materi</h5>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('materi.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-3">
                        <label for="modul_id" class="col-sm-2 col-form-label">Modul</label>
                        <div class="col-sm-10">
                            <select class="form-select @error('modul_id') is-invalid @enderror" id="modul_id" name="modul_id" required>
                                <option value="">-- Pilih Modul --</option>
                                @foreach ($modules as $modul)
                                    <option value="{{ $modul->id }}" {{ (old('modul_id') == $modul->id || $selected_module == $modul->id) ? 'selected' : '' }}>
                                        {{ $modul->nama_modul }}
                                    </option>
                                @endforeach
                            </select>
                            @error('modul_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="judul_materi" class="col-sm-2 col-form-label">Judul Materi</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control @error('judul_materi') is-invalid @enderror" id="judul_materi" name="judul_materi" value="{{ old('judul_materi') }}" required>
                            @error('judul_materi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="tipe_konten" class="col-sm-2 col-form-label">Tipe Konten</label>
                        <div class="col-sm-10">
                            <select class="form-select @error('tipe_konten') is-invalid @enderror" id="tipe_konten" name="tipe_konten" required>
                                <option value="">-- Pilih Tipe Konten --</option>
                                <option value="pdf" {{ old('tipe_konten') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="doc" {{ old('tipe_konten') == 'doc' ? 'selected' : '' }}>Document</option>
                                <option value="video" {{ old('tipe_konten') == 'video' ? 'selected' : '' }}>Video</option>
                                <option value="audio" {{ old('tipe_konten') == 'audio' ? 'selected' : '' }}>Audio</option>
                                <option value="gambar" {{ old('tipe_konten') == 'gambar' ? 'selected' : '' }}>Gambar</option>
                                <option value="link" {{ old('tipe_konten') == 'link' ? 'selected' : '' }}>Link</option>
                                <option value="scorm" {{ old('tipe_konten') == 'scorm' ? 'selected' : '' }}>SCORM</option>
                            </select>
                            @error('tipe_konten')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3" id="file-input-container">
                        <label for="file" class="col-sm-2 col-form-label">File</label>
                        <div class="col-sm-10">
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            <small class="form-text text-muted">Ukuran maksimal file: 100MB</small>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3" id="file-url-container" style="display: none;">
                        <label for="file_url" class="col-sm-2 col-form-label">URL</label>
                        <div class="col-sm-10">
                            <input type="url" class="form-control @error('file_url') is-invalid @enderror" id="file_url" name="file_url" value="{{ old('file_url') }}">
                            @error('file_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="deskripsi" class="col-sm-2 col-form-label">Deskripsi</label>
                        <div class="col-sm-10">
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="durasi_menit" class="col-sm-2 col-form-label">Durasi (menit)</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" id="durasi_menit" name="durasi_menit" min="0" value="{{ old('durasi_menit') }}">
                            @error('durasi_menit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="urutan" class="col-sm-2 col-form-label">Urutan</label>
                        <div class="col-sm-10">
                            <input type="number" class="form-control @error('urutan') is-invalid @enderror" id="urutan" name="urutan" min="0" value="{{ old('urutan') }}">
                            <small class="form-text text-muted">Biarkan kosong untuk menempatkan di urutan terakhir</small>
                            @error('urutan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-10 offset-sm-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_wajib" name="is_wajib" {{ old('is_wajib') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_wajib">
                                    Materi Wajib
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-10 offset-sm-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    Publikasikan Sekarang
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-10 offset-sm-2">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('materi.index', request()->query()) }}" class="btn btn-secondary">Kembali</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipeKontenSelect = document.getElementById('tipe_konten');
        const fileInputContainer = document.getElementById('file-input-container');
        const fileUrlContainer = document.getElementById('file-url-container');
        const fileInput = document.getElementById('file');
        const fileUrlInput = document.getElementById('file_url');
        
        function updateFormFields() {
            if (tipeKontenSelect.value === 'link') {
                fileInputContainer.style.display = 'none';
                fileUrlContainer.style.display = 'flex';
                fileInput.removeAttribute('required');
                fileUrlInput.setAttribute('required', 'required');
            } else {
                fileInputContainer.style.display = 'flex';
                fileUrlContainer.style.display = 'none';
                fileUrlInput.removeAttribute('required');
                fileInput.setAttribute('required', 'required');
            }
        }
        
        // Initialize form state
        updateFormFields();
        
        // Add change event listener
        tipeKontenSelect.addEventListener('change', updateFormFields);
    });
</script>
@endpush