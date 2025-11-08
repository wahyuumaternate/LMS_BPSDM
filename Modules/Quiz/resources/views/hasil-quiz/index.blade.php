@extends('layouts.main')

@section('title', 'Daftar Hasil Quiz')
@section('page-title', 'Daftar Hasil Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Hasil Quiz</h5>
                        {{-- <div>
                            <a href="{{ route('hasil-quiz.export', request()->query()) }}" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Export Data
                            </a>
                        </div> --}}
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('hasil-quiz.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="quiz_id" class="form-label">Filter berdasarkan Quiz</label>
                            <select class="form-select" name="quiz_id" id="quiz_id" onchange="this.form.submit()">
                                <option value="">-- Semua Quiz --</option>
                                @foreach ($quizzes as $quiz)
                                    <option value="{{ $quiz->id }}"
                                        {{ request('quiz_id') == $quiz->id ? 'selected' : '' }}>
                                        {{ $quiz->judul_quiz }}
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
                                        {{ $peserta->nama_lengkap ?? 'Peserta #' . $peserta->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="is_passed" class="form-label">Filter berdasarkan Status</label>
                            <select class="form-select" name="is_passed" id="is_passed" onchange="this.form.submit()">
                                <option value="">-- Semua Status --</option>
                                <option value="1" {{ request('is_passed') === '1' ? 'selected' : '' }}>Lulus</option>
                                <option value="0" {{ request('is_passed') === '0' ? 'selected' : '' }}>Tidak Lulus
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Total Hasil</h5>
                                    <h3>{{ $results->total() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Lulus</h5>
                                    <h3>{{ $results->where('is_passed', true)->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Tidak Lulus</h5>
                                    <h3>{{ $results->where('is_passed', false)->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Rata-rata Nilai</h5>
                                    <h3>{{ number_format($results->avg('nilai'), 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Quiz</th>
                                    <th scope="col">Peserta</th>
                                    <th scope="col">NIP</th>
                                    <th scope="col">Attempt</th>
                                    <th scope="col">Nilai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Durasi</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $key => $result)
                                    <tr>
                                        <th scope="row">{{ $results->firstItem() + $key }}</th>
                                        <td>
                                            <a href="{{ route('hasil-quiz.quiz-overview', $result->quiz_id) }}">
                                                {{ $result->quiz->judul_quiz ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('hasil-quiz.peserta-overview', $result->peserta_id) }}">
                                                {{ $result->peserta->nama_lengkap ?? 'Peserta #' . $result->peserta_id }}
                                            </a>
                                        </td>
                                        <td>{{ $result->peserta->nip ?? 'N/A' }}</td>
                                        <td>{{ $result->attempt }}</td>
                                        <td>{{ number_format($result->nilai, 2) }}</td>
                                        <td>
                                            @if ($result->is_passed)
                                                <span class="badge bg-success">Lulus</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Lulus</span>
                                            @endif
                                        </td>
                                        <td>{{ $result->durasi_pengerjaan_menit }} menit</td>
                                        <td>{{ $result->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('hasil-quiz.show', $result->id) }}">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('hasil-quiz.destroy', $result->id) }}"
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
                                        <td colspan="10" class="text-center">Tidak ada data hasil quiz</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $results->withQueryString()->links() }}
                </div>
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
                            'Apakah Anda yakin ingin menghapus hasil quiz ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
