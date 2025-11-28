@extends('layouts.main')

@section('title', 'Manajemen Jenis Kursus')
@section('page-title', 'Manajemen Jenis Kursus')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Jenis Kursus</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createJenisModal">
                            <i class="bi bi-plus-circle"></i> Tambah Jenis Kursus
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
        <th style="width: 10%">Kode</th>
        <th style="width: 25%">Nama Jenis</th>
        <th style="width: 25%">Kategori</th>
        <th style="width: 10%">Status</th>
        <th style="width: 10%">Urutan</th>
        <th style="width: 15%">Aksi</th>
    </tr>
</thead>
<tbody id="jenis-sortable">
    @forelse($jenisKursus as $key => $item)
        <tr data-id="{{ $item->id }}">
            <td>{{ $key + 1 }}</td>
            <td><span class="badge bg-primary">{{ $item->kode_jenis }}</span></td>
            <td>{{ $item->nama_jenis }}</td>
            <td>
                @if($item->kategoriKursus)
                    <span class="badge bg-info text-white">
                        <i class="bi {{ $item->kategoriKursus->icon }}"></i>
                        {{ $item->kategoriKursus->nama_kategori }}
                    </span>
                @endif
            </td>
            <td>
                @if($item->is_active)
                    <span class="badge bg-success">Aktif</span>
                @else
                    <span class="badge bg-secondary">Nonaktif</span>
                @endif
            </td>
            <td>{{ $item->urutan }}</td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('kategori.jenis-kursus.show', $item->id) }}"
                        class="btn btn-sm btn-info text-white">
                        <i class="bi bi-eye"></i>
                    </a>
                    <button type="button"
                        class="btn btn-sm btn-warning text-white edit-jenis"
                        data-id="{{ $item->id }}"
                        data-kategori="{{ $item->kategori_kursus_id }}"
                        data-kode="{{ $item->kode_jenis }}"
                        data-nama="{{ $item->nama_jenis }}"
                        data-slug="{{ $item->slug }}"
                        data-deskripsi="{{ $item->deskripsi }}"
                        data-active="{{ $item->is_active }}"
                        data-urutan="{{ $item->urutan }}"
                        data-bs-toggle="modal" data-bs-target="#editJenisModal">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                        data-id="{{ $item->id }}" data-nama="{{ $item->nama_jenis }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center">Tidak ada data jenis kursus</td>
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
    <div class="modal fade" id="createJenisModal" tabindex="-1" aria-labelledby="createJenisModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createJenisModalLabel">Tambah Jenis Kursus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kategori.jenis-kursus.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label for="kategori_kursus_id" class="col-sm-3 col-form-label">Kategori <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select @error('kategori_kursus_id') is-invalid @enderror" 
                                        id="kategori_kursus_id" name="kategori_kursus_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoriKursus as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_kursus_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_kursus_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="kode_jenis" class="col-sm-3 col-form-label">Kode Jenis <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('kode_jenis') is-invalid @enderror"
                                    id="kode_jenis" name="kode_jenis" value="{{ old('kode_jenis') }}" 
                                    placeholder="PKA, PKP, LATSAR, dll" required>
                                @error('kode_jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kode singkat/akronim jenis pelatihan (maks 20 karakter)</div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="nama_jenis" class="col-sm-3 col-form-label">Nama Jenis <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('nama_jenis') is-invalid @enderror"
                                    id="nama_jenis" name="nama_jenis" value="{{ old('nama_jenis') }}" required>
                                @error('nama_jenis')
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
                                <div class="form-text">Slug akan otomatis dibuat dari nama jenis.</div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                          id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <div class="form-text">Urutan tampilan jenis kursus, dimulai dari 1.</div>
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
    <div class="modal fade" id="editJenisModal" tabindex="-1" aria-labelledby="editJenisModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editJenisModalLabel">Edit Jenis Kursus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editJenisForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label for="edit_kategori_kursus_id" class="col-sm-3 col-form-label">Kategori <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-select @error('kategori_kursus_id') is-invalid @enderror" 
                                        id="edit_kategori_kursus_id" name="kategori_kursus_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoriKursus as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                </select>
                                @error('kategori_kursus_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_kode_jenis" class="col-sm-3 col-form-label">Kode Jenis <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('kode_jenis') is-invalid @enderror"
                                    id="edit_kode_jenis" name="kode_jenis" required>
                                @error('kode_jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_nama_jenis" class="col-sm-3 col-form-label">Nama Jenis <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('nama_jenis') is-invalid @enderror"
                                    id="edit_nama_jenis" name="nama_jenis" required>
                                @error('nama_jenis')
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
                                <div class="form-text">Slug akan otomatis dibuat dari nama jenis.</div>
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="edit_deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
                            <div class="col-sm-9">
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                          id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                     
                        <div class="mb-3 row">
                            <label for="edit_is_active" class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select class="form-select" id="edit_is_active" name="is_active">
                                    <option value="1">Aktif</option>
                                    <option value="0">Nonaktif</option>
                                </select>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-generate slug from name (create) - SELALU UPDATE
            const namaJenis = document.getElementById('nama_jenis');
            const slug = document.getElementById('slug');

            if (namaJenis && slug) {
                namaJenis.addEventListener('input', function() {
                    slug.value = namaJenis.value
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                });
            }

            // Auto-generate slug from name (edit) - SELALU UPDATE
            const editNamaJenis = document.getElementById('edit_nama_jenis');
            const editSlug = document.getElementById('edit_slug');

            if (editNamaJenis && editSlug) {
                editNamaJenis.addEventListener('input', function() {
                    editSlug.value = editNamaJenis.value
                        .toLowerCase()
                        .replace(/[^\w\s-]/g, '')
                        .replace(/[\s_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                });
            }

            // Edit jenis kursus
            const editJenisButtons = document.querySelectorAll('.edit-jenis');
            const editForm = document.getElementById('editJenisForm');

            if (editJenisButtons.length > 0 && editForm) {
                // Edit jenis kursus
editJenisButtons.forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const kategori = this.getAttribute('data-kategori');
        const kode = this.getAttribute('data-kode');
        const nama = this.getAttribute('data-nama');
        const slug = this.getAttribute('data-slug');
        const deskripsi = this.getAttribute('data-deskripsi');
        const active = this.getAttribute('data-active');
        const urutan = this.getAttribute('data-urutan');

        editForm.action = "{{ route('kategori.jenis-kursus.update', '') }}/" + id;

        document.getElementById('edit_kategori_kursus_id').value = kategori;
        document.getElementById('edit_kode_jenis').value = kode;
        document.getElementById('edit_nama_jenis').value = nama;
        document.getElementById('edit_slug').value = slug;
        document.getElementById('edit_deskripsi').value = deskripsi;
        document.getElementById('edit_is_active').value = active;
        document.getElementById('edit_urutan').value = urutan;
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
                        html: `Jenis Kursus: <b>${nama}</b> akan dihapus.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('delete-form');
                            form.action = "{{ route('kategori.jenis-kursus.destroy', '') }}/" + id;
                            form.submit();
                        }
                    });
                });
            });

            // Initialize sortable if jQuery UI is available
            if (typeof $.fn.sortable !== 'undefined') {
                $("#jenis-sortable").sortable({
                    update: function(event, ui) {
                        const orders = {};
                        $('#jenis-sortable tr').each(function(index) {
                            const id = $(this).data('id');
                            orders[id] = index + 1;
                        });

                        $.ajax({
                            url: "{{ route('kategori.jenis-kursus.updateOrder') }}",
                            method: 'POST',
                            data: {
                                orders: orders,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: response.success,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    setTimeout(() => location.reload(), 1500);
                                }
                            },
                            error: function(error) {
                                console.error('Error updating order:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Gagal memperbarui urutan jenis kursus.'
                                });
                            }
                        });
                    }
                });
                $("#jenis-sortable").disableSelection();
            }
        });
    </script>
@endpush