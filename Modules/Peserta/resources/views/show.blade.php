@extends('layouts.main')

@section('title', 'Detail Peserta')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Peserta</h5>
                        <div>
                            <a href="{{ route('peserta.edit', $pesertum->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="{{ route('peserta.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column - Photo and Basic Info -->
                            <div class="col-md-4 text-center mb-4">
                                @if($pesertum->foto_profil)
                                <img src="{{ asset('storage/profile/foto/' . $pesertum->foto_profil) }}" 
                                    alt="Foto Profil" 
                                    class="img-fluid rounded-circle mb-3" 
                                    style="width: 200px; height: 200px; object-fit: cover;">
                                @else
                                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                                    style="width: 200px; height: 200px;">
                                    <i class="bi bi-person-fill text-white" style="font-size: 100px;"></i>
                                </div>
                                @endif
                                
                                <h4>{{ $pesertum->nama_lengkap }}</h4>
                                <p class="text-muted">{{ $pesertum->username }}</p>
                                
                                <div class="mt-3">
                                    @if($pesertum->status_kepegawaian == 'pns')
                                    <span class="badge bg-success fs-6">PNS</span>
                                    @elseif($pesertum->status_kepegawaian == 'pppk')
                                    <span class="badge bg-info fs-6">PPPK</span>
                                    @else
                                    <span class="badge bg-warning fs-6">Kontrak</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Right Column - Detailed Info -->
                            <div class="col-md-8">
                                <!-- Informasi Akun -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-person-circle"></i> Informasi Akun
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Username</small>
                                            <strong>{{ $pesertum->username }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Email</small>
                                            <strong>{{ $pesertum->email }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Pribadi -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-person-badge"></i> Data Pribadi
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">NIP</small>
                                            <strong>{{ $pesertum->nip ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Jenis Kelamin</small>
                                            <strong>{{ $pesertum->jenis_kelamin_label }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Tempat Lahir</small>
                                            <strong>{{ $pesertum->tempat_lahir ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Tanggal Lahir</small>
                                            <strong>{{ $pesertum->tanggal_lahir ? $pesertum->tanggal_lahir->format('d F Y') : '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">No. Telepon</small>
                                            <strong>{{ $pesertum->no_telepon ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Pendidikan Terakhir</small>
                                            <strong>{{ $pesertum->pendidikan_terakhir_label }}</strong>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <small class="text-muted d-block">Alamat</small>
                                            <strong>{{ $pesertum->alamat ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Kepegawaian -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-building"></i> Data Kepegawaian
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">OPD</small>
                                            <strong>{{ $pesertum->opd->nama_opd ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Status Kepegawaian</small>
                                            <strong>{{ $pesertum->status_kepegawaian_label }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Pangkat/Golongan</small>
                                            <strong>{{ $pesertum->pangkat_golongan ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Jabatan</small>
                                            <strong>{{ $pesertum->jabatan ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Sistem -->
                                <div class="mb-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="bi bi-clock-history"></i> Informasi Sistem
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Dibuat Pada</small>
                                            <strong>{{ $pesertum->created_at->format('d F Y H:i') }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Terakhir Diupdate</small>
                                            <strong>{{ $pesertum->updated_at->format('d F Y H:i') }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <small class="text-muted d-block">Email Terverifikasi</small>
                                            <strong>
                                                @if($pesertum->email_verified_at)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i> Terverifikasi
                                                </span>
                                                @else
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-x-circle"></i> Belum Terverifikasi
                                                </span>
                                                @endif
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <a href="{{ route('peserta.edit', $pesertum->id) }}" class="btn btn-warning">
                                            <i class="bi bi-pencil"></i> Edit Data
                                        </a>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="bi bi-trash"></i> Hapus Peserta
                                        </button>
                                    </div>
                                </div>
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
                    <p>Apakah Anda yakin ingin menghapus peserta <strong>{{ $pesertum->nama_lengkap }}</strong>?</p>
                    <p class="text-danger"><small>Data yang dihapus tidak dapat dikembalikan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('peserta.destroy', $pesertum->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection