@extends('layouts.main')

@section('title', 'Daftar Peserta')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Peserta</h5>
                        <a href="{{ route('peserta.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Peserta
                        </a>
                    </div>

                    <div class="card-body">
                        <!-- Search and Filter Bar -->
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <form action="{{ route('peserta.index') }}" method="GET" id="searchForm">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" 
                                            placeholder="Cari username, email, nama, atau NIP..." 
                                            value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="bi bi-search"></i> Cari
                                        </button>
                                        @if(request('search') || request('opd') || request('status'))
                                        <a href="{{ route('peserta.index') }}" class="btn btn-outline-danger">
                                            <i class="bi bi-x"></i> Clear
                                        </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <form action="{{ route('peserta.index') }}" method="GET" id="filterOpdForm">
                                    <select class="form-select" name="opd" onchange="this.form.submit()">
                                        <option value="">Semua OPD</option>
                                        @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}" {{ request('opd') == $opd->id ? 'selected' : '' }}>
                                            {{ $opd->nama_opd }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request('status'))
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                    @endif
                                </form>
                            </div>
                            <div class="col-md-2">
                                <form action="{{ route('peserta.index') }}" method="GET" id="filterStatusForm">
                                    <select class="form-select" name="status" onchange="this.form.submit()">
                                        <option value="">Semua Status</option>
                                        <option value="pns" {{ request('status') == 'pns' ? 'selected' : '' }}>PNS</option>
                                        <option value="pppk" {{ request('status') == 'pppk' ? 'selected' : '' }}>PPPK</option>
                                        <option value="kontrak" {{ request('status') == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                                    </select>
                                    @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request('opd'))
                                    <input type="hidden" name="opd" value="{{ request('opd') }}">
                                    @endif
                                </form>
                            </div>
                            <div class="col-md-2">
                                <form action="{{ route('peserta.index') }}" method="GET" id="perPageForm">
                                    <select class="form-select" name="per_page" onchange="this.form.submit()">
                                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per halaman</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per halaman</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per halaman</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per halaman</option>
                                    </select>
                                    @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request('opd'))
                                    <input type="hidden" name="opd" value="{{ request('opd') }}">
                                    @endif
                                    @if(request('status'))
                                    <input type="hidden" name="status" value="{{ request('status') }}">
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

                        <!-- Peserta Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table">
                                    <tr>
                                        <th width="3%">No</th>
                                        <th width="7%">Foto</th>
                                        <th width="12%">Username</th>
                                        <th width="15%">Nama Lengkap</th>
                                        <th width="10%">NIP</th>
                                        <th width="15%">OPD</th>
                                        <th width="10%">Jabatan</th>
                                        <th width="8%">Status</th>
                                        <th width="12%">Email</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pesertas as $index => $peserta)
                                    <tr>
                                        <td>{{ $pesertas->firstItem() + $index }}</td>
                                        <td>
                                            @if($peserta->foto_profil)
                                            <img src="{{ asset('storage/profile/foto/' . $peserta->foto_profil) }}" 
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
                                        <td>{{ $peserta->username }}</td>
                                        <td>{{ $peserta->nama_lengkap }}</td>
                                        <td>{{ $peserta->nip ?? '-' }}</td>
                                        <td>
                                            <small>{{ $peserta->opd->nama_opd ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $peserta->jabatan ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if($peserta->status_kepegawaian == 'pns')
                                            <span class="badge bg-success">PNS</span>
                                            @elseif($peserta->status_kepegawaian == 'pppk')
                                            <span class="badge bg-info">PPPK</span>
                                            @else
                                            <span class="badge bg-warning">Kontrak</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $peserta->email }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('peserta.show', $peserta->id) }}" 
                                                    class="btn btn-info btn-sm" 
                                                    title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                
                                                <a href="{{ route('peserta.edit', $peserta->id) }}" 
                                                    class="btn btn-warning btn-sm" 
                                                    title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <button type="button" 
                                                    class="btn btn-danger btn-sm delete-btn" 
                                                    data-id="{{ $peserta->id }}"
                                                    data-name="{{ $peserta->nama_lengkap }}"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center">Tidak ada data peserta</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Menampilkan {{ $pesertas->firstItem() ?? 0 }} 
                                sampai {{ $pesertas->lastItem() ?? 0 }} 
                                dari {{ $pesertas->total() }} data
                            </div>
                            <div>
                                {{ $pesertas->appends(request()->query())->links() }}
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
                    <p>Apakah Anda yakin ingin menghapus peserta <strong id="delete_peserta_name"></strong>?</p>
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
        const deletePesertaName = document.getElementById('delete_peserta_name');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const pesertaId = this.getAttribute('data-id');
                const pesertaName = this.getAttribute('data-name');
                
                deletePesertaName.textContent = pesertaName;
                deleteForm.action = `/admin/peserta/${pesertaId}`;
                
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