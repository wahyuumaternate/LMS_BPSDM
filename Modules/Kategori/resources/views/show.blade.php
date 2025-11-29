@extends('layouts.main')

@section('title', 'Detail Kategori Kursus')
@section('page-title', 'Detail Kategori Kursus')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Kategori Kursus</h5>
                        
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width: 35%">Icon</th>
                                        <td>
                                            @if ($kategori->icon)
                                                <i class="bi {{ $kategori->icon }} fs-3"></i>
                                                <span class="ms-2">{{ $kategori->icon }}</span>
                                            @else
                                                <i class="bi bi-folder fs-3"></i>
                                                <span class="ms-2 text-muted">Tidak ada icon</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Nama Kategori</th>
                                        <td>{{ $kategori->nama_kategori }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td><code>{{ $kategori->slug }}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Urutan</th>
                                        <td>{{ $kategori->urutan }}</td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <td>
                                            @if ($kategori->deskripsi)
                                                {!! nl2br(e($kategori->deskripsi)) !!}
                                            @else
                                                <span class="text-muted">Tidak ada deskripsi</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat pada</th>
                                        <td>{{ $kategori->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir diupdate</th>
                                        <td>{{ $kategori->updated_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="mb-3">Daftar Jenis Kursus dalam Kategori Ini</h5>
                        
                        @if ($kategori->jenisKursus && $kategori->jenisKursus->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">No.</th>
                                            <th style="width: 15%">Kode</th>
                                            <th style="width: 30%">Nama Jenis</th>
                                            <th style="width: 15%">Status</th>
                                            <th style="width: 10%">Urutan</th>
                                            <th style="width: 15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kategori->jenisKursus as $key => $jenis)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td><span class="badge bg-primary">{{ $jenis->kode_jenis }}</span></td>
                                                <td>{{ $jenis->nama_jenis }}</td>
                                                <td>
                                                    @if ($jenis->is_active)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>{{ $jenis->urutan }}</td>
                                                <td>
                                                    <a href="{{ route('kategori.jenis-kursus.show', $jenis->id) }}"
                                                        class="btn btn-sm btn-info text-white">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Belum ada jenis kursus dalam kategori ini.
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('kategori.kategori-kursus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal"
                            data-bs-target="#editKategoriModal">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button type="button" class="btn btn-danger btn-delete"
                            data-id="{{ $kategori->id }}" data-nama="{{ $kategori->nama_kategori }}">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
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
                <form action="{{ route('kategori.kategori-kursus.update', $kategori->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3 row">
                            <label for="nama_kategori" class="col-sm-3 col-form-label">Nama Kategori <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror"
                                    id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}"
                                    required>
                                @error('nama_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="slug" class="col-sm-3 col-form-label">Slug</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                    id="slug" name="slug" value="{{ old('slug', $kategori->slug) }}" readonly
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
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi"
                                    name="deskripsi" rows="3">{{ old('deskripsi', $kategori->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 row">
                            <label for="icon" class="col-sm-3 col-form-label">Icon</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                    id="icon" name="icon" value="{{ old('icon', $kategori->icon) }}">
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
                                    id="urutan" name="urutan" value="{{ old('urutan', $kategori->urutan) }}" min="1">
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
            // Auto-generate slug from name - SELALU UPDATE
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

            // Icon preview buttons
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

            // Delete konfirmasi dengan SweetAlert
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        html: `Kategori: <b>${nama}</b> dan semua jenis kursus di dalamnya akan dihapus.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('delete-form');
                            form.action = "{{ route('kategori.kategori-kursus.destroy', '') }}/" + id;
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush