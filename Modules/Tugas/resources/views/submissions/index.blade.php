@extends('layouts.main')

@section('title', 'Pengumpulan Tugas')
@section('page-title', 'Pengumpulan Tugas')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Pengumpulan Tugas: {{ $tugas->judul }}</h5>
                    <div>
                        <a href="{{ route('tugas.show', $tugas->id) }}" class="btn btn-info">
                            <i class="bi bi-info-circle"></i> Detail Tugas
                        </a>
                        <a href="{{ route('tugas.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Tugas
                        </a>
                    </div>
                </div>

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

                <!-- Summary Card -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Pengumpulan</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-file-earmark-check"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $tugas->submissions->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Sudah Dinilai</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success">
                                        <i class="bi bi-check-circle text-white"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $tugas->submissions->where('status', 'dinilai')->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Menunggu Penilaian</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning">
                                        <i class="bi bi-hourglass-split text-white"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $tugas->submissions->where('status', 'menunggu_penilaian')->count() }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Nilai Rata-Rata</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info">
                                        <i class="bi bi-bar-chart text-white"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>
                                            @php 
                                                $avgScore = $tugas->submissions->where('status', 'dinilai')->avg('nilai');
                                                echo $avgScore ? number_format($avgScore, 1) : 'N/A';
                                            @endphp
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Peserta</th>
                                <th>Tanggal Pengumpulan</th>
                                <th>File</th>
                                <th>Keterangan</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugas->submissions as $key => $submission)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $submission->peserta->nama ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($submission->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($submission->file_submission)
                                            <a href="{{ Storage::url('public/' . $submission->file_submission) }}" class="btn btn-sm btn-primary" target="_blank">
                                                <i class="bi bi-file-earmark"></i> Lihat File
                                            </a>
                                        @else
                                            <span class="badge bg-secondary">Tidak ada file</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($submission->keterangan, 50) }}</td>
                                    <td>
                                        @if($submission->status == 'menunggu_penilaian')
                                            <span class="badge bg-warning">Menunggu Penilaian</span>
                                        @elseif($submission->status == 'dinilai')
                                            <span class="badge bg-success">Dinilai</span>
                                        @elseif($submission->status == 'terlambat')
                                            <span class="badge bg-danger">Terlambat</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $submission->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission->status == 'dinilai')
                                            <span class="badge bg-info">{{ $submission->nilai }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#nilaiModal" 
                                                data-submission-id="{{ $submission->id }}"
                                                data-peserta-nama="{{ $submission->peserta->nama ?? 'N/A' }}"
                                                data-submission-nilai="{{ $submission->nilai }}"
                                                data-submission-feedback="{{ $submission->feedback }}">
                                            <i class="bi bi-pencil-square"></i> Nilai
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Belum ada pengumpulan tugas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penilaian -->
<div class="modal fade" id="nilaiModal" tabindex="-1" aria-labelledby="nilaiModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nilaiModalLabel">Penilaian Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="nilaiForm" action="" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Peserta:</label>
                        <input type="text" class="form-control" id="peserta_nama" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="nilai" class="form-label">Nilai (1-{{ $tugas->nilai_maksimal ?? 100 }}):</label>
                        <input type="number" class="form-control" id="nilai" name="nilai" min="0" max="{{ $tugas->nilai_maksimal ?? 100 }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Feedback/Komentar:</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle modal for grading
        const nilaiModal = document.getElementById('nilaiModal');
        if (nilaiModal) {
            nilaiModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const submissionId = button.getAttribute('data-submission-id');
                const pesertaNama = button.getAttribute('data-peserta-nama');
                const nilai = button.getAttribute('data-submission-nilai');
                const feedback = button.getAttribute('data-submission-feedback');
                
                // Set form action URL
                document.getElementById('nilaiForm').action = `/admin/content/submissions/${submissionId}/nilai`;
                
                // Fill form fields
                document.getElementById('peserta_nama').value = pesertaNama;
                document.getElementById('nilai').value = nilai || '';
                document.getElementById('feedback').value = feedback || '';
            });
        }
    });
</script>
@endpush
