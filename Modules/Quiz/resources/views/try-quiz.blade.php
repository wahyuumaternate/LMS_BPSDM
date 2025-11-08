@extends('layouts.main')

@section('title', 'Uji Coba Quiz: ' . $quiz->judul_quiz)
@section('page-title', 'Uji Coba Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Page Title with Breadcrumbs -->
            <div class="pagetitle mb-3">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quizzes.index') }}">Quiz</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('quizzes.show', $quiz->id) }}">{{ $quiz->judul_quiz }}</a></li>
                        <li class="breadcrumb-item active">Uji Coba</li>
                    </ol>
                </nav>
            </div>

            <!-- Quiz Info Card -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Informasi Quiz <span class="badge bg-warning text-dark">Mode
                                    Instruktur</span></h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-journal-text"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ $quiz->judul_quiz }}</h6>
                                    <div class="d-flex flex-wrap mt-2">
                                        <span class="text-muted small pt-1 me-3"><i class="bi bi-list-ol me-1"></i>
                                            {{ $quiz->soalQuiz->count() }} Soal</span>
                                        <span class="text-muted small pt-1 me-3"><i class="bi bi-clock me-1"></i>
                                            {{ $quiz->durasi_menit }} Menit</span>
                                        <span class="text-muted small pt-1"><i class="bi bi-award me-1"></i> Passing:
                                            {{ $quiz->passing_grade }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timer Card -->
                <div class="col-md-4">
                    <div class="card info-card revenue-card">
                        <div class="card-body">
                            <h5 class="card-title">Waktu Tersisa</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-alarm"></i>
                                </div>
                                <div class="ps-3">
                                    <h6 id="timer-display">
                                        {{ str_pad(floor($quiz->durasi_menit / 60), 2, '0', STR_PAD_LEFT) }}:{{ str_pad($quiz->durasi_menit % 60, 2, '0', STR_PAD_LEFT) }}:00
                                    </h6>
                                    <span class="text-success small pt-1 fw-bold">Waktu Pengerjaan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Quiz Card -->
            <div class="card">
                <div class="card-body pt-3">
                    <!-- Tabs for Quiz Navigation -->
                    <ul class="nav nav-tabs nav-tabs-bordered mb-3">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#quiz-content">Pertanyaan
                                Quiz</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#quiz-navigator">Navigasi
                                Quiz</button>
                        </li>
                        <li class="nav-item ms-auto">
                            <div class="bg-light p-2 rounded">
                                <span class="text-dark">Soal <span id="current-question">1</span> dari
                                    {{ $quiz->soalQuiz->count() }}</span> |
                                <span class="text-success"><i class="bi bi-check-circle-fill"></i> <span
                                        id="answered-count">0</span> Terjawab</span>
                            </div>
                        </li>
                    </ul>

                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h4 class="alert-heading"><i class="bi bi-info-circle me-1"></i> Mode Uji Coba Instruktur</h4>
                        <p>Anda sedang dalam mode uji coba quiz sebagai instruktur. Hasil dari uji coba ini tidak akan
                            disimpan ke database, tetapi Anda dapat melihat hasil dan pembahasan setelah menyelesaikan quiz
                            ini.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <div class="tab-content pt-2">
                        <!-- Quiz Content Tab -->
                        <div class="tab-pane fade show active" id="quiz-content">
                            <!-- Progress bar -->
                            <div class="progress mb-3">
                                <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                                    role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                    aria-valuemax="100"></div>
                            </div>

                            <!-- Quiz Form -->
                            <form id="quiz-form" action="{{ route('quizzes.process-try', $quiz->id) }}" method="POST">
                                @csrf

                                <div id="questions-container">
                                    @foreach ($quiz->soalQuiz as $index => $soal)
                                        <div class="question-card mb-4 {{ $index > 0 ? 'd-none' : '' }}"
                                            data-question-id="{{ $soal->id }}"
                                            data-question-index="{{ $index }}">
                                            <div class="card border-primary">
                                                <div
                                                    class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                    <h5 class="m-0">Soal #{{ $index + 1 }}</h5>
                                                    <span class="badge bg-light text-dark">{{ $soal->poin ?? 1 }}
                                                        Poin</span>
                                                </div>
                                                <div class="card-body">
                                                    <div class="question-text mb-4 p-2 bg-light rounded">
                                                        {!! $soal->pertanyaan !!}
                                                    </div>

                                                    <h6 class="fw-bold mb-3"><i class="bi bi-list-check me-2"></i>Pilihan
                                                        Jawaban:</h6>
                                                    <div class="options-container">
                                                        @foreach ($soal->options as $option)
                                                            <div
                                                                class="form-check option-item mb-3 border rounded p-3 position-relative">
                                                                <input class="form-check-input answer-option" type="radio"
                                                                    name="jawaban[{{ $soal->id }}]"
                                                                    id="option{{ $soal->id }}_{{ $option->id }}"
                                                                    value="{{ $option->id }}"
                                                                    data-question-id="{{ $soal->id }}">
                                                                <label class="form-check-label w-100"
                                                                    for="option{{ $soal->id }}_{{ $option->id }}">
                                                                    <span
                                                                        class="option-label fw-bold me-2">{{ chr(64 + $loop->iteration) }}.</span>
                                                                    {{ $option->teks_opsi }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" id="prev-button" class="btn btn-outline-primary btn-lg"
                                        disabled>
                                        <i class="bi bi-arrow-left"></i> Soal Sebelumnya
                                    </button>
                                    <button type="button" id="next-button" class="btn btn-primary btn-lg">
                                        Soal Berikutnya <i class="bi bi-arrow-right"></i>
                                    </button>
                                    <button type="submit" id="submit-quiz" class="btn btn-success btn-lg d-none">
                                        <i class="bi bi-check-circle"></i> Selesaikan Quiz
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Quiz Navigator Tab -->
                        <div class="tab-pane fade" id="quiz-navigator">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title text-white mb-0">Peta Navigasi Soal</h5>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-3">Klik nomor soal untuk menuju ke soal yang diinginkan. Warna
                                        hijau menandakan soal sudah dijawab.</p>
                                    <div class="question-navigator d-flex flex-wrap gap-2">
                                        @foreach ($quiz->soalQuiz as $index => $soal)
                                            <button type="button" class="btn btn-outline-primary question-nav-btn"
                                                data-question-index="{{ $index }}"
                                                data-question-id="{{ $soal->id }}">
                                                {{ $index + 1 }}
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="mt-4">
                                        <h6 class="fw-bold">Keterangan:</h6>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="d-flex align-items-center">
                                                <span class="btn btn-outline-primary btn-sm me-2"
                                                    style="width: 30px; height: 30px; cursor: default;">?</span>
                                                <span>Belum dijawab</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="btn btn-success btn-sm me-2"
                                                    style="width: 30px; height: 30px; cursor: default;">?</span>
                                                <span>Sudah dijawab</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="btn btn-outline-primary active btn-sm me-2"
                                                    style="width: 30px; height: 30px; cursor: default;">?</span>
                                                <span>Soal aktif</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-danger mt-3">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="card-title text-white mb-0">Petunjuk Quiz</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex align-items-center">
                                            <i class="bi bi-alarm text-danger me-2 fs-5"></i>
                                            <span>Waktu quiz adalah <strong>{{ $quiz->durasi_menit }} menit</strong>. Quiz
                                                akan otomatis dikumpulkan saat waktu habis.</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center">
                                            <i class="bi bi-award text-success me-2 fs-5"></i>
                                            <span>Nilai minimum kelulusan (passing grade) adalah
                                                <strong>{{ $quiz->passing_grade }}%</strong>.</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center">
                                            <i class="bi bi-list-check text-primary me-2 fs-5"></i>
                                            <span>Pastikan semua soal terjawab sebelum mengumpulkan quiz.</span>
                                        </li>
                                        <li class="list-group-item d-flex align-items-center">
                                            <i class="bi bi-arrow-repeat text-warning me-2 fs-5"></i>
                                            <span>Anda dapat menavigasi antar soal menggunakan tombol Sebelumnya/Berikutnya
                                                atau dengan mengklik nomor soal.</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="confirmSubmitModalLabel">Konfirmasi Selesai Quiz</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-question-circle text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Yakin ingin menyelesaikan quiz?</h4>
                    </div>

                    <div id="unanswered-warning" class="alert alert-warning d-none">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-3 me-2"></i>
                            <div>
                                <strong>Perhatian!</strong>
                                <p class="mb-0">Masih ada <span id="unanswered-count" class="fw-bold">0</span> soal
                                    yang belum dijawab.</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted">Setelah quiz diselesaikan, Anda akan melihat hasil dan pembahasan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="button" class="btn btn-success" id="confirm-submit">
                        <i class="bi bi-check2-circle"></i> Ya, Selesaikan Quiz
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Timer Warning Modal -->
    <div class="modal fade" id="timerWarningModal" tabindex="-1" aria-labelledby="timerWarningModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="timerWarningModalLabel">Peringatan Waktu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-clock-history text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-center">Waktu hampir habis!</h4>
                    <p class="text-center">Anda memiliki waktu kurang dari <strong>5 menit</strong> lagi.</p>
                    <p class="text-center mb-0">Pastikan untuk menyelesaikan quiz sebelum waktu habis.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-hourglass"></i> Lanjutkan Quiz
                    </button>
                    <button type="button" class="btn btn-danger" id="timer-submit-now">
                        <i class="bi bi-send-check"></i> Selesaikan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize variables
            const questionsCount = {{ $quiz->soalQuiz->count() }};
            let currentIndex = 0;
            let answeredQuestions = {};
            let confirmSubmitModal;
            let timerWarningModal;

            // Timer variables
            const quizDuration = {{ $quiz->durasi_menit }} * 60; // Convert to seconds
            let timeLeft = quizDuration;
            let timerInterval;
            let warningShown = false;

            // DOM elements
            const questionCards = document.querySelectorAll('.question-card');
            const navButtons = document.querySelectorAll('.question-nav-btn');
            const prevButton = document.getElementById('prev-button');
            const nextButton = document.getElementById('next-button');
            const submitButton = document.getElementById('submit-quiz');
            const answerOptions = document.querySelectorAll('.answer-option');
            const progressBar = document.getElementById('progress-bar');
            const currentQuestionSpan = document.getElementById('current-question');
            const answeredCountSpan = document.getElementById('answered-count');
            const timerDisplay = document.getElementById('timer-display');
            const confirmSubmitBtn = document.getElementById('confirm-submit');
            const timerSubmitNowBtn = document.getElementById('timer-submit-now');
            const quizForm = document.getElementById('quiz-form');

            // Initialize modals
            confirmSubmitModal = new bootstrap.Modal(document.getElementById('confirmSubmitModal'));
            timerWarningModal = new bootstrap.Modal(document.getElementById('timerWarningModal'));

            // Initialize quiz navigation
            function showQuestion(index) {
                // Hide all questions
                questionCards.forEach(card => card.classList.add('d-none'));

                // Show the current question
                questionCards[index].classList.remove('d-none');

                // Update current question indicator
                currentQuestionSpan.textContent = index + 1;

                // Update buttons
                prevButton.disabled = index === 0;

                if (index === questionsCount - 1) {
                    nextButton.classList.add('d-none');
                    submitButton.classList.remove('d-none');
                } else {
                    nextButton.classList.remove('d-none');
                    submitButton.classList.add('d-none');
                }

                // Update navigation buttons
                navButtons.forEach(btn => {
                    btn.classList.remove('active');
                    if (parseInt(btn.dataset.questionIndex) === index) {
                        btn.classList.add('active');
                    }
                });

                // Update current index
                currentIndex = index;
            }

            // Update progress
            function updateProgress() {
                const answeredCount = Object.keys(answeredQuestions).length;
                const progress = (answeredCount / questionsCount) * 100;

                progressBar.style.width = `${progress}%`;
                progressBar.setAttribute('aria-valuenow', progress);
                answeredCountSpan.textContent = answeredCount;

                // Add proper Bootstrap classes based on progress
                if (progress < 25) {
                    progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-danger';
                } else if (progress < 75) {
                    progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
                } else if (progress < 100) {
                    progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-info';
                } else {
                    progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-success';
                }

                // Update nav buttons
                navButtons.forEach(btn => {
                    const qId = btn.dataset.questionId;
                    if (answeredQuestions[qId]) {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-success');
                    }
                });
            }

            // Check answered questions on load (in case of page refresh)
            function checkAnsweredOnLoad() {
                answerOptions.forEach(option => {
                    if (option.checked) {
                        const questionId = option.dataset.questionId;
                        answeredQuestions[questionId] = true;
                    }
                });
                updateProgress();
            }

            // Timer function
            function startTimer() {
                timerInterval = setInterval(function() {
                    timeLeft--;

                    const hours = Math.floor(timeLeft / 3600);
                    const minutes = Math.floor((timeLeft % 3600) / 60);
                    const seconds = timeLeft % 60;

                    timerDisplay.textContent =
                        `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                    // Timer color changes
                    const timerCard = document.querySelector('.revenue-card');
                    const timerIcon = document.querySelector('.revenue-card .card-icon');

                    if (timeLeft <= 300) { // 5 minutes or less
                        timerCard.classList.add('border-danger');
                        timerCard.querySelector('.card-icon').classList.add('bg-danger');
                        timerDisplay.classList.add('text-danger');
                        timerDisplay.classList.add('fw-bold');
                    } else if (timeLeft <= 600) { // 10 minutes or less
                        timerCard.classList.add('border-warning');
                        timerCard.querySelector('.card-icon').classList.add('bg-warning');
                        timerDisplay.classList.add('text-warning');
                    }

                    // Show warning when 5 minutes left
                    if (timeLeft === 300 && !warningShown) {
                        timerWarningModal.show();
                        warningShown = true;
                    }

                    // Auto submit when time is up
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        submitQuiz();
                    }
                }, 1000);
            }

            // Submit quiz
            function submitQuiz() {
                quizForm.submit();
            }

            // Animated options highlight
            function setupOptionsHighlight() {
                document.querySelectorAll('.option-item').forEach(item => {
                    item.addEventListener('mouseenter', function() {
                        if (!this.querySelector('input').checked) {
                            this.classList.add('shadow-sm', 'bg-light');
                        }
                    });

                    item.addEventListener('mouseleave', function() {
                        if (!this.querySelector('input').checked) {
                            this.classList.remove('shadow-sm', 'bg-light');
                        }
                    });
                });
            }

            // Update options highlighting when selected
            function updateOptionsHighlight() {
                document.querySelectorAll('.option-item').forEach(item => {
                    const input = item.querySelector('input');

                    if (input.checked) {
                        item.classList.add('shadow', 'border-primary', 'bg-light');
                    } else {
                        item.classList.remove('shadow', 'border-primary', 'bg-light');
                    }
                });
            }

            // Event listeners
            prevButton.addEventListener('click', function() {
                if (currentIndex > 0) {
                    showQuestion(currentIndex - 1);
                }
            });

            nextButton.addEventListener('click', function() {
                if (currentIndex < questionsCount - 1) {
                    showQuestion(currentIndex + 1);
                }
            });

            navButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.questionIndex);
                    showQuestion(index);

                    // If we're in the navigator tab, switch back to questions tab
                    const questionsTab = document.querySelector(
                        'button[data-bs-target="#quiz-content"]');
                    if (questionsTab) {
                        new bootstrap.Tab(questionsTab).show();
                    }
                });
            });

            answerOptions.forEach(option => {
                option.addEventListener('change', function() {
                    const questionId = this.dataset.questionId;
                    answeredQuestions[questionId] = true;
                    updateProgress();
                    updateOptionsHighlight();
                });
            });

            submitButton.addEventListener('click', function(e) {
                e.preventDefault();

                // Check for unanswered questions
                const unansweredCount = questionsCount - Object.keys(answeredQuestions).length;
                const unansweredWarning = document.getElementById('unanswered-warning');
                const unansweredCountSpan = document.getElementById('unanswered-count');

                if (unansweredCount > 0) {
                    unansweredWarning.classList.remove('d-none');
                    unansweredCountSpan.textContent = unansweredCount;
                } else {
                    unansweredWarning.classList.add('d-none');
                }

                confirmSubmitModal.show();
            });

            confirmSubmitBtn.addEventListener('click', function() {
                confirmSubmitModal.hide();
                submitQuiz();
            });

            timerSubmitNowBtn.addEventListener('click', function() {
                timerWarningModal.hide();
                submitQuiz();
            });

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft' && !prevButton.disabled) {
                    prevButton.click();
                } else if (e.key === 'ArrowRight' && !nextButton.classList.contains('d-none')) {
                    nextButton.click();
                }
            });

            // Initialize
            showQuestion(0);
            checkAnsweredOnLoad();
            updateProgress();
            startTimer();
            setupOptionsHighlight();
            updateOptionsHighlight();

            // Mark first nav button as active
            if (navButtons.length > 0) {
                navButtons[0].classList.add('active');
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Quiz Navigator */
        .question-navigator .btn {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .question-navigator .btn:hover {
            transform: scale(1.05);
            z-index: 1;
        }

        .question-navigator .btn.active {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.5);
        }

        /* Timer */
        #quiz-timer {
            font-size: 1.2rem;
            font-weight: bold;
        }

        /* Questions */
        .question-text {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .question-text img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 10px auto;
        }

        /* Options styling */
        .options-container {
            padding-left: 0.5rem;
        }

        .option-item {
            transition: all 0.2s ease-in-out;
            border: 1px solid #dee2e6;
        }

        .option-item:hover {
            background-color: #f8f9fa;
        }

        .option-label {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            background-color: #f0f0f0;
            color: #333;
        }

        .form-check-input {
            margin-top: 0.3rem;
        }

        .form-check-input:checked+.form-check-label .option-label {
            background-color: #0d6efd;
            color: white;
        }

        .form-check-input:checked+.form-check-label {
            font-weight: 500;
            color: #0d6efd;
        }

        /* Tab Navigation */
        .nav-tabs-bordered .nav-link.active {
            font-weight: bold;
        }

        /* Animation for timer warning */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .text-danger.fw-bold {
            animation: pulse 1s infinite;
        }
    </style>
@endpush
