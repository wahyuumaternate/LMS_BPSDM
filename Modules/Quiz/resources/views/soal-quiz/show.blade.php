@extends('layouts.main')

@section('title', 'Detail Soal Quiz')
@section('page-title', 'Detail Soal Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Detail Soal Quiz</h5>
                        <div>
                            <a href="{{ route('soal-quiz.edit', $soalQuiz->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Soal
                            </a>
                            <a href="{{ route('quizzes.show', $soalQuiz->quiz_id) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Quiz
                            </a>

                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('validation_result'))
                        @php $result = session('validation_result'); @endphp
                        <div class="alert alert-{{ $result['is_valid'] ? 'success' : 'danger' }} alert-dismissible fade show"
                            role="alert">
                            <strong>{{ $result['status'] == 'valid' ? 'Validasi Berhasil!' : 'Validasi Gagal!' }}</strong>
                            {{ $result['message'] }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th style="width: 30%">Quiz</th>
                                    <td>{{ $soalQuiz->quiz->judul_quiz ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Tingkat Kesulitan</th>
                                    <td>
                                        <span
                                            class="badge bg-{{ $soalQuiz->tingkat_kesulitan == 'mudah'
                                                ? 'success'
                                                : ($soalQuiz->tingkat_kesulitan == 'sedang'
                                                    ? 'warning'
                                                    : 'danger') }}">
                                            {{ ucfirst($soalQuiz->tingkat_kesulitan) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Poin</th>
                                    <td>{{ $soalQuiz->poin }}</td>
                                </tr>
                                <tr>
                                    <th>Dibuat Pada</th>
                                    <td>{{ $soalQuiz->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4 mb-4">
                        <h5>Pertanyaan:</h5>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($soalQuiz->pertanyaan)) !!}
                        </div>
                    </div>

                    @if ($soalQuiz->pembahasan)
                        <div class="mt-4 mb-4">
                            <h5>Pembahasan:</h5>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($soalQuiz->pembahasan)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 mb-4">
                        <h5>Opsi Jawaban:</h5>
                        <div class="list-group">
                            @foreach ($soalQuiz->options as $index => $option)
                                <div
                                    class="list-group-item list-group-item-action {{ $option->is_jawaban_benar ? 'list-group-item-success' : '' }}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Opsi
                                            {{ $index + 1 }}{{ $option->is_jawaban_benar ? ' (Jawaban Benar)' : '' }}
                                        </h6>
                                        <small>Urutan: {{ $option->urutan }}</small>
                                    </div>
                                    <p class="mb-1">{{ $option->teks_opsi }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        <form action="{{ route('soal-quiz.destroy', $soalQuiz->id) }}" method="POST"
                            class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Hapus Soal Quiz
                            </button>
                        </form>
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
            const deleteForm = document.querySelector('.delete-form');
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm(
                        'Apakah Anda yakin ingin menghapus soal quiz ini? Tindakan ini tidak dapat dibatalkan.'
                    )) {
                    this.submit();
                }
            });
        });
    </script>
@endpush
