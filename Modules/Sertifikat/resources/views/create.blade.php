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
                            
                            <!-- STEP 1: Pilih Kursus Dulu -->
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Langkah 1:</strong> Pilih kursus terlebih dahulu untuk melihat peserta yang terdaftar.
                            </div>

                            <!-- Kursus -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="kursus_id" class="form-label">
                                        Kursus <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('kursus_id') is-invalid @enderror" 
                                        id="kursus_id" name="kursus_id" required>
                                        <option value="">-- Pilih Kursus --</option>
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

                            <!-- Peserta (akan di-load via AJAX) -->
                            <div id="peserta-section" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="peserta_id" class="form-label">
                                            Peserta <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('peserta_id') is-invalid @enderror" 
                                            id="peserta_id" name="peserta_id" required disabled>
                                            <option value="">-- Pilih kursus terlebih dahulu --</option>
                                        </select>
                                        @error('peserta_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="peserta-loading" class="text-muted mt-2" style="display: none;">
                                            <i class="spinner-border spinner-border-sm me-2"></i>
                                            Memuat data peserta...
                                        </div>
                                        <div id="peserta-count" class="text-muted mt-2" style="display: none;">
                                            <i class="bi bi-people me-1"></i>
                                            <span id="count-text"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Lainnya (akan muncul setelah pilih kursus & peserta) -->
                            <div id="form-details" style="display: none;">
                                <hr class="my-4">

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

                                <!-- Template & Notes -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="template_name" class="form-label">Template <span class="text-danger">*</span></label>
                                        <select class="form-select @error('template_name') is-invalid @enderror" 
                                            id="template_name" name="template_name" required>
                                            <option value="default" selected>Default Template</option>
                                            <option value="tema_2">Tema 2</option>
                                            <option value="tema_3">Tema 3</option>
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
                                            name="generate_now" value="1" checked>
                                        <label class="form-check-label" for="generate_now">
                                            Generate PDF sekarang
                                        </label>
                                    </div>
                                    <small class="text-muted">Jika tidak dicentang, PDF dapat di-generate nanti.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
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
   
   
    
    <script>
        // Wrap semua code dalam document ready
        document.addEventListener('DOMContentLoaded', function() {
            // Check if jQuery is loaded
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded!');
                alert('Error: jQuery tidak ditemukan. Silakan hubungi administrator.');
                return;
            }

            // Initialize Select2 for Kursus
            jQuery('#kursus_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Pilih Kursus --'
            });

            // Event when Kursus is selected
            jQuery('#kursus_id').on('change', function() {
                const kursusId = jQuery(this).val();
                
                if (kursusId) {
                    loadPesertaByKursus(kursusId);
                } else {
                    resetPesertaSection();
                }
            });

            // Function to load peserta based on kursus
            function loadPesertaByKursus(kursusId) {
                // Show loading
                jQuery('#peserta-loading').show();
                jQuery('#peserta-count').hide();
                jQuery('#peserta-section').show();
                jQuery('#form-details').hide();
                jQuery('#submitBtn').prop('disabled', true);
                
                // Reset peserta dropdown
                jQuery('#peserta_id').html('<option value="">Memuat...</option>').prop('disabled', true);

                // AJAX request
                jQuery.ajax({
                    url: `/sertifikat/get-peserta-by-kursus/${kursusId}`,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        jQuery('#peserta-loading').hide();
                        
                        if (response.success) {
                            const pesertas = response.data;
                            
                            if (pesertas.length > 0) {
                                // Populate peserta dropdown
                                let options = '<option value="">-- Pilih Peserta --</option>';
                                pesertas.forEach(function(peserta) {
                                    options += `<option value="${peserta.id}">
                                        ${peserta.nama_lengkap}
                                        ${peserta.nip ? ' - NIP: ' + peserta.nip : ''}
                                    </option>`;
                                });
                                
                                jQuery('#peserta_id').html(options).prop('disabled', false);
                                
                                // Initialize Select2 for Peserta
                                jQuery('#peserta_id').select2({
                                    theme: 'bootstrap-5',
                                    width: '100%',
                                    placeholder: '-- Pilih Peserta --'
                                });
                                
                                // Show count
                                jQuery('#count-text').text(`${pesertas.length} peserta terdaftar di kursus ini`);
                                jQuery('#peserta-count').show();
                                
                            } else {
                                // No peserta found
                                jQuery('#peserta_id').html('<option value="">Tidak ada peserta terdaftar</option>');
                                jQuery('#count-text').text('Tidak ada peserta yang terdaftar di kursus ini');
                                jQuery('#peserta-count').show();
                                
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Tidak Ada Peserta',
                                        text: 'Tidak ada peserta yang terdaftar di kursus ini.',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    alert('Tidak ada peserta yang terdaftar di kursus ini.');
                                }
                            }
                        }
                    },
                    error: function(xhr) {
                        jQuery('#peserta-loading').hide();
                        jQuery('#peserta_id').html('<option value="">Error memuat data</option>');
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal memuat data peserta. Silakan coba lagi.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Gagal memuat data peserta. Silakan coba lagi.');
                        }
                    }
                });
            }

            // Function to reset peserta section
            function resetPesertaSection() {
                jQuery('#peserta-section').hide();
                jQuery('#form-details').hide();
                jQuery('#peserta_id').html('<option value="">-- Pilih kursus terlebih dahulu --</option>').prop('disabled', true);
                jQuery('#peserta-count').hide();
                jQuery('#submitBtn').prop('disabled', true);
            }

            // Event when Peserta is selected
            jQuery('#peserta_id').on('change', function() {
                const pesertaId = jQuery(this).val();
                
                if (pesertaId) {
                    // Show form details
                    jQuery('#form-details').slideDown();
                    jQuery('#submitBtn').prop('disabled', false);
                } else {
                    // Hide form details
                    jQuery('#form-details').slideUp();
                    jQuery('#submitBtn').prop('disabled', true);
                }
            });

            // Form submission
            jQuery('#createSertifikatForm').on('submit', function(e) {
                const kursusId = jQuery('#kursus_id').val();
                const pesertaId = jQuery('#peserta_id').val();
                
                if (!kursusId || !pesertaId) {
                    e.preventDefault();
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Pilih kursus dan peserta terlebih dahulu!',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Pilih kursus dan peserta terlebih dahulu!');
                    }
                    return false;
                }
                
                jQuery('#submitBtn').prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Menyimpan...');
            });

            // Auto-load if old values exist (after validation error)
            @if(old('kursus_id'))
                loadPesertaByKursus('{{ old('kursus_id') }}');
                
                @if(old('peserta_id'))
                    setTimeout(function() {
                        jQuery('#peserta_id').val('{{ old('peserta_id') }}').trigger('change');
                    }, 1000);
                @endif
            @endif
        });
    </script>
@endpush