@extends('layouts.main')

@section('title', 'Detail Sertifikat')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header Card -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center text-primary">
                        <h5 class="mb-0">
                            <i class="bi bi-patch-check"></i> Detail Sertifikat
                        </h5>
                        <div>
                            <a href="{{ route('sertifikat.index') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <a href="{{ route('sertifikat.edit', $sertifikat->id) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            @if($downloadUrl)
                                <a href="{{ $downloadUrl }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bi bi-download"></i> Download PDF
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column - Certificate Info -->
                    <div class="col-lg-12">
                        <!-- Certificate Details -->
                        <div class="card mb-3">
                            <div class="card-header text-primary">
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
                                                    <button class="btn btn-sm btn-outline-primary ms-2" 
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
                                                    <span class="badge">{{ ucfirst($sertifikat->template_name) }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted">Status</th>
                                                <td>
                                                    @if($sertifikat->status === 'published')
                                                        <span class="badge">
                                                            <i class="bi bi-check-circle"></i> Published
                                                        </span>
                                                    @elseif($sertifikat->status === 'draft')
                                                        <span class="badge">
                                                            <i class="bi bi-clock"></i> Draft
                                                        </span>
                                                    @else
                                                        <span class="badge">
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
                                                        <span class="text-primary">
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
                                                        <span class="text-primary">
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
                                    <div class="alert alert-light border mb-0">
                                        <strong><i class="bi bi-sticky"></i> Catatan:</strong><br>
                                        {{ $sertifikat->notes }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Participant Info -->
                        <div class="card mb-3">
                            <div class="card-header text-primary">
                                <h6 class="mb-0"><i class="bi bi-person"></i> Informasi Peserta</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="25%" class="text-muted">Nama Lengkap</th>
                                        <td>
                                            <strong>{{ $sertifikat->peserta->nama_lengkap }}</strong>
                                            {{-- <a href="{{ route('peserta.show', $sertifikat->peserta->id) }}" 
                                                class="btn btn-sm btn-outline-primary ms-2">
                                                <i class="bi bi-eye"></i> Lihat Profil
                                            </a> --}}
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
                                                <a href="mailto:{{ $sertifikat->peserta->email }}" class="text-primary">
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
                            <div class="card-header text-primary">
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
                                            <td>{!! $sertifikat->kursus->deskripsi  !!}</td>
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
                            <div class="card-header text-primary">
                                <h6 class="mb-0"><i class="bi bi-pen"></i> Informasi Penandatangan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Penandatangan 1 -->
                                    <div class="col-md-6">
                                        <div class=" pt-3">
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

                                   
                                </div>
                            </div>
                        </div>

                        <!-- PDF Preview -->
                        @if($fileUrl)
                            <div class="card mb-3">
                                <div class="card-header text-primary">
                                    <h6 class="mb-0"><i class="bi bi-file-pdf"></i> Preview Sertifikat </h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="ratio ratio-16x9" style="min-height: 600px;">
                                        <iframe src="{{ $fileUrl }}" 
                                            class="border-0" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                </div>
                                <div class="card-footer text-center bg-light">
                                    <a href="{{ route('sertifikat.preview', $sertifikat->id) }}" 
                                        class="btn btn-primary" target="_blank">
                                        <i class="bi bi-eye"></i> Buka di Tab Baru
                                    </a>
                                    <a href="{{ $downloadUrl }}" class="btn btn-primary">
                                        <i class="bi bi-download"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                 
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus sertifikat <strong>{{ $sertifikat->nomor_sertifikat }}</strong>?</p>
                    <p class="text-muted"><small>File PDF dan data sertifikat akan dihapus permanen.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('sertifikat.destroy', $sertifikat->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-dark">Hapus</button>
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
                            showAlert('primary', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('dark', 'Gagal generate PDF');
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
                            showAlert('primary', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('dark', xhr.responseJSON?.message || 'Gagal mengirim email');
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
                            showAlert('primary', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('dark', 'Gagal mencabut sertifikat');
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
                            showAlert('primary', response.message);
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr) {
                        showAlert('dark', 'Gagal memulihkan sertifikat');
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
                showAlert('primary', 'Copied to clipboard!');
            }, function(err) {
                showAlert('dark', 'Failed to copy');
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