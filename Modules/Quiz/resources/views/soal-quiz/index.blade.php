@extends('layouts.main')

@section('title', 'Daftar Soal Quiz')
@section('page-title', 'Daftar Soal Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Soal Quiz</h5>
                        <div>
                            <a href="{{ route('soal-quiz.create', request()->query()) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Soal
                            </a>
                            <a href="{{ route('soal-quiz.create-bulk', request()->query()) }}" class="btn btn-success">
                                <i class="bi bi-plus-circle-dotted"></i> Tambah Multiple Soal
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('soal-quiz.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="quiz_id" class="form-label">Filter berdasarkan Quiz</label>
                            <select class="form-select" name="quiz_id" id="quiz_id" onchange="this.form.submit()">
                                <option value="">-- Semua Quiz --</option>
                                @foreach (\Modules\Quiz\Entities\Quiz::orderBy('judul_quiz')->get() as $quiz)
                                    <option value="{{ $quiz->id }}"
                                        {{ request('quiz_id') == $quiz->id ? 'selected' : '' }}>
                                        {{ $quiz->judul_quiz }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tingkat_kesulitan" class="form-label">Filter berdasarkan Tingkat Kesulitan</label>
                            <select class="form-select" name="tingkat_kesulitan" id="tingkat_kesulitan"
                                onchange="this.form.submit()">
                                <option value="">-- Semua Level --</option>
                                @foreach (['mudah', 'sedang', 'sulit'] as $level)
                                    <option value="{{ $level }}"
                                        {{ request('tingkat_kesulitan') == $level ? 'selected' : '' }}>
                                        {{ ucfirst($level) }}
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
                                    <th scope="col">Quiz</th>
                                    <th scope="col">Pertanyaan</th>
                                    <th scope="col">Tingkat Kesulitan</th>
                                    <th scope="col">Poin</th>
                                    <th scope="col">Jml Opsi</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($soalQuizzes as $key => $soal)
                                    <tr>
                                        <th scope="row">{{ $soalQuizzes->firstItem() + $key }}</th>
                                        <td>{{ $soal->quiz->judul_quiz ?? 'N/A' }}</td>
                                        <td>
                                            {!! Str::limit(strip_tags($soal->pertanyaan), 50) !!}
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $soal->tingkat_kesulitan == 'mudah'
                                                    ? 'success'
                                                    : ($soal->tingkat_kesulitan == 'sedang'
                                                        ? 'warning'
                                                        : 'danger') }}">
                                                {{ ucfirst($soal->tingkat_kesulitan) }}
                                            </span>
                                        </td>
                                        <td>{{ $soal->poin }}</td>
                                        <td>{{ $soal->options->count() }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('soal-quiz.show', $soal->id) }}">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('soal-quiz.edit', $soal->id) }}">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a></li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('soal-quiz.validate-options', $soal->id) }}">
                                                            <i class="bi bi-check-circle"></i> Validasi Jawaban
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('soal-quiz.destroy', $soal->id) }}"
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
                                        <td colspan="7" class="text-center">Tidak ada data soal quiz</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $soalQuizzes->withQueryString()->links() }}
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
                            'Apakah Anda yakin ingin menghapus soal quiz ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
