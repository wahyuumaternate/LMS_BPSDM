@extends('layouts.main')

@section('title', 'Buat Kursus')
@section('page-title', 'Buat Kursus Baru')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Form Kursus</h5>

            <form class="row g-3" action="{{ route('course.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="col-md-6 position-relative">
                    <label for="admin_instruktur_id" class="form-label">
                        Instruktur <span class="text-danger">*</span>
                    </label>
                    <select id="admin_instruktur_id" 
                            name="admin_instruktur_id"
                            class="form-select @error('admin_instruktur_id') is-invalid @enderror" 
                            required>
                        <option value="">Pilih Instruktur</option>
                        @foreach ($instruktur as $item)
                            <option value="{{ $item->id }}" {{ old('admin_instruktur_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_lengkap_dengan_gelar }}
                            </option>
                        @endforeach
                    </select>
                    @error('admin_instruktur_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="jenis_kursus_id" class="form-label">
                        Jenis Kursus <span class="text-danger">*</span>
                    </label>
                    <select id="jenis_kursus_id" 
                            name="jenis_kursus_id"
                            class="form-select @error('jenis_kursus_id') is-invalid @enderror" 
                            required>
                        <option value="">Pilih Jenis Kursus</option>
                        @foreach ($jenisKursus as $item)
                            <option value="{{ $item->id }}" {{ old('jenis_kursus_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->kategoriKursus->nama_kategori }} - {{ $item->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_kursus_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="level" class="form-label">
                        Level <span class="text-danger">*</span>
                    </label>
                    <select id="level" 
                            name="level" 
                            class="form-select @error('level') is-invalid @enderror" 
                            required>
                        <option value="">Pilih Level</option>
                        <option value="dasar" {{ old('level') == 'dasar' ? 'selected' : '' }}>Dasar</option>
                        <option value="menengah" {{ old('level') == 'menengah' ? 'selected' : '' }}>Menengah</option>
                        <option value="lanjut" {{ old('level') == 'lanjut' ? 'selected' : '' }}>Lanjut</option>
                    </select>
                    @error('level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="tipe" class="form-label">
                        Tipe <span class="text-danger">*</span>
                    </label>
                    <select id="tipe" 
                            name="tipe" 
                            class="form-select @error('tipe') is-invalid @enderror" 
                            required>
                        <option value="">Pilih Tipe</option>
                        <option value="daring" {{ old('tipe') == 'daring' ? 'selected' : '' }}>Daring</option>
                        <option value="luring" {{ old('tipe') == 'luring' ? 'selected' : '' }}>Luring</option>
                        <option value="hybrid" {{ old('tipe') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                    @error('tipe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="status" class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            class="form-select @error('status') is-invalid @enderror"
                            required>
                        <option value="">Pilih Status</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-8">
                    <label for="judul" class="form-label">
                        Judul Kursus <span class="text-danger">*</span>
                    </label>
                    <input type="text" 
                           name="judul" 
                           value="{{ old('judul') }}"
                           class="form-control @error('judul') is-invalid @enderror" 
                           id="judul" 
                           required>
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- MODIFIED: Kode Kursus Auto-Generate --}}
                <div class="col-md-4">
                    <label for="kode_kursus" class="form-label">Kode Kursus</label>
                    <input type="text" 
                           class="form-control" 
                           id="kode_kursus" 
                           value="(Auto-Generate: PEL-YYYY-XXXX)" 
                           readonly 
                           disabled
                           style="background-color: #e9ecef;">
                    <div class="form-text text-primary">
                        <i class="bi bi-info-circle"></i> Kode akan digenerate otomatis saat menyimpan
                    </div>
                </div>
                
                <div class="col-md-12">
                    <label for="deskripsi" class="col-form-label">
                        Deskripsi <span class="text-danger">*</span>
                    </label>
                    <div class="">
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                  id="deskripsi" 
                                  name="deskripsi" 
                                  rows="3"
                                  required>{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="tujuan_pembelajaran" class="col-form-label">Tujuan Pembelajaran</label>
                    <div class="">
                        <textarea class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" 
                                  id="tujuan_pembelajaran"
                                  name="tujuan_pembelajaran" 
                                  rows="3">{{ old('tujuan_pembelajaran') }}</textarea>
                        @error('tujuan_pembelajaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="sasaran_peserta" class="col-form-label">Sasaran Peserta</label>
                    <div class="">
                        <textarea class="form-control @error('sasaran_peserta') is-invalid @enderror" 
                                  id="sasaran_peserta"
                                  name="sasaran_peserta" 
                                  rows="3">{{ old('sasaran_peserta') }}</textarea>
                        @error('sasaran_peserta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="durasi_jam" class="col-form-label">Durasi (jam)</label>
                    <input type="number" 
                           min="0" 
                           class="form-control @error('durasi_jam') is-invalid @enderror"
                           id="durasi_jam" 
                           name="durasi_jam" 
                           value="{{ old('durasi_jam') ?? '0' }}">
                    @error('durasi_jam')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="kuota_peserta" class="col-form-label">Kuota Peserta</label>
                    <input type="number" 
                           min="0"
                           class="form-control @error('kuota_peserta') is-invalid @enderror" 
                           id="kuota_peserta"
                           name="kuota_peserta" 
                           value="{{ old('kuota_peserta') ?? '0' }}">
                    @error('kuota_peserta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="passing_grade" class="col-form-label">Passing Grade</label>
                    <input type="text" 
                           class="form-control @error('passing_grade') is-invalid @enderror"
                           id="passing_grade" 
                           name="passing_grade" 
                           value="{{ old('passing_grade') ?? '70' }}">
                    @error('passing_grade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="thumbnail" class="col-form-label">Thumbnail</label>
                    <input type="file" 
                           class="form-control @error('thumbnail') is-invalid @enderror" 
                           id="thumbnail"
                           name="thumbnail" 
                           accept="image/jpeg,image/jpg,image/png">
                    @error('thumbnail')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">JPG, JPEG, PNG. Max: 2MB</div>
                </div>
                
                <div class="col-md-3">
                    <label for="tanggal_buka_pendaftaran" class="col-form-label">Tanggal Buka Pendaftaran</label>
                    <input type="date" 
                           class="form-control @error('tanggal_buka_pendaftaran') is-invalid @enderror"
                           id="tanggal_buka_pendaftaran" 
                           name="tanggal_buka_pendaftaran"
                           value="{{ old('tanggal_buka_pendaftaran') }}">
                    @error('tanggal_buka_pendaftaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="tanggal_tutup_pendaftaran" class="col-form-label">Tanggal Tutup Pendaftaran</label>
                    <input type="date" 
                           class="form-control @error('tanggal_tutup_pendaftaran') is-invalid @enderror"
                           id="tanggal_tutup_pendaftaran" 
                           name="tanggal_tutup_pendaftaran"
                           value="{{ old('tanggal_tutup_pendaftaran') }}">
                    @error('tanggal_tutup_pendaftaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="tanggal_mulai_kursus" class="col-form-label">Tanggal Mulai Kursus</label>
                    <input type="date" 
                           class="form-control @error('tanggal_mulai_kursus') is-invalid @enderror"
                           id="tanggal_mulai_kursus" 
                           name="tanggal_mulai_kursus" 
                           value="{{ old('tanggal_mulai_kursus') }}">
                    @error('tanggal_mulai_kursus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="tanggal_selesai_kursus" class="col-form-label">Tanggal Selesai Kursus</label>
                    <input type="date" 
                           class="form-control @error('tanggal_selesai_kursus') is-invalid @enderror"
                           id="tanggal_selesai_kursus" 
                           name="tanggal_selesai_kursus"
                           value="{{ old('tanggal_selesai_kursus') }}">
                    @error('tanggal_selesai_kursus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Submit
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </button>
                    <a href="{{ route('course.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    
    {{-- <!-- TinyMCE (if available) -->
    @if(config('app.tinymce_enabled', false))
        <script src="{{ asset('assets/tinymce/tinymce.min.js') }}"></script>
    @endif --}}

    <script>
        window.addEventListener('load', function() {
          

            // Initialize Select2 for all select dropdowns
            jQuery('#admin_instruktur_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Instruktur',
                allowClear: true,
                width: '100%'
            });

            jQuery('#jenis_kursus_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Jenis Kursus',
                allowClear: true,
                width: '100%'
            });

            jQuery('#level').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Level',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1 // Disable search for small list
            });

            jQuery('#tipe').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Tipe',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1 // Disable search for small list
            });

            jQuery('#status').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Status',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1 // Disable search for small list
            });

          if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#deskripsi, #tujuan_pembelajaran, #sasaran_peserta',
        height: 200,
        menubar: false,
        license_key: 'gpl', // Add GPL license key to remove evaluation warning
        
        // FIX: Plugins harus array, bukan string dengan spasi
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 
            'preview', 'anchor', 'searchreplace', 'visualblocks', 
            'code', 'fullscreen', 'insertdatetime', 'media', 'table', 
            'paste', 'help', 'wordcount'
        ],
        
        toolbar: 'undo redo | formatselect | ' +
                 'bold italic backcolor | alignleft aligncenter ' +
                 'alignright alignjustify | bullist numlist outdent indent | ' +
                 'removeformat | help',
        
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px }',
        
        setup: function(editor) {
            editor.on('change', function() {
                editor.save();
            });
        }
    });
}


            // Form validation before submit
            jQuery('form').on('submit', function(e) {
                const instruktur = jQuery('#admin_instruktur_id').val();
                const jenisKursus = jQuery('#jenis_kursus_id').val();
                const judul = jQuery('#judul').val().trim();
                
                if (!instruktur || !jenisKursus || !judul) {
                    e.preventDefault();
                    alert('Mohon lengkapi field yang wajib diisi (*)');
                    return false;
                }
            });

        });
    </script>

    @if (session('error'))
        <script>
            window.addEventListener('load', function() {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: 'error',
                        title: "{{ session('error') }}"
                    });
                } else {
                    alert("{{ session('error') }}");
                }
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            window.addEventListener('load', function() {
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: 'success',
                        title: "{{ session('success') }}"
                    });
                } else {
                    alert("{{ session('success') }}");
                }
            });
        </script>
    @endif
@endpush