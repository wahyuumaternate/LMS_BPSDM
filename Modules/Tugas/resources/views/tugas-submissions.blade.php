@extends('layouts.main')

@section('title', 'Manajemen Pengumpulan Tugas')
@section('page-title', 'Manajemen Pengumpulan Tugas')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Pengumpulan Tugas</h5>

                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('submission.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="tugas_id" class="form-label">Filter berdasarkan Tugas</label>
                            <select class="form-select" name="tugas_id" id="tugas_id" onchange="this.form.submit()">
                                <option value="">-- Semua Tugas --</option>
                                @foreach ($tugasList as $tugas)
                                    <option value="{{ $tugas->id }}"
                                        {{ request('tugas_id') == $tugas->id ? 'selected' : '' }}>
                                        {{ $tugas->judul }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="peserta_id" class="form-label">Filter berdasarkan Peserta</label>
                            <select class="form-select" name="peserta_id" id="peserta_id" onchange="this.form.submit()">
                                <option value="">-- Semua Peserta --</option>
                                @foreach ($pesertas as $peserta)
                                    <option value="{{ $peserta->id }}"
                                        {{ request('peserta_id') == $peserta->id ? 'selected' : '' }}>
                                        {{ $peserta->nama_lengkap }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter berdasarkan Status</label>
                            <select class="form-select" name="status" id="status" onchange="this.form.submit()">
                                <option value="">-- Semua Status --</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>
                                    Submitted</option>
                                <option value="graded" {{ request('status') === 'graded' ? 'selected' : '' }}>Sudah Dinilai
                                </option>
                                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>
                                    Dikembalikan</option>
                                <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Terlambat
                                </option>
                            </select>
                        </div>
                    </form>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Peserta</th>
                                    <th>Tugas</th>
                                    <th>Tanggal Kumpul</th>
                                    <th>Status</th>
                                    <th>Nilai</th>
                                    <th>Penilai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submissions as $key => $submission)
                                    <tr>
                                        <td>{{ $submissions->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('submission.by-peserta', $submission->peserta_id) }}">
                                                {{ $submission->peserta->nama ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('submission.by-tugas', $submission->tugas_id) }}">
                                                {{ $submission->tugas->judul ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $submission->tanggal_submit ? \Carbon\Carbon::parse($submission->tanggal_submit)->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td>
                                            @if ($submission->status == 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @elseif($submission->status == 'submitted')
                                                <span class="badge bg-info">Submitted</span>
                                            @elseif($submission->status == 'graded')
                                                <span class="badge bg-success">Sudah Dinilai</span>
                                            @elseif($submission->status == 'returned')
                                                <span class="badge bg-warning">Dikembalikan</span>
                                            @elseif($submission->status == 'late')
                                                <span class="badge bg-danger">Terlambat</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $submission->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($submission->status == 'graded')
                                                {{ $submission->nilai }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $submission->penilai->nama ?? '-' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('submission.show', $submission->id) }}">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a></li>

                                                    @if ($submission->file_jawaban)
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('submission.download', $submission->id) }}">
                                                                <i class="bi bi-download"></i> Download File
                                                            </a></li>
                                                    @endif

                                                    @if (in_array($submission->status, ['draft', 'returned']))
                                                        <li><a class="dropdown-item"
                                                                href="{{ route('submission.edit', $submission->id) }}">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </a></li>
                                                    @endif

                                                    @if (in_array($submission->status, ['submitted', 'late']))
                                                        <li>
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal" data-bs-target="#gradeModal"
                                                                data-submission-id="{{ $submission->id }}"
                                                                data-peserta-name="{{ $submission->peserta->nama ?? 'N/A' }}"
                                                                data-tugas-title="{{ $submission->tugas->judul ?? 'N/A' }}">
                                                                <i class="bi bi-check-circle"></i> Berikan Nilai
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal" data-bs-target="#returnModal"
                                                                data-submission-id="{{ $submission->id }}"
                                                                data-peserta-name="{{ $submission->peserta->nama ?? 'N/A' }}"
                                                                data-tugas-title="{{ $submission->tugas->judul ?? 'N/A' }}">
                                                                <i class="bi bi-arrow-return-left"></i> Kembalikan Untuk
                                                                Revisi
                                                            </button>
                                                        </li>
                                                    @endif

                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('submission.destroy', $submission->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data pengumpulan tugas</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $submissions->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1" aria-labelledby="gradeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="gradeModalLabel">Berikan Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST" id="gradeForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p><strong>Peserta:</strong> <span id="grade-peserta-name"></span></p>
                            <p><strong>Tugas:</strong> <span id="grade-tugas-title"></span></p>
                        </div>
                        <div class="mb-3">
                            <label for="nilai" class="form-label">Nilai (0-100)</label>
                            <input type="number" class="form-control" id="nilai" name="nilai" min="0"
                                max="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="catatan_penilai" class="form-label">Catatan Penilaian</label>
                            <textarea class="form-control" id="catatan_penilai" name="catatan_penilai" rows="3"></textarea>
                            <div class="form-text">Berikan feedback atau komentar untuk peserta.</div>
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

    <!-- Return for Revision Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Kembalikan Untuk Revisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST" id="returnForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <p><strong>Peserta:</strong> <span id="return-peserta-name"></span></p>
                            <p><strong>Tugas:</strong> <span id="return-tugas-title"></span></p>
                        </div>
                        <div class="mb-3">
                            <label for="return_catatan_penilai" class="form-label">Catatan Untuk Perbaikan <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="return_catatan_penilai" name="catatan_penilai" rows="3" required></textarea>
                            <div class="form-text">Berikan feedback dan petunjuk untuk perbaikan.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Kembalikan Untuk Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm(
                            'Apakah Anda yakin ingin menghapus pengumpulan tugas ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });

            // Grade modal
            const gradeModal = document.getElementById('gradeModal');
            if (gradeModal) {
                gradeModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const submissionId = button.getAttribute('data-submission-id');
                    const pesertaName = button.getAttribute('data-peserta-name');
                    const tugasTitle = button.getAttribute('data-tugas-title');

                    // Set form action URL and modal content
                    document.getElementById('gradeForm').action =
                        `/content/submissions/${submissionId}/grade`;
                    document.getElementById('grade-peserta-name').textContent = pesertaName;
                    document.getElementById('grade-tugas-title').textContent = tugasTitle;
                });
            }

            // Return modal
            const returnModal = document.getElementById('returnModal');
            if (returnModal) {
                returnModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const submissionId = button.getAttribute('data-submission-id');
                    const pesertaName = button.getAttribute('data-peserta-name');
                    const tugasTitle = button.getAttribute('data-tugas-title');

                    // Set form action URL and modal content
                    document.getElementById('returnForm').action =
                        `/content/submissions/${submissionId}/return`;
                    document.getElementById('return-peserta-name').textContent = pesertaName;
                    document.getElementById('return-tugas-title').textContent = tugasTitle;
                });
            }

            // Select2 for better dropdowns (if available)
            if (typeof $.fn.select2 !== 'undefined') {
                $('#tugas_id, #peserta_id, #status').select2({
                    allowClear: true
                });
            }
        });
    </script>
@endpush
