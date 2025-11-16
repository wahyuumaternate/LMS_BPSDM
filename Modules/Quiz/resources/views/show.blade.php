@extends('layouts.main')

@section('title', 'Detail Quiz')
@section('page-title', 'Detail Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Quiz Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> {{ $quiz->judul_quiz }}
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('quizzes.try', $quiz->id) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-play-circle"></i> Uji Coba
                            </a>
                            <a href="{{ route('course.kuis', $quiz->modul->kursus_id) }}" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3">Informasi Quiz</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Modul</label>
                                        <p class="mb-0 fw-bold">{{ $quiz->modul->nama_modul ?? 'N/A' }}</p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Durasi</label>
                                        <p class="mb-0">
                                            @if ($quiz->durasi_menit > 0)
                                                <span class="badge bg-info">{{ $quiz->durasi_menit }} Menit</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Terbatas</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Bobot Nilai</label>
                                        <p class="mb-0">{{ $quiz->bobot_nilai ?? '-' }}%</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Passing Grade</label>
                                        <p class="mb-0">
                                            <span class="badge bg-success">{{ $quiz->passing_grade }}%</span>
                                        </p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Maksimal Percobaan</label>
                                        <p class="mb-0">
                                            @if ($quiz->max_attempt > 0)
                                                <span class="badge bg-warning">{{ $quiz->max_attempt }}x</span>
                                            @else
                                                <span class="badge bg-secondary">Unlimited</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Jumlah Soal Ditampilkan</label>
                                        <p class="mb-0">
                                            @if ($quiz->jumlah_soal > 0)
                                                {{ $quiz->jumlah_soal }} Soal
                                            @else
                                                Semua Soal
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if ($quiz->deskripsi)
                                <div class="info-item">
                                    <label class="text-muted small">Deskripsi</label>
                                    <p class="mb-0">{{ $quiz->deskripsi }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-muted mb-3">Pengaturan</h6>
                            <div class="list-group">
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Acak Urutan Soal</span>
                                    @if ($quiz->random_soal)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Tampilkan Hasil</span>
                                    @if ($quiz->tampilkan_hasil)
                                        <span class="badge bg-success">Ya</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Status Publikasi</span>
                                    @if ($quiz->is_published)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-warning">Draft</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Soal Quiz Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-check"></i> Daftar Soal
                            <span class="badge bg-primary">{{ $quiz->questions->count() }} Soal</span>
                        </h5>
                        <div class="btn-group">
                            <a href="{{ route('soal-quiz.create', ['quiz_id' => $quiz->id]) }}"
                                class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle"></i> Tambah Soal
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#bulkCreateModal">
                                <i class="bi bi-file-earmark-plus"></i> Tambah Soal Masal
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if ($quiz->questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="60%">Pertanyaan</th>
                                        <th width="10%">Poin</th>
                                        <th width="10%">Opsi</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($quiz->questions as $index => $question)
                                        <tr>
                                            <td class="fw-bold">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="question-text">
                                                    {!! Str::limit(strip_tags($question->pertanyaan), 150) !!}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $question->poin ?? 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $question->options->count() }} opsi</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('soal-quiz.show', $question->id) }}"
                                                        class="btn btn-sm btn-info" title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    <a href="{{ route('soal-quiz.edit', $question->id) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <form action="{{ route('soal-quiz.destroy', $question->id) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            title="Hapus Soal">
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
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">Belum Ada Soal</h5>
                            <p class="text-muted">Silakan tambahkan soal untuk quiz ini.</p>
                            <div class="mt-3">
                                <a href="{{ route('soal-quiz.create', ['quiz_id' => $quiz->id]) }}"
                                    class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> Tambah Soal Pertama
                                </a>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#bulkCreateModal">
                                    <i class="bi bi-file-earmark-plus"></i> Tambah Soal Masal
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Create Modal -->
    <div class="modal fade" id="bulkCreateModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-plus"></i> Tambah Soal Masal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('soal-quiz.store-bulk') }}" method="POST" id="bulkCreateForm">
                    @csrf
                    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Petunjuk:</strong> Tambahkan soal dengan mengklik tombol "Tambah Soal Baru".
                        </div>

                        <div id="questions-container"></div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary" id="add-question">
                                <i class="bi bi-plus-circle"></i> Tambah Soal Baru
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submit-button">
                            <i class="bi bi-save"></i> Simpan Semua Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <template id="question-template">
        <div class="question-panel card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Soal #<span class="question-number"></span></h6>
                <button type="button" class="btn-close remove-question"></button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="soal[INDEX][pertanyaan]" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Poin</label>
                    <input type="number" class="form-control" name="soal[INDEX][poin]" value="1" min="1">
                </div>

                <hr>

                <h6>Pilihan Jawaban <span class="text-danger">*</span></h6>
                <div class="options-container"></div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary add-option">
                        <i class="bi bi-plus"></i> Tambah Opsi
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option" disabled>
                        <i class="bi bi-dash"></i> Hapus Opsi
                    </button>
                </div>
            </div>
        </div>
    </template>

    <template id="option-template">
        <div class="option-row input-group mb-2">
            <span class="input-group-text option-label fw-bold" style="min-width: 50px;">A</span>
            <input type="text" class="form-control" name="soal[Q_INDEX][options][OPT_INDEX][teks_opsi]"
                placeholder="Masukkan pilihan jawaban" required>
            <input type="hidden" name="soal[Q_INDEX][options][OPT_INDEX][urutan]" value="">
            <div class="input-group-text">
                <input class="form-check-input mt-0 me-2 correct-answer" type="radio"
                    name="soal[Q_INDEX][jawaban_benar]" value="OPT_INDEX" required>
                <span>Benar</span>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let questionCount = 0;
        const labels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        function addQuestion() {
            const idx = questionCount;
            const template = document.getElementById('question-template');
            const clone = template.content.cloneNode(true);
            const panel = clone.querySelector('.question-panel');

            // Set question number
            panel.querySelector('.question-number').textContent = idx + 1;

            // Replace INDEX in all names
            panel.querySelectorAll('[name*="INDEX"]').forEach(el => {
                el.name = el.name.replace(/INDEX/g, idx);
            });

            const container = panel.querySelector('.options-container');

            // Add 4 initial options
            for (let i = 0; i < 4; i++) {
                addOption(container, idx, i, i === 0);
            }

            // Remove question button
            panel.querySelector('.remove-question').addEventListener('click', function() {
                panel.remove();
                renumberQuestions();
                questionCount--;
                updateSubmitButton();
            });

            // Add option button
            panel.querySelector('.add-option').addEventListener('click', function() {
                const optCount = container.querySelectorAll('.option-row').length;
                if (optCount < 10) {
                    addOption(container, idx, optCount);
                    updateRemoveButton(container, panel);
                } else {
                    alert('Maksimal 10 opsi');
                }
            });

            // Remove option button
            panel.querySelector('.remove-option').addEventListener('click', function() {
                const opts = container.querySelectorAll('.option-row');
                if (opts.length > 2) {
                    container.removeChild(opts[opts.length - 1]);
                    ensureChecked(container);
                    updateRemoveButton(container, panel);
                    reindexOptions(container, idx);
                }
            });

            document.getElementById('questions-container').appendChild(panel);
            questionCount++;
            updateSubmitButton();
            updateRemoveButton(container, panel);
        }

        function addOption(container, qIdx, oIdx, checked = false) {
            const template = document.getElementById('option-template');
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.option-row');

            row.querySelector('.option-label').textContent = labels[oIdx] || (oIdx + 1);

            const textInput = row.querySelector('input[type="text"]');
            textInput.name = textInput.name.replace('Q_INDEX', qIdx).replace('OPT_INDEX', oIdx);

            const hidden = row.querySelector('input[type="hidden"]');
            hidden.name = hidden.name.replace('Q_INDEX', qIdx).replace('OPT_INDEX', oIdx);
            hidden.value = oIdx + 1;

            const radio = row.querySelector('.correct-answer');
            radio.name = radio.name.replace('Q_INDEX', qIdx);
            radio.value = oIdx;
            if (checked) radio.checked = true;

            container.appendChild(row);
        }

        function reindexOptions(container, qIdx) {
            container.querySelectorAll('.option-row').forEach((row, idx) => {
                row.querySelector('.option-label').textContent = labels[idx] || (idx + 1);

                const textInput = row.querySelector('input[type="text"]');
                const oldName = textInput.name;
                textInput.name = `soal[${qIdx}][options][${idx}][teks_opsi]`;

                const hidden = row.querySelector('input[type="hidden"]');
                hidden.name = `soal[${qIdx}][options][${idx}][urutan]`;
                hidden.value = idx + 1;

                const radio = row.querySelector('.correct-answer');
                radio.value = idx;
            });
        }

        function ensureChecked(container) {
            const radios = container.querySelectorAll('.correct-answer');
            const hasChecked = Array.from(radios).some(r => r.checked);
            if (!hasChecked && radios.length > 0) {
                radios[0].checked = true;
            }
        }

        function updateRemoveButton(container, panel) {
            const count = container.querySelectorAll('.option-row').length;
            panel.querySelector('.remove-option').disabled = count <= 2;
        }

        function renumberQuestions() {
            document.querySelectorAll('.question-panel').forEach((panel, idx) => {
                panel.querySelector('.question-number').textContent = idx + 1;
            });
        }

        function updateSubmitButton() {
            document.getElementById('submit-button').disabled = questionCount === 0;
        }

        // Events
        document.getElementById('add-question').addEventListener('click', addQuestion);

        document.getElementById('bulkCreateForm').addEventListener('submit', function(e) {
            if (questionCount === 0) {
                e.preventDefault();
                alert('Minimal 1 soal harus ditambahkan!');
                return false;
            }

            const panels = document.querySelectorAll('.question-panel');
            for (let i = 0; i < panels.length; i++) {
                const container = panels[i].querySelector('.options-container');
                const checked = container.querySelectorAll('.correct-answer:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert(`Soal #${i + 1} harus memiliki jawaban benar!`);
                    return false;
                }
            }
        });

        // Modal events
        const modal = document.getElementById('bulkCreateModal');
        modal.addEventListener('shown.bs.modal', function() {
            if (questionCount === 0) addQuestion();
        });

        modal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('questions-container').innerHTML = '';
            questionCount = 0;
            updateSubmitButton();
        });
    </script>
@endpush

@push('styles')
    <style>
        .info-item label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .question-text {
            line-height: 1.5;
        }

        .question-panel {
            border-left: 4px solid #0d6efd;
        }
    </style>
@endpush
