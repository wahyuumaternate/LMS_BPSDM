@extends('layouts.main')

@section('title', 'Simulasi Ujian')
@section('page-title', 'Simulasi Ujian')

@push('css')
    <style>
        .timer-container {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: #fff;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .question-navigation {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 20px;
        }

        .question-nav-button {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }

        .question-nav-button.active {
            background-color: #0d6efd;
            color: #fff;
            font-weight: bold;
        }

        .question-nav-button.answered {
            background-color: #198754;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Timer Container -->
            <div class="timer-container">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $ujian->judul_ujian }} (SIMULASI)</h5>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Sisa Waktu:</span>
                        <div id="timer" class="badge bg-danger fs-6">00:00:00</div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill"></i> Ini adalah mode simulasi ujian untuk
                        admin/instruktur. Hasil simulasi akan disimpan tetapi ditandai sebagai simulasi.
                    </div>

                    <form id="ujian-form" action="{{ route('ujians.process-simulation', $ujian->id) }}" method="POST">
                        @csrf

                        <!-- Question navigation -->
                        <div class="question-navigation mb-4">
                            @foreach ($soalUjians as $index => $soal)
                                <div class="question-nav-button" data-question="{{ $index + 1 }}">
                                    {{ $index + 1 }}
                                </div>
                            @endforeach
                        </div>

                        <!-- Questions -->
                        @foreach ($soalUjians as $index => $soal)
                            <div class="question-section" id="question-{{ $index + 1 }}"
                                style="{{ $index > 0 ? 'display: none;' : '' }}">
                                <div class="mb-4">
                                    <h5 class="card-title">Soal {{ $index + 1 }} dari {{ $soalUjians->count() }}</h5>
                                    <span
                                        class="badge bg-{{ $soal->getTypeBadgeClass() }} mb-2">{{ $soal->getFormattedType() }}</span>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="question-text">{!! $soal->pertanyaan !!}</div>
                                        </div>
                                    </div>
                                </div>

                                @if ($soal->tipe_soal == 'pilihan_ganda')
                                    <div class="mb-3">
                                        <label class="form-label">Pilihan Jawaban:</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input answer-input" type="radio"
                                                name="jawaban_{{ $soal->id }}" id="pg-a-{{ $soal->id }}"
                                                value="A">
                                            <label class="form-check-label" for="pg-a-{{ $soal->id }}">
                                                A. {!! $soal->pilihan_a !!}
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input answer-input" type="radio"
                                                name="jawaban_{{ $soal->id }}" id="pg-b-{{ $soal->id }}"
                                                value="B">
                                            <label class="form-check-label" for="pg-b-{{ $soal->id }}">
                                                B. {!! $soal->pilihan_b !!}
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input answer-input" type="radio"
                                                name="jawaban_{{ $soal->id }}" id="pg-c-{{ $soal->id }}"
                                                value="C">
                                            <label class="form-check-label" for="pg-c-{{ $soal->id }}">
                                                C. {!! $soal->pilihan_c !!}
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input answer-input" type="radio"
                                                name="jawaban_{{ $soal->id }}" id="pg-d-{{ $soal->id }}"
                                                value="D">
                                            <label class="form-check-label" for="pg-d-{{ $soal->id }}">
                                                D. {!! $soal->pilihan_d !!}
                                            </label>
                                        </div>
                                    </div>
                                @elseif($soal->tipe_soal == 'benar_salah')
                                    <div class="mb-3">
                                        <label class="form-label">Pilihan Jawaban:</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input answer-input" type="radio"
                                                name="jawaban_{{ $soal->id }}" id="bs-1-{{ $soal->id }}"
                                                value="Benar">
                                            <label class="form-check-label" for="bs-1-{{ $soal->id }}">
                                                Benar
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input answer-input" type="radio"
                                                name="jawaban_{{ $soal->id }}" id="bs-2-{{ $soal->id }}"
                                                value="Salah">
                                            <label class="form-check-label" for="bs-2-{{ $soal->id }}">
                                                Salah
                                            </label>
                                        </div>
                                    </div>
                                @elseif($soal->tipe_soal == 'essay')
                                    <div class="mb-3">
                                        <label class="form-label">Jawaban:</label>
                                        <textarea class="form-control answer-input" name="jawaban_{{ $soal->id }}" rows="4"
                                            placeholder="Tulis jawaban Anda di sini..."></textarea>
                                    </div>
                                @endif

                                <div class="mt-4 d-flex justify-content-between">
                                    @if ($index > 0)
                                        <button type="button" class="btn btn-secondary btn-prev"
                                            data-prev="{{ $index }}">
                                            <i class="bi bi-arrow-left"></i> Sebelumnya
                                        </button>
                                    @else
                                        <div></div>
                                    @endif

                                    @if ($index < $soalUjians->count() - 1)
                                        <button type="button" class="btn btn-primary btn-next"
                                            data-next="{{ $index + 2 }}">
                                            Selanjutnya <i class="bi bi-arrow-right"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#submitConfirmationModal">
                                            <i class="bi bi-check-circle"></i> Selesai
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <!-- Submit Confirmation Modal -->
                        <div class="modal fade" id="submitConfirmationModal" tabindex="-1"
                            aria-labelledby="submitConfirmationModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="submitConfirmationModalLabel">Konfirmasi Submit Ujian
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin menyelesaikan ujian ini?</p>
                                        <p>Jawaban yang belum diisi akan dianggap kosong.</p>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> Ini adalah mode simulasi. Hasil Anda akan
                                            disimpan sebagai data simulasi.
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kembali
                                            ke Ujian</button>
                                        <button type="submit" class="btn btn-success">Ya, Selesaikan Ujian</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Timer functionality
            const timerElement = document.getElementById('timer');
            let timeLeft = {{ $sisa }};

            function updateTimer() {
                const hours = Math.floor(timeLeft / 3600);
                const minutes = Math.floor((timeLeft % 3600) / 60);
                const seconds = timeLeft % 60;

                timerElement.textContent =
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Waktu habis! Ujian akan otomatis diselesaikan.');
                    document.getElementById('ujian-form').submit();
                } else {
                    timeLeft--;
                }
            }

            // Initial call and set interval
            updateTimer();
            const timerInterval = setInterval(updateTimer, 1000);

            // Question navigation
            const navButtons = document.querySelectorAll('.question-nav-button');
            const questionSections = document.querySelectorAll('.question-section');

            navButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const questionNumber = this.dataset.question;
                    showQuestion(questionNumber);
                });
            });

            function showQuestion(number) {
                questionSections.forEach(section => {
                    section.style.display = 'none';
                });

                document.getElementById(`question-${number}`).style.display = 'block';

                // Update active button
                navButtons.forEach(button => {
                    button.classList.remove('active');
                    if (button.dataset.question == number) {
                        button.classList.add('active');
                    }
                });
            }

            // Mark questions as answered
            const answerInputs = document.querySelectorAll('.answer-input');

            answerInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const questionId = this.getAttribute('name').split('_')[1];
                    const questionIndex = Array.from(questionSections).findIndex(section => section
                        .id.includes(questionId));

                    if (questionIndex !== -1) {
                        const navButton = navButtons[questionIndex];
                        navButton.classList.add('answered');
                    }
                });
            });

            // Previous and Next buttons
            const prevButtons = document.querySelectorAll('.btn-prev');
            const nextButtons = document.querySelectorAll('.btn-next');

            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const prevQuestionNumber = this.dataset.prev;
                    showQuestion(prevQuestionNumber);
                });
            });

            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const nextQuestionNumber = this.dataset.next;
                    showQuestion(nextQuestionNumber);
                });
            });

            // Set first question as active initially
            showQuestion(1);
        });
    </script>
@endpush
