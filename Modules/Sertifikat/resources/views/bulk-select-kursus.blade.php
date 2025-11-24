@extends('layouts.main')

@section('title', 'Generate Sertifikat Massal - Pilih Kursus')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Generate Sertifikat Massal - Pilih Kursus</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Pilih kursus untuk generate sertifikat secara massal kepada peserta yang terdaftar.
                        </div>

                        <form action="{{ route('sertifikat.bulk.generate-form') }}" method="GET">
                            <div class="mb-4">
                                <label for="kursus_id" class="form-label">
                                    Pilih Kursus <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="kursus_id" name="kursus_id" required>
                                    <option value="">-- Pilih Kursus --</option>
                                    @foreach($kursusList as $kursus)
                                        <option value="{{ $kursus->id }}">
                                            {{ $kursus->judul }}
                                            @if(isset($kursus->pesertas_count))
                                                ({{ $kursus->pesertas_count }} peserta)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    Pilih kursus yang pesertanya akan dibuatkan sertifikat
                                </small>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('sertifikat.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-right"></i> Lanjutkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title"><i class="bi bi-lightbulb"></i> Informasi</h6>
                        <ul class="mb-0">
                            <li>Sistem akan menampilkan daftar peserta yang belum memiliki sertifikat untuk kursus yang dipilih</li>
                            <li>Anda dapat memilih peserta mana saja yang akan dibuatkan sertifikat</li>
                            <li>Data penandatangan dapat diatur secara bersamaan untuk semua sertifikat</li>
                            <li>PDF sertifikat akan otomatis di-generate untuk semua peserta yang dipilih</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#kursus_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- Pilih Kursus --'
            });
        });
    </script>
@endpush

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush