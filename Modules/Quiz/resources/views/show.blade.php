@extends('layouts.main')

@section('title', 'Detail Quiz')
@section('page-title', 'Detail Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Quiz</h5>

                        <div class="action-buttons mb-3">
                            <a href="{{ route('quizzes.edit', $quiz->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Quiz
                            </a>
                            <a href="{{ route('quizzes.try', $quiz->id) }}" class="btn btn-success">
                                <i class="bi bi-play-circle"></i> Uji Coba Quiz
                            </a>
                            <a href="{{ route('quizzes.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th style="width: 30%">Modul</th>
                                    <td>{{ $quiz->modul->nama_modul ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Judul Quiz</th>
                                    <td>{{ $quiz->judul_quiz }}</td>
                                </tr>
                                <tr>
                                    <th>Deskripsi</th>
                                    <td>{{ $quiz->deskripsi ?? 'Tidak ada deskripsi' }}</td>
                                </tr>
                                <tr>
                                    <th>Durasi</th>
                                    <td>{{ $quiz->durasi_menit ?? '-' }} {{ $quiz->durasi_menit ? 'menit' : '' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th style="width: 30%">Jumlah Soal</th>
                                    <td>{{ $quiz->jumlah_soal }}</td>
                                </tr>
                                <tr>
                                    <th>Bobot Nilai</th>
                                    <td>{{ $quiz->bobot_nilai }}</td>
                                </tr>
                                <tr>
                                    <th>Passing Grade</th>
                                    <td>{{ $quiz->passing_grade }}%</td>
                                </tr>
                                <tr>
                                    <th>Maksimal Percobaan</th>
                                    <td>{{ $quiz->max_attempt ? $quiz->max_attempt : 'Tidak terbatas' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Pengaturan Tambahan:</h6>
                                <ul class="mb-0">
                                    <li>Acak Urutan Soal: {{ $quiz->random_soal ? 'Ya' : 'Tidak' }}</li>
                                    <li>Tampilkan Hasil Setelah Selesai: {{ $quiz->tampilkan_hasil ? 'Ya' : 'Tidak' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Soal Quiz Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Daftar Soal</h5>
                                <a href="{{ route('soal-quiz.create', ['quiz_id' => $quiz->id]) }}"
                                    class="btn btn-sm btn-success">
                                    <i class="bi bi-plus-circle"></i> Tambah Soal
                                </a>
                            </div>

                            @if (count($quiz->soalQuiz) > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>Pertanyaan</th>
                                                <th>Tipe Soal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($quiz->soalQuiz as $index => $soal)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        {!! Str::limit(strip_tags($soal->pertanyaan), 100) !!}
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ ucfirst($soal->tipe_soal) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('soal-quiz.edit', $soal->id) }}"
                                                                class="btn btn-sm btn-warning">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="{{ route('soal-quiz.show', $soal->id) }}"
                                                                class="btn btn-sm btn-info">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <form action="{{ route('soal-quiz.destroy', $soal->id) }}"
                                                                method="POST" class="d-inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    Belum ada soal untuk quiz ini. Silahkan tambahkan soal baru.
                                </div>
                            @endif
                        </div>
                    </div>
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
                            'Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
