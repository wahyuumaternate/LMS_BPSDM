@extends('layouts.main')

@section('title', 'Simulasi Ujian')
@section('page-title', 'Simulasi Ujian: ' . $ujian->judul_ujian)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Ujian Info -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="alert-heading">Info Ujian</h6>
                                <p class="mb-1"><strong>Judul Ujian:</strong> {{ $ujian->judul_ujian }}</p>
                                <p class="mb-1"><strong>Kursus:</strong> {{ $ujian->kursus->nama_kursus }}</p>
                                <p class="mb-1"><strong>Total Soal:</strong> {{ $soalUjians->count() }}</p>
                                <p class="mb-0"><strong>Passing Grade:</strong> {{ $ujian->passing_grade }}%</p>
                            </div>
                            <div class="text-center">
                                <h5>Sisa Waktu</h5>
                                <div id="countdown" class="h4 text-danger">{{ gmdate('H:i:s', $sisa) }}</div>
                            </div>
                        </div>
                    </div>

                    <form id="exam-form" action="{{ route('ujians.process-simulation', $ujian->id) }}" method="POST">
                        @csrf
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-4">
                                    <div class="btn-group mb-3" role="group" aria-label="Navigasi soal">
                                        @foreach ($soalUjians as $index => $soal)
                                            <button type="button" class="btn btn-outline-primary soal-nav"
                                                data-soal="{{ $index + 1 }}">{{ $index + 1 }}</button>
                                        @endforeach
                                    </div>

                                    <div class="d-flex mt-2">
                                        <div class="me-3">
                                            <span class="badge bg-success">■</span> Terjawab
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary">■</span> Belum terjawab
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach ($soalUjians as $index => $soal)
                            <div class="card mb-4 soal-card" id="soal-{{ $index + 1 }}"
                                style="{{ $index > 0 ? 'display:none;' : '' }}">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Soal {{ $index + 1 }} dari {{ $soalUjians->count() }}</h5>
                                    <div
                                        class="badge bg-{{ $soal->tingkat_kesulitan == 'mudah' ? 'success' : ($soal->tingkat_kesulitan == 'sedang' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($soal->tingkat_kesulitan) }} ({{ $soal->poin }} poin)
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        {!! $soal->pertanyaan !!}
                                    </div>

                                    @if ($soal->tipe_soal == 'pilihan_ganda')
                                        <div class="mt-3 mb-3">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="jawaban_{{ $soal->id }}" id="jawaban_{{ $soal->id }}_a"
                                                    value="A">
                                                <label class="form-check-label" for="jawaban_{{ $soal->id }}_a">
                                                    A. {!! $soal->pilihan_a !!}
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="jawaban_{{ $soal->id }}" id="jawaban_{{ $soal->id }}_b"
                                                    value="B">
                                                <label class="form-check-label" for="jawaban_{{ $soal->id }}_b">
                                                    B. {!! $soal->pilihan_b !!}
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="jawaban_{{ $soal->id }}" id="jawaban_{{ $soal->id }}_c"
                                                    value="C">
                                                <label class="form-check-label" for="jawaban_{{ $soal->id }}_c">
                                                    C. {!! $soal->pilihan_c !!}
                                                </label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="jawaban_{{ $soal->id }}" id="jawaban_{{ $soal->id }}_d"
                                                    value="D">
                                                <label class="form-check-label" for="jawaban_{{ $soal->id }}_d">
                                                    D. {!! $soal->pilihan_d !!}
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-secondary btn-prev"
                                            {{ $index == 0 ? 'disabled' : '' }}>Soal Sebelumnya</button>
                                        <button type="button" class="btn btn-primary btn-next"
                                            {{ $index == $soalUjians->count() - 1 ? 'disabled' : '' }}>Soal
                                            Berikutnya</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-grid gap-2 col-md-6 mx-auto mb-4">
                            <button type="button" class="btn btn-danger btn-lg" data-bs-toggle="modal"
                                data-bs-target="#confirmSubmit">
                                Selesaikan Ujian
                            </button>
                        </div>

                        <!-- Modal Konfirmasi Submit -->
                        <div class="modal fade" id="confirmSubmit" tabindex="-1" aria-labelledby="confirmSubmitLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="confirmSubmitLabel">Konfirmasi Selesaikan Ujian</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah Anda yakin ingin menyelesaikan ujian? Jawaban yang sudah diisi tidak dapat
                                        diubah lagi.
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Kembali</button>
                                        <button type="submit" class="btn btn-danger">Ya, Selesaikan Ujian</button>
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
            // Timer
            let secondsRemaining = {{ $sisa }};
            const countdownEl = document.getElementById('countdown');

            const timer = setInterval(function() {
                secondsRemaining--;

                if (secondsRemaining <= 0) {
                    clearInterval(timer);
                    document.getElementById('exam-form').submit();
                    return;
                }

                const hours = Math.floor(secondsRemaining / 3600);
                const minutes = Math.floor((secondsRemaining % 3600) / 60);
                const seconds = secondsRemaining % 60;

                countdownEl.textContent =
                    (hours < 10 ? '0' : '') + hours + ':' +
                    (minutes < 10 ? '0' : '') + minutes + ':' +
                    (seconds < 10 ? '0' : '') + seconds;

                if (secondsRemaining < 300) { // Less than 5 minutes
                    countdownEl.classList.add('text-danger', 'blink');
                }
            }, 1000);

            // Navigation
            const soalCards = document.querySelectorAll('.soal-card');
            const soalNavButtons = document.querySelectorAll('.soal-nav');
            const prevButtons = document.querySelectorAll('.btn-prev');
            const nextButtons = document.querySelectorAll('.btn-next');

            function showSoal(index) {
                soalCards.forEach(card => card.style.display = 'none');
                soalCards[index - 1].style.display = 'block';

                // Update active button
                soalNavButtons.forEach(btn => btn.classList.remove('active'));
                soalNavButtons[index - 1].classList.add('active');

                // Scroll to top
                window.scrollTo(0, 0);
            }

            // Initial active
            soalNavButtons[0].classList.add('active');

            // Set up navigation buttons
            soalNavButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const soalIndex = parseInt(btn.dataset.soal);
                    showSoal(soalIndex);
                });
            });

            prevButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const currentCard = btn.closest('.soal-card');
                    const currentIndex = Array.from(soalCards).indexOf(currentCard);

                    if (currentIndex > 0) {
                        showSoal(currentIndex);
                    }
                });
            });

            nextButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const currentCard = btn.closest('.soal-card');
                    const currentIndex = Array.from(soalCards).indexOf(currentCard);

                    if (currentIndex < soalCards.length - 1) {
                        showSoal(currentIndex + 2);
                    }
                });
            });

            // Highlight answered questions
            const radios = document.querySelectorAll('input[type="radio"]');

            function updateNavigation() {
                const answeredQuestions = {};

                // Get all answered questions
                radios.forEach(radio => {
                    if (radio.checked) {
                        // Extract soal ID from the name (jawaban_X)
                        const soalId = radio.name.split('_')[1];
                        answeredQuestions[soalId] = true;
                    }
                });

                // Update navigation buttons
                soalNavButtons.forEach((btn, index) => {
                    const soal = soalUjians[index];
                    if (answeredQuestions[soal.id]) {
                        btn.classList.add('btn-success');
                        btn.classList.remove('btn-outline-primary');
                    } else {
                        btn.classList.remove('btn-success');
                        btn.classList.add('btn-outline-primary');
                    }
                });
            }

            radios.forEach(radio => {
                radio.addEventListener('change', updateNavigation);
            });

            // Prevent accidental navigation away
            window.addEventListener('beforeunload', function(e) {
                e.preventDefault();
                e.returnValue = '';
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .blink {
            animation: blink 1s linear infinite;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .soal-nav {
            min-width: 40px;
            margin: 2px;
        }
    </style>
@endpush
