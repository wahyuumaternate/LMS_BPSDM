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
                                <i class="bi bi-file-earmark-plus"></i> Generate Massal
                            </a>
                            <a href="{{ route('sertifikat.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Sertifikat
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
                                {{-- <tr>
                                    <th>Penandatangan 2</th>
                                    <td id="show_penandatangan2"></td>
                                </tr>
                                <tr>
                                    <th>Jabatan</th>
                                    <td id="show_jabatan2"></td>
                                </tr> --}}
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteSertifikatModal" tabindex="-1" aria-labelledby="deleteSertifikatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSertifikatModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus sertifikat <strong id="delete_sertifikat_nomor"></strong>?</p>
                    <p class="text-danger"><small>File PDF dan QR Code juga akan dihapus.</small></p>
                    <input type="hidden" id="delete_sertifikat_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Filter change handlers
            $('#filterKursus, #filterStatus').on('change', function() {
                loadSertifikatTable();
            });

            // Search functionality
            $('#searchButton').on('click', function() {
                loadSertifikatTable();
            });

            $('#searchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    loadSertifikatTable();
                }
            });

            $('#clearSearch').on('click', function() {
                $('#searchInput').val('');
                loadSertifikatTable();
            });

            // Show Sertifikat details
            $(document).on('click', '.show-sertifikat-btn', function() {
                const sertifikatId = $(this).data('id');
                
                $.ajax({
                    url: `/sertifikat/${sertifikatId}`,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            
                            $('#show_nomor_sertifikat').text(data.nomor_sertifikat);
                            $('#show_peserta').text(data.peserta.nama_lengkap);
                            $('#show_kursus').text(data.kursus.judul);
                            $('#show_tanggal_terbit').text(data.formatted_tanggal);
                            
                            // Status badge
                            let statusBadge = '';
                            if (data.status === 'published') {
                                statusBadge = '<span class="badge bg-success">Published</span>';
                            } else if (data.status === 'draft') {
                                statusBadge = '<span class="badge bg-warning">Draft</span>';
                            } else {
                                statusBadge = '<span class="badge bg-danger">Revoked</span>';
                            }
                            $('#show_status').html(statusBadge);
                            
                            $('#show_penandatangan1').text(data.nama_penandatangan1);
                            $('#show_jabatan1').text(data.jabatan_penandatangan1);
                            $('#show_penandatangan2').text(data.nama_penandatangan2 || '-');
                            $('#show_jabatan2').text(data.jabatan_penandatangan2 || '-');
                            $('#show_email_sent').html(data.is_sent_email 
                                ? '<span class="badge bg-success">Ya</span>' 
                                : '<span class="badge bg-secondary">Belum</span>');
                            
                            // PDF section
                            if (data.file_url) {
                                $('#show_pdf_section').html(`
                                    <iframe src="${data.file_url}" width="100%" height="400px" class="border rounded"></iframe>
                                `);
                                $('#show_download_btn').attr('href', data.download_url).show();
                            } else {
                                $('#show_pdf_section').html('<p class="text-muted">PDF belum di-generate</p>');
                                $('#show_download_btn').hide();
                            }
                            
                            $('#show_edit_btn').data('id', data.id);
                            $('#showSertifikatModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal memuat data sertifikat.');
                    }
                });
            });

            // Edit from show modal
            $('#show_edit_btn').on('click', function() {
                const sertifikatId = $(this).data('id');
                $('#showSertifikatModal').modal('hide');
                window.location.href = `/sertifikat/${sertifikatId}/edit`;
            });

            // Delete Sertifikat
            $(document).on('click', '.delete-sertifikat-btn', function() {
                const sertifikatId = $(this).data('id');
                const sertifikatNomor = $(this).data('nomor');
                
                $('#delete_sertifikat_id').val(sertifikatId);
                $('#delete_sertifikat_nomor').text(sertifikatNomor);
                $('#deleteSertifikatModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                const sertifikatId = $('#delete_sertifikat_id').val();
                
                $.ajax({
                    url: `/sertifikat/${sertifikatId}`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        _method: "DELETE"
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#deleteSertifikatModal').modal('hide');
                            showAlert('success', response.message);
                            loadSertifikatTable();
                        }
                    },
                    error: function(xhr) {
                        $('#deleteSertifikatModal').modal('hide');
                        showAlert('danger', 'Terjadi kesalahan. Silakan coba lagi.');
                    }
                });
            });

            // Generate PDF
            $(document).on('click', '.generate-pdf-btn', function() {
                const sertifikatId = $(this).data('id');
                const btn = $(this);
                
                btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Generating...');
                
                $.ajax({
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
            $(document).on('click', '.send-email-btn', function() {
                const sertifikatId = $(this).data('id');
                const btn = $(this);
                
                if (!confirm('Kirim sertifikat ke email peserta?')) return;
                
                btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Sending...');
                
                $.ajax({
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
                const search = $('#searchInput').val();
                const kursusId = $('#filterKursus').val();
                const status = $('#filterStatus').val();
                
                $.ajax({
                    url: "{{ route('sertifikat.index') }}",
                    type: "GET",
                    data: {
                        search: search,
                        kursus_id: kursusId,
                        status: status
                    },
                    success: function(response) {
                        $('#sertifikatTableContainer').html(response);
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
                
                $('#alertMessage').html(alertHTML).show();
                
                setTimeout(function() {
                    $('#alertMessage').fadeOut();
                }, 5000);
            }
        });
    </script>
@endpush