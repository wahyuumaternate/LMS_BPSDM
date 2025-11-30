@extends('layouts.main')

@section('title', 'Kelola Kategori Berita')

@section('content')
<div class="pagetitle">
    <h1>Kelola Kategori Berita</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Kategori Berita</li>
        </ol>
    </nav>
</div>

<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Kategori</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bi bi-plus-circle"></i> Tambah Kategori
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Kategori</th>
                                    <th>Slug</th>
                                    <th width="100">Icon</th>
                                    <th width="100">Color</th>
                                    <th width="80">Urutan</th>
                                    <th width="100">Jumlah Berita</th>
                                    <th width="100">Status</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoris as $kategori)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $kategori->nama_kategori }}</strong>
                                        @if($kategori->deskripsi)
                                            <br><small class="text-muted">{{ Str::limit($kategori->deskripsi, 50) }}</small>
                                        @endif
                                    </td>
                                    <td><code>{{ $kategori->slug }}</code></td>
                                    <td>
                                        @if($kategori->icon)
                                            <i class="{{ $kategori->icon }}" style="font-size: 1.5rem;"></i>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $kategori->badge_color_class }}">
                                            {{ $kategori->color }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $kategori->urutan }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $kategori->berita_count }}</span>
                                    </td>
                                    <td>{!! $kategori->status_badge !!}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="editKategori({{ $kategori->id }}, '{{ $kategori->nama_kategori }}', '{{ $kategori->deskripsi }}', '{{ $kategori->icon }}', '{{ $kategori->color }}', {{ $kategori->urutan }}, {{ $kategori->is_active ? 1 : 0 }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete({{ $kategori->id }}, '{{ $kategori->nama_kategori }}', {{ $kategori->berita_count }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        
                                        <form id="delete-form-{{ $kategori->id }}" 
                                              action="{{ route('kategori-berita.destroy', $kategori->id) }}" 
                                              method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-2">Belum ada kategori</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('kategori-berita.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_kategori" class="form-label">Nama Kategori *</label>
                        <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="icon" class="form-label">Icon (Bootstrap Icons)</label>
                        <input type="text" class="form-control" id="icon" name="icon" 
                               placeholder="bi-newspaper">
                        <small class="form-text text-muted">
                            Lihat icons di <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label">Warna Badge *</label>
                        <select class="form-select" id="color" name="color" required>
                            <option value="primary">Primary (Biru)</option>
                            <option value="secondary">Secondary (Abu)</option>
                            <option value="success">Success (Hijau)</option>
                            <option value="danger">Danger (Merah)</option>
                            <option value="warning">Warning (Kuning)</option>
                            <option value="info">Info (Cyan)</option>
                            <option value="dark">Dark (Hitam)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="urutan" class="form-label">Urutan</label>
                        <input type="number" class="form-control" id="urutan" name="urutan" value="0" min="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Aktif</label>
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
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nama_kategori" class="form-label">Nama Kategori *</label>
                        <input type="text" class="form-control" id="edit_nama_kategori" name="nama_kategori" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_icon" class="form-label">Icon</label>
                        <input type="text" class="form-control" id="edit_icon" name="icon">
                    </div>

                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Warna Badge *</label>
                        <select class="form-select" id="edit_color" name="color" required>
                            <option value="primary">Primary (Biru)</option>
                            <option value="secondary">Secondary (Abu)</option>
                            <option value="success">Success (Hijau)</option>
                            <option value="danger">Danger (Merah)</option>
                            <option value="warning">Warning (Kuning)</option>
                            <option value="info">Info (Cyan)</option>
                            <option value="dark">Dark (Hitam)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_urutan" class="form-label">Urutan</label>
                        <input type="number" class="form-control" id="edit_urutan" name="urutan" min="0">
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editKategori(id, nama, deskripsi, icon, color, urutan, isActive) {
    document.getElementById('editForm').action = '/admin/kategori-berita/' + id;
    document.getElementById('edit_nama_kategori').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi || '';
    document.getElementById('edit_icon').value = icon || '';
    document.getElementById('edit_color').value = color;
    document.getElementById('edit_urutan').value = urutan;
    document.getElementById('edit_is_active').checked = isActive == 1;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function confirmDelete(id, nama, beritaCount) {
    if (beritaCount > 0) {
        Swal.fire({
            title: 'Tidak Bisa Dihapus!',
            html: `Kategori <strong>${nama}</strong> masih memiliki <strong>${beritaCount} berita</strong>.<br><br>Hapus atau pindahkan berita terlebih dahulu.`,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus kategori <strong>${nama}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush