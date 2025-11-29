@extends('layouts.main')

@section('title', 'Manajemen Kategori Kursus')
@section('page-title', 'Manajemen Kategori Kursus')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Kategori Kursus</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createKategoriModal">
                            <i class="bi bi-plus-circle"></i> Tambah Kategori
                        </button>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No.</th>
                                    <th style="width: 5%">Icon</th>
                                    <th style="width: 25%">Nama Kategori</th>
                                    <th style="width: 20%">Slug</th>
                                    <th style="width: 10%">Urutan</th>
                                    <th style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="kategori-sortable">
                                @forelse($kategori as $key => $item)
                                    <tr data-id="{{ $item->id }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td class="text-center">
                                            @if ($item->icon)
                                                <i class="bi {{ $item->icon }} fs-5"></i>
                                            @else
                                                <i class="bi bi-folder fs-5"></i>
                                            @endif
                                        </td>
                                        <td>{{ $item->nama_kategori }}</td>
                                        <td><code>{{ $item->slug }}</code></td>
                                        <td>{{ $item->urutan }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('kategori.kategori-kursus.show', $item->id) }}"
                                                    class="btn btn-sm btn-info text-white">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button"
                                                    class="btn btn-sm btn-warning text-white edit-kategori"
                                                    data-id="{{ $item->id }}" data-nama="{{ $item->nama_kategori }}"
                                                    data-slug="{{ $item->slug }}"
                                                    data-deskripsi="{{ $item->deskripsi }}"
                                                    data-icon="{{ $item->icon }}" data-urutan="{{ $item->urutan }}"
                                                    data-bs-toggle="modal" data-bs-target="#editKategoriModal">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-id="{{ $item->id }}" data-nama="{{ $item->nama_kategori }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data kategori kursus</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createKategoriModal" tabindex="-1" aria-labelledby="createKategoriModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createKategoriModalLabel">Tambah Kategori Kursus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kategori.kategori-kursus.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-3 row">
                            <label for="nama_kategori" class="col-sm-3 col-form-label">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror"
                                    id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}" required>
                                @error('nama_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="slug" class="col-sm-3 col-form-label">Slug</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug') }}" readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Slug akan otomatis dibuat dari nama kategori.</div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi"
                                    rows="3">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="icon" class="col-sm-3 col-form-label">Icon</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                    id="icon" name="icon" value="{{ old('icon') }}">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nama kelas Bootstrap Icon (contoh: bi-laptop-fill, bi-book)</div>

                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-laptop-fill">
                                        <i class="bi bi-laptop-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-book">
                                        <i class="bi bi-book"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-code-slash">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-palette">
                                        <i class="bi bi-palette"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-calculator">
                                        <i class="bi bi-calculator"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-globe">
                                        <i class="bi bi-globe"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-preview"
                                        data-icon="bi-briefcase">
                                        <i class="bi bi-briefcase"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="urutan" class="col-sm-3 col-form-label">Urutan</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                    id="urutan" name="urutan" value="{{ old('urutan', 1) }}" min="1">
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Urutan tampilan kategori, dimulai dari 1.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editKategoriModal" tabindex="-1" aria-labelledby="editKategoriModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKategoriModalLabel">Edit Kategori Kursus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editKategoriForm" action="" method="POST">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 row">
                            <label for="edit_nama_kategori" class="col-sm-3 col-form-label">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror"
                                    id="edit_nama_kategori" name="nama_kategori" required>
                                @error('nama_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_slug" class="col-sm-3 col-form-label">Slug</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="edit_slug" name="slug" readonly
                                    style="background-color: #e9ecef; cursor: not-allowed;">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Slug akan otomatis dibuat dari nama kategori.</div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="edit_deskripsi" name="deskripsi"
                                    rows="3"></textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_icon" class="col-sm-3 col-form-label">Icon</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                    id="edit_icon" name="icon">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nama kelas Bootstrap Icon (contoh: bi-laptop-fill, bi-book)</div>

                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-laptop-fill">
                                        <i class="bi bi-laptop-fill"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-book">
                                        <i class="bi bi-book"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-code-slash">
                                        <i class="bi bi-code-slash"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-palette">
                                        <i class="bi bi-palette"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-calculator">
                                        <i class="bi bi-calculator"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-globe">
                                        <i class="bi bi-globe"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary edit-icon-preview"
                                        data-icon="bi-briefcase">
                                        <i class="bi bi-briefcase"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_urutan" class="col-sm-3 col-form-label">Urutan</label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                    id="edit_urutan" name="urutan" min="1">
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Urutan tampilan kategori, dimulai dari 1.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Hapus tersembunyi -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
   
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate slug from name (create) - SELALU UPDATE
            const namaKategori = document.getElementById('nama_kategori');
            const slug = document.getElementById('slug');

            if (namaKategori && slug) {
                namaKategori.addEventListener('input', function() {
                    slug.value = namaKategori.value
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                });
            }

            // Auto-generate slug from name (edit) - SELALU UPDATE
            const editNamaKategori = document.getElementById('edit_nama_kategori');
            const editSlug = document.getElementById('edit_slug');

            if (editNamaKategori && editSlug) {
                editNamaKategori.addEventListener('input', function() {
                    editSlug.value = editNamaKategori.value
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                });
            }

            // Icon preview buttons (create)
            const iconPreviewButtons = document.querySelectorAll('.icon-preview');
            const iconField = document.getElementById('icon');

            if (iconPreviewButtons.length > 0 && iconField) {
                iconPreviewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const iconClass = this.getAttribute('data-icon');
                        iconField.value = iconClass;
                    });
                });
            }

            // Icon preview buttons (edit)
            const editIconPreviewButtons = document.querySelectorAll('.edit-icon-preview');
            const editIconField = document.getElementById('edit_icon');

            if (editIconPreviewButtons.length > 0 && editIconField) {
                editIconPreviewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const iconClass = this.getAttribute('data-icon');
                        editIconField.value = iconClass;
                    });
                });
            }

            // Edit kategori
            const editKategoriButtons = document.querySelectorAll('.edit-kategori');
            const editForm = document.getElementById('editKategoriForm');

            if (editKategoriButtons.length > 0 && editForm) {
                editKategoriButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const nama = this.getAttribute('data-nama');
                        const slug = this.getAttribute('data-slug');
                        const deskripsi = this.getAttribute('data-deskripsi');
                        const icon = this.getAttribute('data-icon');
                        const urutan = this.getAttribute('data-urutan');

                        // Gunakan route name yang benar
                        editForm.action = "{{ route('kategori.kategori-kursus.update', '') }}/" +
                            id;

                        document.getElementById('edit_nama_kategori').value = nama;
                        document.getElementById('edit_slug').value = slug;
                        document.getElementById('edit_deskripsi').value = deskripsi;
                        document.getElementById('edit_icon').value = icon;
                        document.getElementById('edit_urutan').value = urutan;

                        // If using TinyMCE
                        if (typeof tinymce !== 'undefined' && tinymce.get('edit_deskripsi')) {
                            tinymce.get('edit_deskripsi').setContent(deskripsi || '');
                        }
                    });
                });
            }

            // Delete konfirmasi dengan SweetAlert
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        html: `Kategori: <b>${nama}</b> akan dihapus.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('delete-form');
                            form.action =
                                "{{ route('kategori.kategori-kursus.destroy', '') }}/" +
                                id;
                            form.submit();
                        }
                    });
                });
            });

            // Initialize TinyMCE for description if available
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#deskripsi, #edit_deskripsi',
                    height: 200,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    setup: function(editor) {
                        editor.on('change', function() {
                            editor.save(); // Update textarea value
                        });
                    }
                });
            }

            // Initialize sortable if jQuery UI is available
            if (typeof $.fn.sortable !== 'undefined') {
                $("#kategori-sortable").sortable({
                    update: function(event, ui) {
                        const orders = {};
                        $('#kategori-sortable tr').each(function(index) {
                            const id = $(this).data('id');
                            orders[id] = index + 1;
                        });

                        // Save new order via AJAX
                        $.ajax({
                            url: "{{ route('kategori.kursus.updateOrder') }}",
                            method: 'POST',
                            data: {
                                orders: orders,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                // Show success message
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: response.success,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    // Refresh the page after 1.5 seconds
                                    setTimeout(() => location.reload(), 1500);
                                }
                            },
                            error: function(error) {
                                console.error('Error updating order:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Gagal memperbarui urutan kategori.'
                                });
                            }
                        });
                    }
                });
                $("#kategori-sortable").disableSelection();
            }

            // Show modals if errors
            @if (session('error_modal') == 'create')
                new bootstrap.Modal(document.getElementById('createKategoriModal')).show();
            @endif

            @if (session('error_modal') == 'edit' && session('edit_id'))
                document.querySelector(`.edit-kategori[data-id="{{ session('edit_id') }}"]`).click();
            @endif
        });
    </script>
@endpush