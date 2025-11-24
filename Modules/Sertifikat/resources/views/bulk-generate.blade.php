@extends('layouts.main')

@section('title', 'Generate Sertifikat Massal')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Generate Sertifikat Massal - {{ $kursus->judul }}</h5>
                    </div>
                    <div class="card-body">
                        @if(session('errors') && count(session('errors')) > 0)
                            <div class="alert alert-warning">
                                <strong>Beberapa error terjadi:</strong>
                                <ul class="mb-0">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('sertifikat.bulk.generate') }}" method="POST" id="bulkGenerateForm">
                            @csrf
                            <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                            
                            <!-- Info Kursus -->
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Kursus:</strong> {{ $kursus->judul }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Total Peserta Tersedia:</strong> {{ $availablePesertas->count() }}
                                    </div>
                                </div>
                            </div>

                            @if($availablePesertas->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Tidak ada peserta yang tersedia untuk di-generate sertifikat.
                                    Semua peserta sudah memiliki sertifikat atau tidak ada peserta terdaftar.
                                </div>
                                <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            @else
                                <!-- Pilih Peserta -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label mb-0">Pilih Peserta <span class="text-danger">*</span></label>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                                <i class="bi bi-check-all"></i> Pilih Semua
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                                <i class="bi bi-x"></i> Batal Pilih
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="5%">
                                                        <input type="checkbox" class="form-check-input" id="checkAll">
                                                    </th>
                                                    <th width="5%">No</th>
                                                    <th>Nama Peserta</th>
                                                    <th width="20%">NIP</th>
                                                    <th width="25%">Email</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($availablePesertas as $index => $peserta)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="form-check-input peserta-checkbox" 
                                                                name="peserta_ids[]" value="{{ $peserta->id }}">
                                                        </td>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $peserta->nama_lengkap }}</td>
                                                        <td>{{ $peserta->nip ?? '-' }}</td>
                                                        <td>{{ $peserta->email ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @error('peserta_ids')
                                        <div class="text-danger"><small>{{ $message }}</small></div>
                                    @enderror
                                </div>

                                <!-- Tanggal & Tempat Terbit -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tanggal_terbit" class="form-label">Tanggal Terbit <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('tanggal_terbit') is-invalid @enderror" 
                                            id="tanggal_terbit" name="tanggal_terbit" 
                                            value="{{ old('tanggal_terbit', date('Y-m-d')) }}" required>
                                        @error('tanggal_terbit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="tempat_terbit" class="form-label">Tempat Terbit <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('tempat_terbit') is-invalid @enderror" 
                                            id="tempat_terbit" name="tempat_terbit" 
                                            value="{{ old('tempat_terbit', 'Jakarta') }}" required>
                                        @error('tempat_terbit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Penandatangan 1 -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Penandatangan Pertama</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nama_penandatangan1" class="form-label">Nama <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="nama_penandatangan1" 
                                                    name="nama_penandatangan1" 
                                                    value="{{ old('nama_penandatangan1', $defaultSignatories['penandatangan1']['nama'] ?? '') }}" required>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="jabatan_penandatangan1" class="form-label">Jabatan <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="jabatan_penandatangan1" 
                                                    name="jabatan_penandatangan1" 
                                                    value="{{ old('jabatan_penandatangan1', $defaultSignatories['penandatangan1']['jabatan'] ?? '') }}" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="nip_penandatangan1" class="form-label">NIP</label>
                                                <input type="text" class="form-control" id="nip_penandatangan1" 
                                                    name="nip_penandatangan1" 
                                                    value="{{ old('nip_penandatangan1', $defaultSignatories['penandatangan1']['nip'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Penandatangan 2 -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Penandatangan Kedua (Opsional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="nama_penandatangan2" class="form-label">Nama</label>
                                                <input type="text" class="form-control" id="nama_penandatangan2" 
                                                    name="nama_penandatangan2" 
                                                    value="{{ old('nama_penandatangan2', $defaultSignatories['penandatangan2']['nama'] ?? '') }}">
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="jabatan_penandatangan2" class="form-label">Jabatan</label>
                                                <input type="text" class="form-control" id="jabatan_penandatangan2" 
                                                    name="jabatan_penandatangan2" 
                                                    value="{{ old('jabatan_penandatangan2', $defaultSignatories['penandatangan2']['jabatan'] ?? '') }}">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="nip_penandatangan2" class="form-label">NIP</label>
                                                <input type="text" class="form-control" id="nip_penandatangan2" 
                                                    name="nip_penandatangan2" 
                                                    value="{{ old('nip_penandatangan2', $defaultSignatories['penandatangan2']['nip'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bi bi-file-earmark-plus"></i> Generate Sertifikat (<span id="selectedCount">0</span>)
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Check all
            $('#checkAll').on('change', function() {
                $('.peserta-checkbox').prop('checked', $(this).prop('checked'));
                updateSelectedCount();
            });

            // Individual checkbox
            $('.peserta-checkbox').on('change', function() {
                updateCheckAllState();
                updateSelectedCount();
            });

            // Select all button
            $('#selectAll').on('click', function() {
                $('.peserta-checkbox').prop('checked', true);
                $('#checkAll').prop('checked', true);
                updateSelectedCount();
            });

            // Deselect all button
            $('#deselectAll').on('click', function() {
                $('.peserta-checkbox').prop('checked', false);
                $('#checkAll').prop('checked', false);
                updateSelectedCount();
            });

            // Form submission
            $('#bulkGenerateForm').on('submit', function(e) {
                const selectedCount = $('.peserta-checkbox:checked').length;
                
                if (selectedCount === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu peserta!');
                    return false;
                }
                
                if (!confirm(`Generate ${selectedCount} sertifikat?`)) {
                    e.preventDefault();
                    return false;
                }
                
                $('#submitBtn').prop('disabled', true)
                    .html('<i class="spinner-border spinner-border-sm"></i> Generating...');
            });

            function updateCheckAllState() {
                const total = $('.peserta-checkbox').length;
                const checked = $('.peserta-checkbox:checked').length;
                $('#checkAll').prop('checked', total === checked);
            }

            function updateSelectedCount() {
                const count = $('.peserta-checkbox:checked').length;
                $('#selectedCount').text(count);
            }

            // Initial count
            updateSelectedCount();
        });
    </script>
@endpush