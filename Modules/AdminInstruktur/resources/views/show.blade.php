@extends('layouts.main')

@section('title', 'Detail Admin/Instruktur')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Admin/Instruktur</h5>
                        <div>
                            @if(auth()->user()->isSuperAdmin() || auth()->id() == $admin->id)
                            <a href="{{ route('admin.edit', $admin->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @endif
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column: Photo and Basic Info -->
                            <div class="col-md-4 text-center mb-4">
                                <div class="mb-3">
                                    @if($admin->foto_profil)
                                    <img src="{{ asset('storage/profile/foto/' . $admin->foto_profil) }}" 
                                        alt="Foto Profil" 
                                        class="img-thumbnail rounded-circle" 
                                        style="width: 200px; height: 200px; object-fit: cover;">
                                    @else
                                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                        style="width: 200px; height: 200px;">
                                        <i class="bi bi-person-fill text-white" style="font-size: 100px;"></i>
                                    </div>
                                    @endif
                                </div>

                                <h4 class="mb-1">
                                    {{ $admin->gelar_depan ? $admin->gelar_depan . ' ' : '' }}
                                    {{ $admin->nama_lengkap }}
                                    {{ $admin->gelar_belakang ? ', ' . $admin->gelar_belakang : '' }}
                                </h4>

                                <div class="mb-3">
                                    @if($admin->role == 'super_admin')
                                    <span class="badge bg-danger fs-6">Super Admin</span>
                                    @else
                                    <span class="badge bg-info fs-6">Instruktur</span>
                                    @endif
                                </div>

                                @if($admin->email_verified_at)
                                <div class="mb-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Email Verified
                                    </span>
                                </div>
                                @else
                                <div class="mb-2">
                                    <span class="badge bg-warning">
                                        <i class="bi bi-exclamation-circle"></i> Email Not Verified
                                    </span>
                                </div>
                                @endif
                            </div>

                            <!-- Right Column: Details -->
                            <div class="col-md-8">
                                <!-- Account Information -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-person-circle"></i> Informasi Akun
                                    </h6>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Username:</div>
                                        <div class="col-md-8">{{ $admin->username }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Email:</div>
                                        <div class="col-md-8">{{ $admin->email }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Role:</div>
                                        <div class="col-md-8">
                                            @if($admin->role == 'super_admin')
                                            <span class="badge bg-danger">Super Admin</span>
                                            @else
                                            <span class="badge bg-info">Instruktur</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Email Verified At:</div>
                                        <div class="col-md-8">
                                            {{ $admin->email_verified_at ? date('d M Y H:i', strtotime($admin->email_verified_at)) : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Information -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-person-badge"></i> Informasi Pribadi
                                    </h6>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Nama Lengkap:</div>
                                        <div class="col-md-8">{{ $admin->nama_lengkap }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Gelar Depan:</div>
                                        <div class="col-md-8">{{ $admin->gelar_depan ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Gelar Belakang:</div>
                                        <div class="col-md-8">{{ $admin->gelar_belakang ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">NIP:</div>
                                        <div class="col-md-8">{{ $admin->nip ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Bidang Keahlian:</div>
                                        <div class="col-md-8">{{ $admin->bidang_keahlian ?? '-' }}</div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-telephone"></i> Informasi Kontak
                                    </h6>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">No. Telepon:</div>
                                        <div class="col-md-8">{{ $admin->no_telepon ?? '-' }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Alamat:</div>
                                        <div class="col-md-8">{{ $admin->alamat ?? '-' }}</div>
                                    </div>
                                </div>

                                <!-- Activity Information -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-clock-history"></i> Informasi Aktivitas
                                    </h6>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Dibuat Pada:</div>
                                        <div class="col-md-8">{{ date('d M Y H:i', strtotime($admin->created_at)) }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">Terakhir Diupdate:</div>
                                        <div class="col-md-8">{{ date('d M Y H:i', strtotime($admin->updated_at)) }}</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4">
                                    @if(auth()->user()->isSuperAdmin() || auth()->id() == $admin->id)
                                    <a href="{{ route('admin.edit', $admin->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i> Edit Profil
                                    </a>
                                    @endif

                                    @if(auth()->user()->isSuperAdmin() && auth()->id() != $admin->id)
                                    <button type="button" 
                                        class="btn btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    @endif

                                    <a href="{{ route('admin.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if(auth()->user()->isSuperAdmin() && auth()->id() != $admin->id)
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus <strong>{{ $admin->nama_lengkap }}</strong>?</p>
                    <p class="text-danger"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('admin.destroy', $admin->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection