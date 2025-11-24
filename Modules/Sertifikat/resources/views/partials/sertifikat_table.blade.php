<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead class="table-light">
            <tr>
                <th width="5%">No</th>
                <th width="15%">Nomor Sertifikat</th>
                <th width="20%">Peserta</th>
                <th width="20%">Kursus</th>
                <th width="12%">Tanggal Terbit</th>
                <th width="10%">Status</th>
                <th width="15%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sertifikats as $index => $sertifikat)
                <tr>
                    <td>{{ $sertifikats->firstItem() + $index }}</td>
                    <td>
                        <strong>{{ $sertifikat->nomor_sertifikat }}</strong>
                        @if($sertifikat->is_sent_email)
                            <br><small class="text-success"><i class="bi bi-envelope-check"></i> Email sent</small>
                        @endif
                    </td>
                    <td>
                        {{ $sertifikat->peserta->nama_lengkap }}
                        @if($sertifikat->peserta->nip)
                            <br><small class="text-muted">NIP: {{ $sertifikat->peserta->nip }}</small>
                        @endif
                    </td>
                    <td>{{ $sertifikat->kursus->judul }}</td>
                    <td>{{ $sertifikat->tanggal_terbit->format('d/m/Y') }}</td>
                    <td>
                        @if($sertifikat->status === 'published')
                            <span class="badge bg-success">Published</span>
                        @elseif($sertifikat->status === 'draft')
                            <span class="badge bg-warning">Draft</span>
                        @else
                            <span class="badge bg-danger">Revoked</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            {{-- Preview PDF --}}
                            <a href="{{ route('sertifikat.preview', $sertifikat->id) }}" 
                                class="btn btn-primary" 
                                title="Preview PDF"
                                target="_blank">
                                <i class="bi bi-eye"></i>
                            </a>
                            
                            {{-- Download PDF --}}
                            @if($sertifikat->file_path && Storage::disk('public')->exists($sertifikat->file_path))
                                <a href="{{ route('sertifikat.download', $sertifikat->id) }}" 
                                    class="btn btn-success" 
                                    title="Download PDF">
                                    <i class="bi bi-download"></i>
                                </a>
                            @else
                                <button class="btn btn-outline-success generate-pdf-btn" 
                                    data-id="{{ $sertifikat->id }}" 
                                    title="Generate PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                            @endif
                            
                           
                            {{-- Detail --}}
                            <button type="button" 
                                class="btn btn-secondary show-sertifikat-btn" 
                                data-id="{{ $sertifikat->id }}" 
                                title="Detail">
                                <i class="bi bi-info-circle"></i>
                            </button>
                            
                            {{-- Edit --}}
                            <a href="{{ route('sertifikat.edit', $sertifikat->id) }}" 
                                class="btn btn-warning" 
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            
                            {{-- Send Email --}}
                            @if($sertifikat->file_path && !$sertifikat->is_sent_email)
                                <button type="button" 
                                    class="btn btn-primary send-email-btn" 
                                    data-id="{{ $sertifikat->id }}" 
                                    title="Kirim Email">
                                    <i class="bi bi-envelope"></i>
                                </button>
                            @endif
                            
                            {{-- Delete --}}
                            <button type="button" 
                                class="btn btn-danger delete-sertifikat-btn" 
                                data-id="{{ $sertifikat->id }}" 
                                data-nomor="{{ $sertifikat->nomor_sertifikat }}" 
                                title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Tidak ada data sertifikat</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($sertifikats->hasPages())
    <div class="mt-3">
        {{ $sertifikats->links() }}
    </div>
@endif

{{-- JavaScript untuk Handle Actions --}}
@push('scripts')
<script>
$(document).ready(function() {
    
    // Handle Generate PDF Button
    $('.generate-pdf-btn').on('click', function() {
        const button = $(this);
        const id = button.data('id');
        
        Swal.fire({
            title: 'Generate PDF?',
            text: 'PDF akan digenerate dan disimpan ke storage',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Generate!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button
                button.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span>');
                
                // Ajax request
                $.ajax({
                    url: `/sertifikat/${id}/generate`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload page untuk update tombol
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        // Enable button kembali
                        button.prop('disabled', false)
                            .html('<i class="bi bi-file-earmark-pdf"></i>');
                        
                        let errorMsg = 'Gagal generate PDF';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });
    
    // Handle Show Detail Button
    $('.show-sertifikat-btn').on('click', function() {
        const id = $(this).data('id');
        
        // Show loading
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Ajax request
        $.ajax({
            url: `/sertifikat/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    
                    // Format data untuk ditampilkan
                    let html = `
                        <div class="text-start">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nomor Sertifikat</th>
                                    <td>${data.nomor_sertifikat}</td>
                                </tr>
                                <tr>
                                    <th>Peserta</th>
                                    <td>${data.peserta.nama_lengkap}</td>
                                </tr>
                                ${data.peserta.nip ? `
                                <tr>
                                    <th>NIP</th>
                                    <td>${data.peserta.nip}</td>
                                </tr>
                                ` : ''}
                                <tr>
                                    <th>Kursus</th>
                                    <td>${data.kursus.judul}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Terbit</th>
                                    <td>${new Date(data.tanggal_terbit).toLocaleDateString('id-ID')}</td>
                                </tr>
                                <tr>
                                    <th>Tempat Terbit</th>
                                    <td>${data.tempat_terbit || '-'}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-${data.status === 'published' ? 'success' : (data.status === 'draft' ? 'warning' : 'danger')}">
                                            ${data.status.toUpperCase()}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Penandatangan</th>
                                    <td>
                                        ${data.nama_penandatangan1}<br>
                                        <small class="text-muted">${data.jabatan_penandatangan1}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    `;
                    
                    Swal.fire({
                        title: 'Detail Sertifikat',
                        html: html,
                        width: '600px',
                        confirmButtonText: 'Tutup'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal mengambil data sertifikat'
                });
            }
        });
    });
    
    // Handle Delete Button
    $('.delete-sertifikat-btn').on('click', function() {
        const id = $(this).data('id');
        const nomor = $(this).data('nomor');
        
        Swal.fire({
            title: 'Hapus Sertifikat?',
            html: `Anda yakin ingin menghapus sertifikat<br><strong>${nomor}</strong>?<br><br>
                   <small class="text-danger">Data yang dihapus tidak dapat dikembalikan!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Ajax request
                $.ajax({
                    url: `/sertifikat/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMsg = 'Gagal menghapus sertifikat';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });
    
    // Handle Send Email Button
    $('.send-email-btn').on('click', function() {
        const id = $(this).data('id');
        const button = $(this);
        
        Swal.fire({
            title: 'Kirim Email?',
            text: 'Sertifikat akan dikirim ke email peserta',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span>');
                
                $.ajax({
                    url: `/sertifikat/${id}/send-email`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terkirim!',
                            text: response.message,
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        button.prop('disabled', false)
                            .html('<i class="bi bi-envelope"></i>');
                        
                        let errorMsg = 'Gagal mengirim email';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush