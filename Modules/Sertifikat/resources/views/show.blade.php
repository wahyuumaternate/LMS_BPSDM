@extends('layouts.main')

@section('title', 'Detail Sertifikat')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header Card -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-patch-check"></i> Detail Sertifikat
                        </h5>
                        <div>
                            <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <a href="{{ route('sertifikat.edit', $sertifikat->id) }}" class="btn btn-warning btn-sm text-white">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @if($downloadUrl)
                                <a href="{{ $downloadUrl }}" class="btn btn-success btn-sm" target="_blank">
                                    <i class="bi bi-download"></i> Download PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column - Certificate Info -->
                    <div class="col-lg-8">
                        <!-- Certificate Details -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Sertifikat</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="40%" class="text-muted">Nomor Sertifikat</th>
                                                <td>
                                                    <strong class="text-primary">{{ $sertifikat->nomor_sertifikat }}</strong>
                                                    <button class="btn btn-sm btn-outline-secondary ms-2" 
                                                        onclick="copyToClipboard('{{ $sertifikat->nomor_sertifikat }}')" 
                                                        title="Copy nomor">
                                                        <i class="bi bi-clipboard"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Tanggal Terbit</th>
                                                <td>{{ $sertifikat->formatted_tanggal }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Template</th>
                                                <td>
                                                    <span class="badge bg-info">{{ ucfirst($sertifikat->template_name) }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Status</th>
                                                <td>
                                                    @if($sertifikat->status === 'published')
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> Published
                                                        </span>
                                                    @elseif($sertifikat->status === 'draft')
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-clock"></i> Draft
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-x-circle"></i> Revoked
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="40%" class="text-muted">File PDF</th>
                                                <td>
                                                    @if($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path))
                                                        <span class="text-success">
                                                            <i class="bi bi-check-circle-fill"></i> Tersedia
                                                        </span>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="bi bi-x-circle"></i> Belum di-generate
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Email Terkirim</th>
                                                <td>
                                                    @if($sertifikat->is_sent_email)
                                                        <span class="text-success">
                                                            <i class="bi bi-envelope-check-fill"></i> 
                                                            {{ $sertifikat->sent_email_at ? $sertifikat->sent_email_at->format('d/m/Y H:i') : 'Ya' }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="bi bi-envelope"></i> Belum dikirim
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Dibuat</th>
                                                <td>{{ $sertifikat->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Terakhir Update</th>
                                                <td>{{ $sertifikat->updated_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                @if($sertifikat->notes)
                                    <div class="alert alert-info mb-0">
                                        <strong><i class="bi bi-sticky"></i> Catatan:</strong><br>
                                        {{ $sertifikat->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Participant Info -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="bi bi-person"></i> Informasi Peserta</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="25%" class="text-muted">Nama Lengkap</th>
                                        <td>
                                            <strong>{{ $sertifikat->peserta->nama_lengkap }}</strong>
                                            <a href="{{ route('peserta.show', $sertifikat->peserta->id) }}" 
                                                class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="bi bi-eye"></i> Lihat Profil
                                            </a>
                                        </td>
                                    </tr>
                                    @if($sertifikat->peserta->nip)
                                        <tr>
                                            <th class="text-muted">NIP</th>
                                            <td>{{ $sertifikat->peserta->nip }}</td>
                                        </tr>
                                    @endif
                                    @if($sertifikat->peserta->email)
                                        <tr>
                                            <th class="text-muted">Email</th>
                                            <td>
                                                <a href="mailto:{{ $sertifikat->peserta->email }}">
                                                    {{ $sertifikat->peserta->email }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    @if($sertifikat->peserta->no_telepon)
                                        <tr>
                                            <th class="text-muted">No. Telepon</th>
                                            <td>{{ $sertifikat->peserta->no_telepon }}</td>
                                        </tr>
                                    @endif
                                    @if($sertifikat->peserta->instansi)
                                        <tr>
                                            <th class="text-muted">Instansi</th>
                                            <td>{{ $sertifikat->peserta->instansi }}</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Course Info -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-book"></i> Informasi Kursus/Pelatihan</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="25%" class="text-muted">Judul Kursus</th>
                                        <td>
                                            <strong>{{ $sertifikat->kursus->judul }}</strong>
                                           
                                        </td>
                                    </tr>
                                    @if($sertifikat->kursus->deskripsi)
                                        <tr>
                                            <th class="text-muted">Deskripsi</th>
                                            <td>{{ Str::limit($sertifikat->kursus->deskripsi, 200) }}</td>
                                        </tr>
                                    @endif
                                    @if($sertifikat->kursus->durasi)
                                        <tr>
                                            <th class="text-muted">Durasi</th>
                                            <td>{{ $sertifikat->kursus->durasi }} Jam Pelajaran</td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Signatories Info -->
                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0"><i class="bi bi-pen"></i> Informasi Penandatangan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Penandatangan 1 -->
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-subtitle mb-2 text-muted">Penandatangan Pertama</h6>
                                                <p class="mb-1"><strong>{{ $sertifikat->nama_penandatangan1 }}</strong></p>
                                                <p class="mb-1 text-muted">{{ $sertifikat->jabatan_penandatangan1 }}</p>
                                                @if($sertifikat->nip_penandatangan1)
                                                    <small class="text-muted">NIP: {{ $sertifikat->nip_penandatangan1 }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Penandatangan 2 -->
                                    @if($sertifikat->nama_penandatangan2)
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2 text-muted">Penandatangan Kedua</h6>
                                                    <p class="mb-1"><strong>{{ $sertifikat->nama_penandatangan2 }}</strong></p>
                                                    <p class="mb-1 text-muted">{{ $sertifikat->jabatan_penandatangan2 }}</p>
                                                    @if($sertifikat->nip_penandatangan2)
                                                        <small class="text-muted">NIP: {{ $sertifikat->nip_penandatangan2 }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- PDF Preview -->
                        @if($fileUrl)
                            <div class="card mb-3">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="bi bi-file-pdf"></i> Preview Sertifikat</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="ratio ratio-16x9" style="min-height: 600px;">
                                        <iframe src="{{ $fileUrl }}" 
                                            class="border-0" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="{{ route('sertifikat.preview', $sertifikat->id) }}" 
                                        class="btn btn-primary" target="_blank">
                                        <i class="bi bi-eye"></i> Buka di Tab Baru
                                    </a>
                                    <a href="{{ $downloadUrl }}" class="btn btn-success">
                                        <i class="bi bi-download"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column - Actions -->
                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    @if(!$sertifikat->file_path || !Storage::disk('public')->exists($sertifikat->file_path))
                                        <button class="btn btn-primary" id="generatePdfBtn">
                                            <i class="bi bi-file-pdf"></i> Generate PDF
                                        </button>
                                    @else
                                        <button class="btn btn-info text-white" id="regeneratePdfBtn">
                                            <i class="bi bi-arrow-repeat"></i> Regenerate PDF
                                        </button>
                                    @endif

                                    @if($sertifikat->file_path && !$sertifikat->is_sent_email && $sertifikat->peserta->email)
                                        <button class="btn btn-success" id="sendEmailBtn">
                                            <i class="bi bi-envelope"></i> Kirim ke Email
                                        </button>
                                    @endif

                                    @if($sertifikat->status === 'published')
                                        <button class="btn btn-danger" id="revokeBtn">
                                            <i class="bi bi-x-circle"></i> Cabut Sertifikat
                                        </button>
                                    @elseif($sertifikat->status === 'revoked')
                                        <button class="btn btn-success" id="restoreBtn">
                                            <i class="bi bi-check-circle"></i> Pulihkan Sertifikat
                                        </button>
                                    @endif

                                    <a href="{{ route('sertifikat.edit', $sertifikat->id) }}" class="btn btn-warning text-white">
                                        <i class="bi bi-pencil"></i> Edit Sertifikat
                                    </a>

                                    <button class="btn btn-outline-danger" id="deleteBtn">
                                        <i class="bi bi-trash"></i> Hapus Sertifikat
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Info -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bi bi-shield-check"></i> Verifikasi</h6>
                            </div>
                            <div class="card-body">
                                @if($sertifikat->verification_url)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">URL Verifikasi:</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" 
                                                value="{{ $sertifikat->verification_url }}" 
                                                id="verificationUrl" readonly>
                                            <button class="btn btn-outline-secondary" 
                                                onclick="copyToClipboard('{{ $sertifikat->verification_url }}')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <a href="{{ $sertifikat->verification_url }}" 
                                        class="btn btn-sm btn-primary w-100" 
                                        target="_blank">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka Halaman Verifikasi
                                    </a>
                                @else
                                    <p class="text-muted mb-0">URL verifikasi belum tersedia</p>
                                @endif
                            </div>
                        </div>

                        <!-- QR Code Placeholder -->
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="bi bi-qr-code"></i> QR Code</h6>
                            </div>
                            <div class="card-body text-center">
                                @if($sertifikat->qr_code_path && Storage::disk('public')->exists($sertifikat->qr_code_path))
                                    <img src="{{ Storage::disk('public')->url($sertifikat->qr_code_path) }}" 
                                        alt="QR Code" class="img-fluid" style="max-width: 200px;">
                                    <p class="text-muted mt-2 mb-0">
                                        <small>Scan untuk verifikasi</small>
                                    </p>
                                @else
                                    <div class="py-5">
                                        <i class="bi bi-qr-code text-muted" style="font-size: 4rem;"></i>
                                        <p class="text-muted mt-2 mb-0">QR Code belum di-generate</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-graph-up"></i> Statistik</h6>
                            </div>
                            <div class="card-body">
                                {{-- <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Sertifikat Peserta:</span>
                                    <strong>{{ $sertifikat->peserta->sertifikats->count() }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Total Sertifikat Kursus:</span>
                                    <strong>{{ $sertifikat->kursus->sertifikats->count() }}</strong>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus sertifikat <strong>{{ $sertifikat->nomor_sertifikat }}</strong>?</p>
                    <p class="text-danger"><small>File PDF dan data sertifikat akan dihapus permanen.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('sertifikat.destroy', $sertifikat->id) }}" method="POST" class="d-inline">
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
        $(document).ready(function() {
            // Generate PDF
            $('#generatePdfBtn, #regeneratePdfBtn').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Generating...');
                
                $.ajax({
                    url: "{{ route('sertifikat.generate-pdf', $sertifikat->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal generate PDF');
                        btn.prop('disabled', false).html('<i class="bi bi-file-pdf"></i> Generate PDF');
                    }
                });
            });

            // Send Email
            $('#sendEmailBtn').on('click', function() {
                if (!confirm('Kirim sertifikat ke email {{ $sertifikat->peserta->email }}?')) return;
                
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i> Sending...');
                
                $.ajax({
                    url: "{{ route('sertifikat.send-email', $sertifikat->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', xhr.responseJSON?.message || 'Gagal mengirim email');
                        btn.prop('disabled', false).html('<i class="bi bi-envelope"></i> Kirim ke Email');
                    }
                });
            });

            // Revoke Certificate
            $('#revokeBtn').on('click', function() {
                if (!confirm('Cabut sertifikat ini? Status akan menjadi "Revoked".')) return;
                
                $.ajax({
                    url: "{{ route('sertifikat.revoke', $sertifikat->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal mencabut sertifikat');
                    }
                });
            });

            // Restore Certificate
            $('#restoreBtn').on('click', function() {
                if (!confirm('Pulihkan sertifikat ini? Status akan menjadi "Published".')) return;
                
                $.ajax({
                    url: "{{ route('sertifikat.restore', $sertifikat->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Gagal memulihkan sertifikat');
                    }
                });
            });

            // Delete Certificate
            $('#deleteBtn').on('click', function() {
                $('#deleteModal').modal('show');
            });
        });

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                showAlert('success', 'Copied to clipboard!');
            }, function(err) {
                showAlert('danger', 'Failed to copy');
            });
        }

        // Show alert function
        function showAlert(type, message) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                    style="z-index: 9999; min-width: 300px;" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('body').append(alertHtml);
            
            setTimeout(function() {
                $('.alert').fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    </script>
@endpush

@push('styles')
    <style>
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .table th {
            font-weight: 600;
        }
        
        .card-header h6 {
            font-weight: 600;
        }
    </style>
@endpush