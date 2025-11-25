@extends('layouts.main')

@section('title', 'Tambah Sertifikat')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tambah Sertifikat Baru</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sertifikat.store') }}" method="POST" id="createSertifikatForm">
                            @csrf
                            
                            <!-- Peserta & Kursus -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="peserta_id" class="form-label">Peserta <span class="text-danger">*</span></label>
                                    <select class="form-select @error('peserta_id') is-invalid @enderror" 
                                        id="peserta_id" name="peserta_id" required>
                                        <option value="">Pilih Peserta</option>
                                        @foreach($pesertaList as $peserta)
                                            <option value="{{ $peserta->id }}" {{ old('peserta_id') == $peserta->id ? 'selected' : '' }}>
                                                {{ $peserta->nama_lengkap }} 
                                                @if($peserta->nip) - NIP: {{ $peserta->nip }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('peserta_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="kursus_id" class="form-label">Kursus <span class="text-danger">*</span></label>
                                    <select class="form-select @error('kursus_id') is-invalid @enderror" 
                                        id="kursus_id" name="kursus_id" required>
                                        <option value="">Pilih Kursus</option>
                                        @foreach($kursusList as $kursus)
                                            <option value="{{ $kursus->id }}" {{ old('kursus_id') == $kursus->id ? 'selected' : '' }}>
                                                {{ $kursus->judul }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kursus_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tanggal & Tempat Terbit -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal_terbit" class="form-label">Tanggal Terbit <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal_terbit') is-invalid @enderror" 
                                        id="tanggal_terbit" name="tanggal_terbit" 
                                        value="{{ old('tanggal_terbit', date('Y-m-d')) }}" required>
                                    @error('tanggal_terbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="tempat_terbit" class="form-label">Tempat Terbit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tempat_terbit') is-invalid @enderror" 
                                        id="tempat_terbit" name="tempat_terbit" 
                                        value="{{ old('tempat_terbit', 'Jakarta') }}" required>
                                    @error('tempat_terbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Penandatangan 1 -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Penandatangan Pertama</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nama_penandatangan1" class="form-label">Nama <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('nama_penandatangan1') is-invalid @enderror" 
                                                id="nama_penandatangan1" name="nama_penandatangan1" 
                                                value="{{ old('nama_penandatangan1', $defaultSignatories['penandatangan1']['nama'] ?? '') }}" required>
                                            @error('nama_penandatangan1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="jabatan_penandatangan1" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('jabatan_penandatangan1') is-invalid @enderror" 
                                                id="jabatan_penandatangan1" name="jabatan_penandatangan1" 
                                                value="{{ old('jabatan_penandatangan1', $defaultSignatories['penandatangan1']['jabatan'] ?? '') }}" required>
                                            @error('jabatan_penandatangan1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="nip_penandatangan1" class="form-label">NIP</label>
                                            <input type="text" class="form-control @error('nip_penandatangan1') is-invalid @enderror" 
                                                id="nip_penandatangan1" name="nip_penandatangan1" 
                                                value="{{ old('nip_penandatangan1', $defaultSignatories['penandatangan1']['nip'] ?? '') }}">
                                            @error('nip_penandatangan1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Penandatangan 2 -->
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Penandatangan Kedua (Opsional)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nama_penandatangan2" class="form-label">Nama</label>
                                            <input type="text" class="form-control @error('nama_penandatangan2') is-invalid @enderror" 
                                                id="nama_penandatangan2" name="nama_penandatangan2" 
                                                value="{{ old('nama_penandatangan2', $defaultSignatories['penandatangan2']['nama'] ?? '') }}">
                                            @error('nama_penandatangan2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="jabatan_penandatangan2" class="form-label">Jabatan</label>
                                            <input type="text" class="form-control @error('jabatan_penandatangan2') is-invalid @enderror" 
                                                id="jabatan_penandatangan2" name="jabatan_penandatangan2" 
                                                value="{{ old('jabatan_penandatangan2', $defaultSignatories['penandatangan2']['jabatan'] ?? '') }}">
                                            @error('jabatan_penandatangan2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="nip_penandatangan2" class="form-label">NIP</label>
                                            <input type="text" class="form-control @error('nip_penandatangan2') is-invalid @enderror" 
                                                id="nip_penandatangan2" name="nip_penandatangan2" 
                                                value="{{ old('nip_penandatangan2', $defaultSignatories['penandatangan2']['nip'] ?? '') }}">
                                            @error('nip_penandatangan2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Template & Notes -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="template_name" class="form-label">Template <span class="text-danger">*</span></label>
                                    <select class="form-select @error('template_name') is-invalid @enderror" 
                                        id="template_name" name="template_name" required>
                                        <option value="default" selected>Default Template</option>
                                        <option value="tema_2" >Tema 2</option>
                                        <option value="tema_3" >Tema 3</option>
                                    </select>
                                    @error('template_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="notes" class="form-label">Catatan</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                        id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Generate Options -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="generate_now" 
                                        name="generate_now" value="1" {{ old('generate_now') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="generate_now">
                                        Generate PDF sekarang
                                    </label>
                                </div>
                                <small class="text-muted">Jika tidak dicentang, PDF dapat di-generate nanti.</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-save"></i> Simpan Sertifikat
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#peserta_id, #kursus_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).find('option:first').text();
                }
            });

            // Form submission
            $('#createSertifikatForm').on('submit', function() {
                $('#submitBtn').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Menyimpan...');
            });
        });
    </script>
@endpush

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush