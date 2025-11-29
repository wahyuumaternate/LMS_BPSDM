@extends('layouts.main')

@section('title', 'Daftar Sertifikat')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Sertifikat</h5>
                        <div>
                            <a href="{{ route('sertifikat.bulk.generate-form') }}" class="btn btn-success">
                                <i class="bi bi-file-earmark-plus"></i> Generate Sertifikat
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filter & Search -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Kursus</label>
                                <select class="form-select" id="filterKursus">
                                    <option value="">Semua Kursus</option>
                                    @foreach($kursusList as $kursus)
                                        <option value="{{ $kursus->id }}" {{ request('kursus_id') == $kursus->id ? 'selected' : '' }}>
                                            {{ $kursus->judul }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>Revoked</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Pencarian</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" 
                                        placeholder="Cari nomor sertifikat atau nama peserta..."
                                        value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                    @if(request('search'))
                                        <button class="btn btn-outline-danger" type="button" id="clearSearch">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        <div id="alertMessage" style="display: none;"></div>

                        <!-- Sertifikat Table -->
                        <div id="sertifikatTableContainer">
                            @include('sertifikat::partials.sertifikat_table')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Show Sertifikat Modal -->
    <div class="modal fade" id="showSertifikatModal" tabindex="-1" aria-labelledby="showSertifikatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showSertifikatModalLabel">Detail Sertifikat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nomor Sertifikat</th>
                                    <td id="show_nomor_sertifikat"></td>
                                </tr>
                                <tr>
                                    <th>Peserta</th>
                                    <td id="show_peserta"></td>
                                </tr>
                                <tr>
                                    <th>Kursus</th>
                                    <td id="show_kursus"></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Terbit</th>
                                    <td id="show_tanggal_terbit"></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="show_status"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Penandatangan 1</th>
                                    <td id="show_penandatangan1"></td>
                                </tr>
                                <tr>
                                    <th>Jabatan</th>
                                    <td id="show_jabatan1"></td>
                                </tr>
                                <tr>
                                    <th>Email Terkirim</th>
                                    <td id="show_email_sent"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3" id="show_pdf_section">
                        <!-- PDF preview will be inserted here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="#" id="show_download_btn" class="btn btn-success" target="_blank">
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn btn-warning text-white" id="show_edit_btn">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/select2/select2-bootstrap-5-theme.min.css') }}">
@endpush

@push('scripts')
    <!-- Load jQuery if not already loaded -->
    <script>
        if (typeof jQuery === 'undefined') {
            document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
        }
    </script>

    <!-- Select2 JS -->
    <script src="{{ asset('assets/select2/select2.min.js') }}"></script>

    <script type="text/javascript">
        window.addEventListener('load', function() {
            // Check jQuery
            if (typeof jQuery === 'undefined') {
                console.error('jQuery not loaded!');
                return;
            }

            console.log('✓ Sertifikat index initialized');

            // Initialize Select2 for filters
            jQuery('#filterKursus').select2({
                theme: 'bootstrap-5',
                placeholder: 'Semua Kursus',
                allowClear: true,
                width: '100%'
            });

            jQuery('#filterStatus').select2({
                theme: 'bootstrap-5',
                placeholder: 'Semua Status',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1 // No search for small list
            });

            // Filter change handlers
            jQuery('#filterKursus, #filterStatus').on('change', function() {
                loadSertifikatTable();
            });

            // Search functionality
            jQuery('#searchButton').on('click', function() {
                loadSertifikatTable();
            });

            jQuery('#searchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    loadSertifikatTable();
                }
            });

            jQuery('#clearSearch').on('click', function() {
                jQuery('#searchInput').val('');
                loadSertifikatTable();
            });

            // Show Sertifikat details
            jQuery(document).on('click', '.show-sertifikat-btn', function() {
                const sertifikatId = jQuery(this).data('id');
                
                jQuery.ajax({
                    url: `/sertifikat/${sertifikatId}`,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            jQuery('#show_nomor_sertifikat').text(data.nomor_sertifikat);
                            jQuery('#show_peserta').text(data.peserta.nama_lengkap);
                            jQuery('#show_kursus').text(data.kursus.judul);
                            jQuery('#show_tanggal_terbit').text(data.formatted_tanggal);
                            
                            // Status badge
                            let statusBadge = '';
                            if (data.status === 'published') {
                                statusBadge = '<span class="badge bg-success">Published</span>';
                            } else if (data.status === 'draft') {
                                statusBadge = '<span class="badge bg-warning">Draft</span>';
                            } else {
                                statusBadge = '<span class="badge bg-danger">Revoked</span>';
                            }
                            jQuery('#show_status').html(statusBadge);
                            
                            jQuery('#show_penandatangan1').text(data.nama_penandatangan1);
                            jQuery('#show_jabatan1').text(data.jabatan_penandatangan1);
                            jQuery('#show_email_sent').html(data.is_sent_email 
                                ? '<span class="badge bg-success">Ya</span>' 
                                : '<span class="badge bg-secondary">Belum</span>');
                            
                            // PDF section
                            if (data.file_url) {
                                jQuery('#show_pdf_section').html(`
                                    <iframe src="${data.file_url}" width="100%" height="400px" class="border rounded"></iframe>
                                `);
                                jQuery('#show_download_btn').attr('href', data.download_url).show();
                            } else {
                                jQuery('#show_pdf_section').html('<p class="text-muted">PDF belum di-generate</p>');
                                jQuery('#show_download_btn').hide();
                            }
                            
                            jQuery('#show_edit_btn').data('id', data.id);
                            
                            // Show modal using Bootstrap 5
                            const modal = new bootstrap.Modal(document.getElementById('showSertifikatModal'));
                            modal.show();
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal memuat data sertifikat.');
                    }
                });
            });

            // Edit from show modal
            jQuery('#show_edit_btn').on('click', function() {
                const sertifikatId = jQuery(this).data('id');
                const modal = bootstrap.Modal.getInstance(document.getElementById('showSertifikatModal'));
                modal.hide();
                window.location.href = `/sertifikat/${sertifikatId}/edit`;
            });

            // Delete Sertifikat
            jQuery(document).on('click', '.delete-sertifikat-btn', function() {
                const sertifikatId = jQuery(this).data('id');
                const sertifikatNomor = jQuery(this).data('nomor');
                
                jQuery('#delete_sertifikat_id').val(sertifikatId);
                jQuery('#delete_sertifikat_nomor').text(sertifikatNomor);
                
                const modal = new bootstrap.Modal(document.getElementById('deleteSertifikatModal'));
                modal.show();
            });

            jQuery('#confirmDeleteBtn').on('click', function() {
                const sertifikatId = jQuery('#delete_sertifikat_id').val();
                const btn = jQuery(this);
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menghapus...');
                
                jQuery.ajax({
                    url: `/sertifikat/${sertifikatId}`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "DELETE"
                    },
                    success: function(response) {
                        if (response.success) {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteSertifikatModal'));
                            modal.hide();
                            showAlert('success', response.message);
                            loadSertifikatTable();
                        }
                        btn.prop('disabled', false).html('Hapus');
                    },
                    error: function(xhr) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteSertifikatModal'));
                        modal.hide();
                        showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
                        btn.prop('disabled', false).html('Hapus');
                    }
                });
            });

            // Generate PDF
            jQuery(document).on('click', '.generate-pdf-btn', function() {
                const sertifikatId = jQuery(this).data('id');
                const btn = jQuery(this);
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                
                jQuery.ajax({
                    url: `/sertifikat/${sertifikatId}/generate-pdf`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            loadSertifikatTable();
                        }
                        btn.prop('disabled', false).html('<i class="bi bi-file-pdf"></i>');
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal generate PDF.');
                        btn.prop('disabled', false).html('<i class="bi bi-file-pdf"></i>');
                    }
                });
            });

            // Send Email
            jQuery(document).on('click', '.send-email-btn', function() {
                const sertifikatId = jQuery(this).data('id');
                const btn = jQuery(this);
                
                if (!confirm('Kirim sertifikat ke email peserta?')) return;
                
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                
                jQuery.ajax({
                    url: `/sertifikat/${sertifikatId}/send-email`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            loadSertifikatTable();
                        }
                        btn.prop('disabled', false).html('<i class="bi bi-envelope"></i>');
                    },
                    error: function(xhr) {
                        showAlert('danger', xhr.responseJSON?.message || 'Gagal mengirim email.');
                        btn.prop('disabled', false).html('<i class="bi bi-envelope"></i>');
                    }
                });
            });

            // Helper Functions
            function loadSertifikatTable() {
                const search = jQuery('#searchInput').val();
                const kursusId = jQuery('#filterKursus').val();
                const status = jQuery('#filterStatus').val();
                
                jQuery.ajax({
                    url: "{{ route('sertifikat.index') }}",
                    type: "GET",
                    data: {
                        search: search,
                        kursus_id: kursusId,
                        status: status
                    },
                    success: function(response) {
                        jQuery('#sertifikatTableContainer').html(response);
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal memuat tabel sertifikat.');
                    }
                });
            }

            function showAlert(type, message) {
                const alertClass = `alert-${type}`;
                const alertHTML = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                
                jQuery('#alertMessage').html(alertHTML).show();
                
                setTimeout(function() {
                    jQuery('#alertMessage').fadeOut();
                }, 5000);
            }

            console.log('✓ Select2 initialized on filters');
        });
    </script>
@endpush