@extends('layouts.main')

@section('title', 'Daftar OPD')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Organisasi Perangkat Daerah (OPD)</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOpdModal">
                            <i class="bi bi-plus-circle"></i> Tambah OPD
                        </button>
                    </div>

                    <div class="card-body">
                        <!-- Search Bar -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Cari OPD..."
                                        value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                    @if (request('search'))
                                        <button class="btn btn-outline-danger" type="button" id="clearSearch">
                                            <i class="bi bi-x"></i> Clear
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        <div id="alertMessage" style="display: none;"></div>

                        <!-- OPD Table -->
                        <div id="opdTableContainer">
                            @include('opd::partials.opd_table')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create OPD Modal -->
    <div class="modal fade" id="createOpdModal" tabindex="-1" aria-labelledby="createOpdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createOpdModalLabel">Tambah OPD Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createOpdForm">
                        @csrf
                        <div class="mb-3">
                            <label for="kode_opd" class="form-label">Kode OPD <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="kode_opd" name="kode_opd" required>
                            <div class="invalid-feedback" id="kode_opd_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_opd" class="form-label">Nama OPD <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_opd" name="nama_opd" required>
                            <div class="invalid-feedback" id="nama_opd_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                            <div class="invalid-feedback" id="alamat_error"></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="no_telepon" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon">
                                <div class="invalid-feedback" id="no_telepon_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                                <div class="invalid-feedback" id="email_error"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nama_kepala" class="form-label">Nama Kepala OPD</label>
                            <input type="text" class="form-control" id="nama_kepala" name="nama_kepala">
                            <div class="invalid-feedback" id="nama_kepala_error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="saveOpdBtn">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit OPD Modal -->
    <div class="modal fade" id="editOpdModal" tabindex="-1" aria-labelledby="editOpdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editOpdModalLabel">Edit OPD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editOpdForm">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" id="edit_opd_id" name="opd_id">
                        <div class="mb-3">
                            <label for="edit_kode_opd" class="form-label">Kode OPD <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_kode_opd" name="kode_opd" required>
                            <div class="invalid-feedback" id="edit_kode_opd_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_nama_opd" class="form-label">Nama OPD <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nama_opd" name="nama_opd" required>
                            <div class="invalid-feedback" id="edit_nama_opd_error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3"></textarea>
                            <div class="invalid-feedback" id="edit_alamat_error"></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_no_telepon" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" id="edit_no_telepon" name="no_telepon">
                                <div class="invalid-feedback" id="edit_no_telepon_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                                <div class="invalid-feedback" id="edit_email_error"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_nama_kepala" class="form-label">Nama Kepala OPD</label>
                            <input type="text" class="form-control" id="edit_nama_kepala" name="nama_kepala">
                            <div class="invalid-feedback" id="edit_nama_kepala_error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="updateOpdBtn">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Show OPD Modal -->
    <div class="modal fade" id="showOpdModal" tabindex="-1" aria-labelledby="showOpdModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="showOpdModalLabel">Detail OPD</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Kode OPD</th>
                            <td id="show_kode_opd"></td>
                        </tr>
                        <tr>
                            <th>Nama OPD</th>
                            <td id="show_nama_opd"></td>
                        </tr>
                        <tr>
                            <th>Alamat</th>
                            <td id="show_alamat"></td>
                        </tr>
                        <tr>
                            <th>Nomor Telepon</th>
                            <td id="show_no_telepon"></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td id="show_email"></td>
                        </tr>
                        <tr>
                            <th>Kepala OPD</th>
                            <td id="show_nama_kepala"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-warning text-white edit-btn">Edit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteOpdModal" tabindex="-1" aria-labelledby="deleteOpdModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteOpdModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus OPD <strong id="delete_opd_name"></strong>?</p>
                    <input type="hidden" id="delete_opd_id">
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
  

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            // Periksa apakah jQuery tersedia
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded');
                return;
            }

            // Gunakan jQuery
            jQuery(document).ready(function($) {
                // Search functionality
                $('#searchButton').on('click', function() {
                    const searchValue = $('#searchInput').val();
                    loadOpdTable(searchValue);
                });

                $('#searchInput').on('keypress', function(e) {
                    if (e.which === 13) {
                        const searchValue = $(this).val();
                        loadOpdTable(searchValue);
                    }
                });

                $('#clearSearch').on('click', function() {
                    $('#searchInput').val('');
                    loadOpdTable('');
                });

                // Create OPD
                $('#saveOpdBtn').on('click', function() {
                    const formData = new FormData(document.getElementById('createOpdForm'));

                    $.ajax({
                        url: "{{ route('opd.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                // Reset form
                                $('#createOpdForm')[0].reset();
                                // Close modal
                                $('#createOpdModal').modal('hide');
                                // Show success message
                                showAlert('success', response.message);
                                // Reload OPD table
                                loadOpdTable();
                            }
                        },
                        error: function(xhr) {
                            console.log('Create Error:', xhr);
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                // Display validation errors
                                $.each(errors, function(key, value) {
                                    $(`#${key}`).addClass('is-invalid');
                                    $(`#${key}_error`).text(value[0]);
                                });
                            } else {
                                showAlert('danger',
                                    'Terjadi kesalahan. Silakan coba lagi.');
                            }
                        }
                    });
                });

                // Show OPD details
                $(document).on('click', '.show-opd-btn', function() {
                    const opdId = $(this).data('id');
                    console.log('Show OPD ID:', opdId);

                    $.ajax({
                        url: `/opd/${opdId}`,
                        type: "GET",
                        success: function(response) {
                            console.log('Show Response:', response);
                            if (response.success) {
                                const opd = response.data;

                                // Fill in the details
                                $('#show_kode_opd').text(opd.kode_opd);
                                $('#show_nama_opd').text(opd.nama_opd);
                                $('#show_alamat').text(opd.alamat || '-');
                                $('#show_no_telepon').text(opd.no_telepon || '-');
                                $('#show_email').text(opd.email || '-');
                                $('#show_nama_kepala').text(opd.nama_kepala || '-');

                                // Store ID for edit button
                                $('.edit-btn').data('id', opd.id);

                                // Show modal
                                $('#showOpdModal').modal('show');
                            }
                        },
                        error: function(xhr) {
                            console.log('Show Error:', xhr);
                            showAlert('danger', 'Gagal memuat data OPD.');
                        }
                    });
                });

                // Edit button in show modal
                $('.edit-btn').on('click', function() {
                    const opdId = $(this).data('id');
                    console.log('Edit from Show, OPD ID:', opdId);
                    $('#showOpdModal').modal('hide');
                    loadOpdForEdit(opdId);
                });

                // Edit OPD button click
                $(document).on('click', '.edit-opd-btn', function() {
                    const opdId = $(this).data('id');
                    console.log('Edit OPD ID:', opdId);
                    loadOpdForEdit(opdId);
                });

                // Update OPD
                $('#updateOpdBtn').on('click', function() {
                    const opdId = $('#edit_opd_id').val();
                    console.log('Update OPD ID:', opdId);
                    const formData = new FormData(document.getElementById('editOpdForm'));

                    $.ajax({
                        url: `/opd/${opdId}`,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-HTTP-Method-Override': 'PUT'
                        },
                        success: function(response) {
                            console.log('Update Response:', response);
                            if (response.success) {
                                // Close modal
                                $('#editOpdModal').modal('hide');
                                // Show success message
                                showAlert('success', response.message);
                                // Reload OPD table
                                loadOpdTable();
                            }
                        },
                        error: function(xhr) {
                            console.log('Update Error:', xhr);
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                // Display validation errors
                                $.each(errors, function(key, value) {
                                    $(`#edit_${key}`).addClass('is-invalid');
                                    $(`#edit_${key}_error`).text(value[0]);
                                });
                            } else {
                                showAlert('danger',
                                    'Terjadi kesalahan. Silakan coba lagi.');
                            }
                        }
                    });
                });

                // Delete OPD button click
                $(document).on('click', '.delete-opd-btn', function() {
                    const opdId = $(this).data('id');
                    const opdName = $(this).data('name');
                    console.log('Delete OPD ID:', opdId, 'Name:', opdName);

                    $('#delete_opd_id').val(opdId);
                    $('#delete_opd_name').text(opdName);
                    $('#deleteOpdModal').modal('show');
                });

                // Confirm delete
                $('#confirmDeleteBtn').on('click', function() {
                    const opdId = $('#delete_opd_id').val();
                    console.log('Confirm Delete OPD ID:', opdId);

                    $.ajax({
                        url: `/opd/${opdId}`,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE"
                        },
                        success: function(response) {
                            console.log('Delete Response:', response);
                            if (response.success) {
                                // Close modal
                                $('#deleteOpdModal').modal('hide');
                                // Show success message
                                showAlert('success', response.message);
                                // Reload OPD table
                                loadOpdTable();
                            }
                        },
                        error: function(xhr) {
                            console.log('Delete Error:', xhr);
                            $('#deleteOpdModal').modal('hide');
                            if (xhr.status === 422) {
                                showAlert('danger', xhr.responseJSON.message);
                            } else {
                                showAlert('danger',
                                    'Terjadi kesalahan. Silakan coba lagi.');
                            }
                        }
                    });
                });

                // Clear validation errors when modal is closed
                $('#createOpdModal').on('hidden.bs.modal', function() {
                    clearValidationErrors('create');
                });

                $('#editOpdModal').on('hidden.bs.modal', function() {
                    clearValidationErrors('edit');
                });

                // Helper Functions
                function loadOpdTable(search = '') {
                    $.ajax({
                        url: "{{ route('opd.index') }}",
                        type: "GET",
                        data: {
                            search: search
                        },
                        success: function(response) {
                            $('#opdTableContainer').html(response);
                        },
                        error: function(xhr) {
                            console.log('Table Load Error:', xhr);
                            showAlert('danger', 'Gagal memuat tabel OPD.');
                        }
                    });
                }

                function loadOpdForEdit(opdId) {
                    $.ajax({
                        url: `/opd/${opdId}`,
                        type: "GET",
                        success: function(response) {
                            console.log('Load for Edit Response:', response);
                            if (response.success) {
                                const opd = response.data;

                                // Fill in the form
                                $('#edit_opd_id').val(opd.id);
                                $('#edit_kode_opd').val(opd.kode_opd);
                                $('#edit_nama_opd').val(opd.nama_opd);
                                $('#edit_alamat').val(opd.alamat);
                                $('#edit_no_telepon').val(opd.no_telepon);
                                $('#edit_email').val(opd.email);
                                $('#edit_nama_kepala').val(opd.nama_kepala);

                                // Show modal
                                $('#editOpdModal').modal('show');
                            }
                        },
                        error: function(xhr) {
                            console.log('Load for Edit Error:', xhr);
                            showAlert('danger', 'Gagal memuat data OPD untuk diedit.');
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

                    // Auto hide after 5 seconds
                    setTimeout(function() {
                        $('#alertMessage').fadeOut();
                    }, 5000);
                }

                function clearValidationErrors(prefix) {
                    const prefixStr = prefix === 'edit' ? 'edit_' : '';

                    $(`#${prefix}OpdForm .is-invalid`).removeClass('is-invalid');
                    $(`#${prefix}OpdForm .invalid-feedback`).text('');

                    $(`#${prefixStr}kode_opd_error`).text('');
                    $(`#${prefixStr}nama_opd_error`).text('');
                    $(`#${prefixStr}alamat_error`).text('');
                    $(`#${prefixStr}no_telepon_error`).text('');
                    $(`#${prefixStr}email_error`).text('');
                    $(`#${prefixStr}nama_kepala_error`).text('');
                }
            });
        });
    </script>
@endpush
