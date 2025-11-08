@extends('layouts.main')

@section('title', 'Daftar Quiz')
@section('page-title', 'Daftar Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Quiz Kursus</h5>
                        <div>
                            <a href="{{ route('quizzes.create', request()->query()) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Quiz
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('quizzes.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="modul_id" class="form-label">Filter berdasarkan Modul</label>
                            <select class="form-select" name="modul_id" id="modul_id" onchange="this.form.submit()">
                                <option value="">-- Semua Modul --</option>
                                @foreach (\Modules\Modul\Entities\Modul::orderBy('nama_modul')->get() as $modul)
                                    <option value="{{ $modul->id }}"
                                        {{ request('modul_id') == $modul->id ? 'selected' : '' }}>
                                        {{ $modul->nama_modul }}
                                    </option>
                                @endforeach
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

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Modul</th>
                                    <th scope="col">Judul Quiz</th>
                                    <th scope="col">Durasi</th>
                                    <th scope="col">Bobot Nilai</th>
                                    <th scope="col">Passing Grade</th>
                                    <th scope="col">Jumlah Soal</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $key => $quiz)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $quiz->modul->nama_modul ?? 'N/A' }}</td>
                                        <td>{{ $quiz->judul_quiz }}</td>
                                        <td>{{ $quiz->durasi_menit ?? '-' }} {{ $quiz->durasi_menit ? 'menit' : '' }}</td>
                                        <td>{{ $quiz->bobot_nilai ?? '-' }}</td>
                                        <td>{{ $quiz->passing_grade ?? '-' }}%</td>
                                        <td>{{ $quiz->jumlah_soal ?? '0' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('quizzes.show', $quiz->id) }}">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('quizzes.edit', $quiz->id) }}">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('quizzes.destroy', $quiz->id) }}"
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
                                        <td colspan="8" class="text-center">Tidak ada data quiz</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($quizzes, 'links'))
                        {{ $quizzes->withQueryString()->links() }}
                    @endif
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
                            'Apakah Anda yakin ingin menghapus quiz ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
