@extends('layouts.main')

@section('title', 'Generate Sertifikat Massal')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Generate Sertifikat Massal</h5>
                            <a href="{{ route('sertifikat.index') }}" class="btn btn-sm btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Info Kursus -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-2"><i class="bi bi-journal-text me-2"></i>{{ $kursus->judul }}</h6>
                                    <small class="text-muted">
                                        @if($kursus->tanggal_mulai_kursus)
                                            Mulai: {{ \Carbon\Carbon::parse($kursus->tanggal_mulai_kursus)->format('d M Y') }}
                                        @endif
                                    </small>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="badge bg-primary fs-6 px-3 py-2">
                                        <i class="bi bi-people me-1"></i>
                                        {{ $availablePesertas->count() }} Peserta Tersedia
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(session('errors') && is_array(session('errors')) && count(session('errors')) > 0)
                            <div class="alert alert-warning alert-dismissible fade show">
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <strong><i class="bi bi-exclamation-triangle me-2"></i>Beberapa error terjadi:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($availablePesertas->isEmpty())
                            <div class="alert alert-warning">
                                <div class="text-center py-4">
                                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #ffc107;"></i>
                                    <h5 class="mt-3">Tidak Ada Peserta Tersedia</h5>
                                    <p class="text-muted">
                                        Semua peserta sudah memiliki sertifikat atau tidak ada peserta terdaftar di kursus ini.
                                    </p>
                                    <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary mt-2">
                                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar Sertifikat
                                    </a>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('sertifikat.bulk.generate') }}" method="POST" id="bulkGenerateForm">
                                @csrf
                                <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                                
                                <!-- Pilih Peserta -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <label class="form-label mb-0 fw-bold">
                                                <i class="bi bi-people me-1"></i>Pilih Peserta 
                                                <span class="text-danger">*</span>
                                            </label>
                                            <br>
                                            <small class="text-muted">
                                                <span id="selectedCount">0</span> dari {{ $availablePesertas->count() }} peserta dipilih
                                            </small>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                                <i class="bi bi-check-all"></i> Pilih Semua
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                                <i class="bi bi-x"></i> Batal Pilih
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50" class="text-center">
                                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                                    </th>
                                                    <th width="50" class="text-center">No</th>
                                                    <th>Nama Peserta</th>
                                                    <th width="180">NIP</th>
                                                    <th width="250">Email</th>
                                                    <th width="150" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($availablePesertas as $index => $peserta)
                                                    <tr class="peserta-row">
                                                        <td class="text-center">
                                                            <input type="checkbox" 
                                                                class="form-check-input peserta-checkbox" 
                                                                name="peserta_ids[]" 
                                                                value="{{ $peserta->id }}"
                                                                id="peserta-{{ $peserta->id }}">
                                                        </td>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>
                                                            <label for="peserta-{{ $peserta->id }}" class="mb-0" style="cursor: pointer;">
                                                                {{ $peserta->nama_lengkap }}
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <code>{{ $peserta->nip ?? '-' }}</code>
                                                        </td>
                                                        <td>{{ $peserta->email ?? '-' }}</td>
                                                        <td class="text-center">
                                                            <span class="badge bg-success">
                                                                <i class="bi bi-check-circle me-1"></i>Siap
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @error('peserta_ids')
                                        <div class="alert alert-danger mt-2">
                                            <i class="bi bi-exclamation-circle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <hr class="my-4">

                                <!-- Tanggal & Tempat Terbit -->
                                <h6 class="mb-3"><i class="bi bi-calendar-event me-2"></i>Informasi Penerbitan</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label for="tanggal_terbit" class="form-label">
                                            Tanggal Terbit <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                            class="form-control @error('tanggal_terbit') is-invalid @enderror" 
                                            id="tanggal_terbit" 
                                            name="tanggal_terbit" 
                                            value="{{ old('tanggal_terbit', date('Y-m-d')) }}" 
                                            required>
                                        @error('tanggal_terbit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="tempat_terbit" class="form-label">
                                            Tempat Terbit <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                            class="form-control @error('tempat_terbit') is-invalid @enderror" 
                                            id="tempat_terbit" 
                                            name="tempat_terbit" 
                                            value="{{ old('tempat_terbit', 'Jakarta') }}" 
                                            required>
                                        @error('tempat_terbit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Penandatangan 1 -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-person-check me-2"></i>Penandatangan Pertama
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nama_penandatangan1" class="form-label">
                                                    Nama <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                    class="form-control @error('nama_penandatangan1') is-invalid @enderror" 
                                                    id="nama_penandatangan1" 
                                                    name="nama_penandatangan1" 
                                                    value="{{ old('nama_penandatangan1', $defaultSignatories['penandatangan1']['nama'] ?? '') }}" 
                                                    required>
                                                @error('nama_penandatangan1')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="jabatan_penandatangan1" class="form-label">
                                                    Jabatan <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                    class="form-control @error('jabatan_penandatangan1') is-invalid @enderror" 
                                                    id="jabatan_penandatangan1" 
                                                    name="jabatan_penandatangan1" 
                                                    value="{{ old('jabatan_penandatangan1', $defaultSignatories['penandatangan1']['jabatan'] ?? '') }}" 
                                                    required>
                                                @error('jabatan_penandatangan1')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="nip_penandatangan1" class="form-label">NIP</label>
                                                <input type="text" 
                                                    class="form-control @error('nip_penandatangan1') is-invalid @enderror" 
                                                    id="nip_penandatangan1" 
                                                    name="nip_penandatangan1" 
                                                    value="{{ old('nip_penandatangan1', $defaultSignatories['penandatangan1']['nip'] ?? '') }}">
                                                @error('nip_penandatangan1')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- <!-- Penandatangan 2 (Optional) -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-person me-2"></i>Penandatangan Kedua 
                                            <small class="text-muted">(Opsional)</small>
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nama_penandatangan2" class="form-label">Nama</label>
                                                <input type="text" 
                                                    class="form-control @error('nama_penandatangan2') is-invalid @enderror" 
                                                    id="nama_penandatangan2" 
                                                    name="nama_penandatangan2" 
                                                    value="{{ old('nama_penandatangan2', $defaultSignatories['penandatangan2']['nama'] ?? '') }}">
                                                @error('nama_penandatangan2')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="jabatan_penandatangan2" class="form-label">Jabatan</label>
                                                <input type="text" 
                                                    class="form-control @error('jabatan_penandatangan2') is-invalid @enderror" 
                                                    id="jabatan_penandatangan2" 
                                                    name="jabatan_penandatangan2" 
                                                    value="{{ old('jabatan_penandatangan2', $defaultSignatories['penandatangan2']['jabatan'] ?? '') }}">
                                                @error('jabatan_penandatangan2')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="nip_penandatangan2" class="form-label">NIP</label>
                                                <input type="text" 
                                                    class="form-control @error('nip_penandatangan2') is-invalid @enderror" 
                                                    id="nip_penandatangan2" 
                                                    name="nip_penandatangan2" 
                                                    value="{{ old('nip_penandatangan2', $defaultSignatories['penandatangan2']['nip'] ?? '') }}">
                                                @error('nip_penandatangan2')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                        <i class="bi bi-file-earmark-plus me-2"></i>
                                        Generate <span id="selectedCountBtn">0</span> Sertifikat
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .peserta-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .peserta-row:hover {
            background-color: #f8f9fa;
        }
        .peserta-row.selected {
            background-color: #e7f3ff;
        }
    </style>
@endpush

@push('scripts')
   
    <script>
        window.addEventListener('load', function() {
            // Check if jQuery loaded
            if (typeof jQuery === 'undefined') {
                console.error('jQuery failed to load!');
                alert('Error loading page. Please refresh.');
                return;
            }

            console.log('✓ Bulk generate form initialized');

            // Check all master checkbox
            jQuery('#checkAll').on('change', function() {
                const isChecked = jQuery(this).prop('checked');
                jQuery('.peserta-checkbox').prop('checked', isChecked);
                updateSelectedCount();
                updateRowHighlight();
            });

            // Individual checkbox change
            jQuery('.peserta-checkbox').on('change', function() {
                updateCheckAllState();
                updateSelectedCount();
                updateRowHighlight();
            });

            // Click on row to toggle checkbox
            jQuery('.peserta-row').on('click', function(e) {
                // Jangan toggle jika klik langsung di checkbox
                if (jQuery(e.target).is('input[type="checkbox"]') || jQuery(e.target).is('label')) {
                    return;
                }
                
                const checkbox = jQuery(this).find('.peserta-checkbox');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            });

            // Select all button
            jQuery('#selectAll').on('click', function() {
                jQuery('.peserta-checkbox').prop('checked', true);
                jQuery('#checkAll').prop('checked', true);
                updateSelectedCount();
                updateRowHighlight();
            });

            // Deselect all button
            jQuery('#deselectAll').on('click', function() {
                jQuery('.peserta-checkbox').prop('checked', false);
                jQuery('#checkAll').prop('checked', false);
                updateSelectedCount();
                updateRowHighlight();
            });

            // Form submission
            jQuery('#bulkGenerateForm').on('submit', function(e) {
                const selectedCount = jQuery('.peserta-checkbox:checked').length;
                
                if (selectedCount === 0) {
                    e.preventDefault();
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: 'Pilih minimal satu peserta untuk di-generate sertifikat!',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Pilih minimal satu peserta!');
                    }
                    return false;
                }
                
                e.preventDefault();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Konfirmasi Generate',
                        html: `Anda akan generate <strong>${selectedCount} sertifikat</strong>.<br>Proses ini mungkin memakan waktu.<br><br>Lanjutkan?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Generate!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitForm();
                        }
                    });
                } else {
                    if (confirm(`Generate ${selectedCount} sertifikat?\nProses ini mungkin memakan waktu.`)) {
                        submitForm();
                    }
                }
            });

            function submitForm() {
                jQuery('#submitBtn').prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');
                
                // Show loading
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Sedang Generate Sertifikat',
                        html: 'Mohon tunggu, jangan tutup halaman ini...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
                
                jQuery('#bulkGenerateForm')[0].submit();
            }

            function updateCheckAllState() {
                const total = jQuery('.peserta-checkbox').length;
                const checked = jQuery('.peserta-checkbox:checked').length;
                jQuery('#checkAll').prop('checked', total === checked && total > 0);
            }

            function updateSelectedCount() {
                const count = jQuery('.peserta-checkbox:checked').length;
                jQuery('#selectedCount').text(count);
                jQuery('#selectedCountBtn').text(count);
                
                // Enable/disable submit button
                if (count > 0) {
                    jQuery('#submitBtn').prop('disabled', false);
                } else {
                    jQuery('#submitBtn').prop('disabled', true);
                }
            }

            function updateRowHighlight() {
                jQuery('.peserta-row').each(function() {
                    const checkbox = jQuery(this).find('.peserta-checkbox');
                    if (checkbox.prop('checked')) {
                        jQuery(this).addClass('selected');
                    } else {
                        jQuery(this).removeClass('selected');
                    }
                });
            }

            // Initial state
            updateSelectedCount();
            updateRowHighlight();
        });
    </script>
@endpush