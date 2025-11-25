@extends('layouts.main')

@section('title', 'Edit Sertifikat')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Sertifikat: {{ $sertifikat->nomor_sertifikat }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sertifikat.update', $sertifikat->id) }}" method="POST" id="editSertifikatForm">
                            @csrf
                            @method('PUT')
                            
                            <!-- Info Sertifikat -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                <strong>Peserta:</strong> {{ $sertifikat->peserta->nama_lengkap }} | 
                                <strong>Kursus:</strong> {{ $sertifikat->kursus->judul }}
                            </div>

                            <!-- Tanggal & Tempat Terbit -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal_terbit" class="form-label">Tanggal Terbit <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal_terbit') is-invalid @enderror" 
                                        id="tanggal_terbit" name="tanggal_terbit" 
                                        value="{{ old('tanggal_terbit', $sertifikat->tanggal_terbit->format('Y-m-d')) }}" required>
                                    @error('tanggal_terbit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="tempat_terbit" class="form-label">Tempat Terbit <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tempat_terbit') is-invalid @enderror" 
                                        id="tempat_terbit" name="tempat_terbit" 
                                        value="{{ old('tempat_terbit', $sertifikat->tempat_terbit) }}" required>
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
                                                value="{{ old('nama_penandatangan1', $sertifikat->nama_penandatangan1) }}" required>
                                            @error('nama_penandatangan1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="jabatan_penandatangan1" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('jabatan_penandatangan1') is-invalid @enderror" 
                                                id="jabatan_penandatangan1" name="jabatan_penandatangan1" 
                                                value="{{ old('jabatan_penandatangan1', $sertifikat->jabatan_penandatangan1) }}" required>
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
                                                value="{{ old('nip_penandatangan1', $sertifikat->nip_penandatangan1) }}">
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
                                                value="{{ old('nama_penandatangan2', $sertifikat->nama_penandatangan2) }}">
                                            @error('nama_penandatangan2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="jabatan_penandatangan2" class="form-label">Jabatan</label>
                                            <input type="text" class="form-control @error('jabatan_penandatangan2') is-invalid @enderror" 
                                                id="jabatan_penandatangan2" name="jabatan_penandatangan2" 
                                                value="{{ old('jabatan_penandatangan2', $sertifikat->jabatan_penandatangan2) }}">
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
                                                value="{{ old('nip_penandatangan2', $sertifikat->nip_penandatangan2) }}">
                                            @error('nip_penandatangan2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Template, Status & Notes -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="template_name" class="form-label">Template <span class="text-danger">*</span></label>
                                    <select class="form-select @error('template_name') is-invalid @enderror" 
                                        id="template_name" name="template_name" required>
                                        
                                         <option value="default" {{ old('template_name', $sertifikat->template_name) == 'default' ? 'selected' : '' }}>Default Template</option>
                                        <option value="tema_2" {{ old('template_name', $sertifikat->template_name) == 'tema_2' ? 'selected' : '' }}>Tema 2</option>
                                        <option value="tema_3" {{ old('template_name', $sertifikat->template_name) == 'tema_3' ? 'selected' : '' }}>Tema 3</option>
                                    </select>
                                    @error('template_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" name="status" required>
                                        <option value="draft" {{ old('status', $sertifikat->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status', $sertifikat->status) == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="revoked" {{ old('status', $sertifikat->status) == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="notes" class="form-label">Catatan</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                        id="notes" name="notes" rows="3">{{ old('notes', $sertifikat->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Regenerate PDF Option -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="regenerate_pdf" 
                                        name="regenerate_pdf" value="1" {{ old('regenerate_pdf') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="regenerate_pdf">
                                        Regenerate PDF setelah update
                                    </label>
                                </div>
                                <small class="text-muted">PDF lama akan diganti dengan yang baru.</small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-save"></i> Update Sertifikat
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
    <script>
        $(document).ready(function() {
            $('#editSertifikatForm').on('submit', function() {
                $('#submitBtn').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Menyimpan...');
            });
        });
    </script>
@endpush