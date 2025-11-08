@extends('layouts.main')

@section('title', 'Buat Tugas Baru')
@section('page-title', 'Buat Tugas Baru')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tugas Baru</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tugas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row mb-3">
                            <label for="modul_id" class="col-sm-2 col-form-label">Modul</label>
                            <div class="col-sm-10">
                                <select class="form-select @error('modul_id') is-invalid @enderror" id="modul_id"
                                    name="modul_id" required>
                                    <option value="">-- Pilih Modul --</option>
                                    @foreach ($moduls as $modul)
                                        <option value="{{ $modul->id }}"
                                            {{ old('modul_id') == $modul->id ? 'selected' : '' }}>
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
                            <label for="judul" class="col-sm-2 col-form-label">Judul Tugas</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                    id="judul" name="judul" value="{{ old('judul') }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="deskripsi" class="col-sm-2 col-form-label">Deskripsi</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4"
                                    required>{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Jelaskan tentang tugas dan apa yang harus dikerjakan peserta.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="petunjuk" class="col-sm-2 col-form-label">Petunjuk</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('petunjuk') is-invalid @enderror" id="petunjuk" name="petunjuk" rows="3">{{ old('petunjuk') }}</textarea>
                                @error('petunjuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Berikan petunjuk teknis tentang format pengumpulan, ketentuan, dsb.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="file_tugas" class="col-sm-2 col-form-label">File Tugas</label>
                            <div class="col-sm-10">
                                <input type="file" class="form-control @error('file_tugas') is-invalid @enderror"
                                    id="file_tugas" name="file_tugas">
                                @error('file_tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">File pendukung tugas (PDF, DOC, DOCX, maks 10MB). Opsional.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_mulai" class="col-sm-2 col-form-label">Tanggal Mulai</label>
                            <div class="col-sm-10">
                                <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                    id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}">
                                @error('tanggal_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tanggal_deadline" class="col-sm-2 col-form-label">Deadline</label>
                            <div class="col-sm-10">
                                <input type="date" class="form-control @error('tanggal_deadline') is-invalid @enderror"
                                    id="tanggal_deadline" name="tanggal_deadline" value="{{ old('tanggal_deadline') }}">
                                @error('tanggal_deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Tanggal terakhir pengumpulan tugas.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nilai_maksimal" class="col-sm-2 col-form-label">Nilai Maksimal</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control @error('nilai_maksimal') is-invalid @enderror"
                                    id="nilai_maksimal" name="nilai_maksimal" min="1" max="100"
                                    value="{{ old('nilai_maksimal', 100) }}">
                                @error('nilai_maksimal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nilai maksimal yang dapat diperoleh (1-100).</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="bobot_nilai" class="col-sm-2 col-form-label">Bobot Nilai</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control @error('bobot_nilai') is-invalid @enderror"
                                    id="bobot_nilai" name="bobot_nilai" min="1"
                                    value="{{ old('bobot_nilai', 1) }}">
                                @error('bobot_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Bobot tugas ini terhadap nilai akhir.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_published"
                                        name="is_published" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publikasikan Sekarang
                                    </label>
                                </div>
                                <div class="form-text">Jika tidak dicentang, tugas akan disimpan sebagai draft.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('tugas.index') }}" class="btn btn-secondary">Batal</a>
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
            // Validate deadline is after start date
            const tanggalMulai = document.getElementById('tanggal_mulai');
            const tanggalDeadline = document.getElementById('tanggal_deadline');

            tanggalDeadline.addEventListener('change', function() {
                if (tanggalMulai.value && tanggalDeadline.value) {
                    if (new Date(tanggalDeadline.value) < new Date(tanggalMulai.value)) {
                        alert('Deadline harus setelah tanggal mulai');
                        tanggalDeadline.value = '';
                    }
                }
            });

            tanggalMulai.addEventListener('change', function() {
                if (tanggalMulai.value && tanggalDeadline.value) {
                    if (new Date(tanggalDeadline.value) < new Date(tanggalMulai.value)) {
                        alert('Deadline harus setelah tanggal mulai');
                        tanggalDeadline.value = '';
                    }
                }
            });

            // Initialize WYSIWYG editor if available
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#deskripsi, #petunjuk',
                    plugins: 'lists link image code table',
                    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link'
                });
            }

            // Select2 for better dropdowns (if available)
            if (typeof $.fn.select2 !== 'undefined') {
                $('#modul_id').select2({
                    placeholder: "-- Pilih Modul --",
                    allowClear: true
                });
            }
        });
    </script>
@endpush
