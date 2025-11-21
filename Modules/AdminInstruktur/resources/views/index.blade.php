@extends('layouts.main')

@section('title', 'Daftar Admin & Instruktur')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Admin & Instruktur</h5>
                        @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Admin/Instruktur
                        </a>
                        @endif
                    </div>

                    <div class="card-body">
                        <!-- Search and Filter Bar -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form action="{{ route('admin.index') }}" method="GET" id="searchForm">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" 
                                            placeholder="Cari username, email, nama, atau NIP..." 
                                            value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-search"></i> Cari
                                        </button>
                                        @if(request('search') || request('role'))
                                        <a href="{{ route('admin.index') }}" class="btn btn-outline-danger">
                                            <i class="bi bi-x"></i> Clear
                                        </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <form action="{{ route('admin.index') }}" method="GET" id="filterForm">
                                    <select class="form-select" name="role" onchange="this.form.submit()">
                                        <option value="">Semua Role</option>
                                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>
                                            Super Admin
                                        </option>
                                        <option value="instruktur" {{ request('role') == 'instruktur' ? 'selected' : '' }}>
                                            Instruktur
                                        </option>
                                    </select>
                                    @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                </form>
                            </div>
                            <div class="col-md-3">
                                <form action="{{ route('admin.index') }}" method="GET" id="perPageForm">
                                    <select class="form-select" name="per_page" onchange="this.form.submit()">
                                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15 per halaman</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per halaman</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
                                    </select>
                                    @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request('role'))
                                    <input type="hidden" name="role" value="{{ request('role') }}">
                                    @endif
                                </form>
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <!-- Admin/Instruktur Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Foto</th>
                                        <th width="15%">Username</th>
                                        <th width="20%">Nama Lengkap</th>
                                        <th width="12%">NIP</th>
                                        <th width="10%">Role</th>
                                        <th width="15%">Email</th>
                                        <th width="13%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adminInstrukturs as $index => $admin)
                                    <tr>
                                        <td>{{ $adminInstrukturs->firstItem() + $index }}</td>
                                        <td>
                                            @if($admin->foto_profil)
                                            <img src="{{ asset('storage/profile/foto/' . $admin->foto_profil) }}" 
                                                alt="Foto Profil" 
                                                class="rounded-circle" 
                                                width="50" 
                                                height="50"
                                                style="object-fit: cover;">
                                            @else
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" 
                                                style="width: 50px; height: 50px;">
                                                <i class="bi bi-person-fill text-white"></i>
                                            </div>
                                            @endif
                                        </td>
                                        <td>{{ $admin->username }}</td>
                                        <td>
                                            {{ $admin->gelar_depan ? $admin->gelar_depan . ' ' : '' }}
                                            {{ $admin->nama_lengkap }}
                                            {{ $admin->gelar_belakang ? ', ' . $admin->gelar_belakang : '' }}
                                        </td>
                                        <td>{{ $admin->nip ?? '-' }}</td>
                                        <td>
                                            @if($admin->role == 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                            @else
                                            <span class="badge bg-info">Instruktur</span>
                                            @endif
                                        </td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.show', $admin->id) }}" 
                                                    class="btn btn-info btn-sm" 
                                                    title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                @if(auth()->user()->isSuperAdmin() || auth()->id() == $admin->id)
                                                <a href="{{ route('admin.edit', $admin->id) }}" 
                                                    class="btn btn-warning btn-sm" 
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endif
                                                
                                                @if(auth()->user()->isSuperAdmin())
                                                <button type="button" 
                                                    class="btn btn-danger btn-sm delete-btn" 
                                                    data-id="{{ $admin->id }}"
                                                    data-name="{{ $admin->nama_lengkap }}"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data admin/instruktur</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Menampilkan {{ $adminInstrukturs->firstItem() ?? 0 }} 
                                sampai {{ $adminInstrukturs->lastItem() ?? 0 }} 
                                dari {{ $adminInstrukturs->total() }} data
                            </div>
                            <div>
                                {{ $adminInstrukturs->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus <strong id="delete_admin_name"></strong>?</p>
                    <p class="text-danger"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete confirmation
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteForm = document.getElementById('deleteForm');
        const deleteAdminName = document.getElementById('delete_admin_name');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const adminId = this.getAttribute('data-id');
                const adminName = this.getAttribute('data-name');
                
                deleteAdminName.textContent = adminName;
                deleteForm.action = `/admin/admin/${adminId}`;
                
                deleteModal.show();
            });
        });

        // Auto hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush