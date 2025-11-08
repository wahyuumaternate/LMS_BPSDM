@extends('layouts.main')

@section('title', 'Tambah Multiple Soal Quiz')
@section('page-title', 'Tambah Multiple Soal Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tambah Multiple Soal Quiz</h5>
                    <p class="text-muted">
                        Gunakan form ini untuk menambahkan beberapa soal quiz sekaligus ke dalam satu quiz.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('soal-quiz.store-bulk') }}" method="POST" id="bulk-form">
                        @csrf

                        <!-- Jika ada quiz_id dari request query, gunakan sebagai nilai default -->
                        @if (isset($selectedQuizId))
                            <input type="hidden" name="quiz_id" value="{{ $selectedQuizId }}">
                        @endif

                        <div class="row mb-3">
                            <label for="quiz_id" class="col-sm-2 col-form-label">Quiz</label>
                            <div class="col-sm-10">
                                <select class="form-select @error('quiz_id') is-invalid @enderror" name="quiz_id"
                                    id="quiz_id" {{ isset($selectedQuizId) ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Quiz --</option>
                                    @foreach ($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}"
                                            {{ old('quiz_id') == $quiz->id || (isset($selectedQuizId) && $selectedQuizId == $quiz->id) ? 'selected' : '' }}>
                                            {{ $quiz->judul_quiz }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('quiz_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div id="questions-container">
                            <!-- Pertanyaan akan ditambahkan di sini -->
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 text-center">
                                <button type="button" id="add-question" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Tambah Soal Baru
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-success btn-lg" id="submit-button" disabled>
                                    <i class="bi bi-save"></i> Simpan Semua Soal
                                </button>
                                @if (isset($selectedQuizId))
                                    <a href="{{ route('quizzes.show', $selectedQuizId) }}"
                                        class="btn btn-secondary btn-lg">Batal</a>
                                @else
                                    <a href="{{ route('soal-quiz.index') }}" class="btn btn-secondary btn-lg">Batal</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Template for question panel (hidden) -->
    <template id="question-template">
        <div class="question-panel card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="mb-0">Soal #<span class="question-number"></span></h5>
                <button type="button" class="btn-close remove-question" aria-label="Close"></button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Pertanyaan</label>
                    <textarea class="form-control" name="questions[INDEX].pertanyaan" rows="3" required></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tingkat Kesulitan</label>
                        <select class="form-select" name="questions[INDEX].tingkat_kesulitan">
                            <option value="mudah">Mudah</option>
                            <option value="sedang">Sedang</option>
                            <option value="sulit">Sulit</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Poin</label>
                        <input type="number" class="form-control" name="questions[INDEX].poin" value="1"
                            min="1">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Pembahasan (Opsional)</label>
                    <textarea class="form-control" name="questions[INDEX].pembahasan" rows="2"></textarea>
                </div>

                <hr>

                <h6>Opsi Jawaban</h6>
                <div class="options-container">
                    <!-- Options will be added here -->
                </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary add-option">
                        <i class="bi bi-plus-circle"></i> Tambah Opsi
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-option">
                        <i class="bi bi-dash-circle"></i> Hapus Opsi Terakhir
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- Template for option (hidden) -->
    <template id="option-template">
        <div class="option-row mb-2">
            <div class="input-group">
                <span class="input-group-text">Opsi <span class="option-number"></span></span>
                <input type="text" class="form-control" name="questions[Q_INDEX].options[OPT_INDEX].teks_opsi" required>
                <div class="input-group-text">
                    <div class="form-check">
                        <input class="form-check-input correct-answer" type="radio"
                            name="questions[Q_INDEX].correct_answer" value="OPT_INDEX">
                        <label class="form-check-label">Jawaban Benar</label>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionsContainer = document.getElementById('questions-container');
            const addQuestionButton = document.getElementById('add-question');
            const submitButton = document.getElementById('submit-button');
            const questionTemplate = document.getElementById('question-template');
            const optionTemplate = document.getElementById('option-template');

            let questionCount = 0;

            // Function to add a new question
            function addQuestion() {
                const questionIndex = questionCount;
                const questionPanel = questionTemplate.content.cloneNode(true).querySelector('.question-panel');

                // Update the question number and all INDEX placeholders in names
                questionPanel.querySelector('.question-number').textContent = questionIndex + 1;
                const inputs = questionPanel.querySelectorAll('[name*="INDEX"]');
                inputs.forEach(input => {
                    input.name = input.name.replace('INDEX', questionIndex);
                });

                // Add options container reference to the question panel
                const optionsContainer = questionPanel.querySelector('.options-container');

                // Add initial options (2)
                addOption(optionsContainer, questionIndex, 0, true); // First option as default correct
                addOption(optionsContainer, questionIndex, 1);

                // Set up option add/remove buttons
                const addOptionBtn = questionPanel.querySelector('.add-option');
                const removeOptionBtn = questionPanel.querySelector('.remove-option');

                addOptionBtn.addEventListener('click', function() {
                    const optionCount = optionsContainer.querySelectorAll('.option-row').length;
                    if (optionCount < 5) { // Maximum 5 options
                        addOption(optionsContainer, questionIndex, optionCount);
                        updateRemoveOptionButton(optionsContainer, removeOptionBtn);
                    } else {
                        alert('Maksimal 5 opsi jawaban untuk setiap soal.');
                    }
                });

                removeOptionBtn.addEventListener('click', function() {
                    const options = optionsContainer.querySelectorAll('.option-row');
                    if (options.length > 2) {
                        optionsContainer.removeChild(options[options.length - 1]);

                        // Ensure at least one option is selected as correct
                        ensureCorrectAnswerSelected(optionsContainer, questionIndex);
                    }
                    updateRemoveOptionButton(optionsContainer, removeOptionBtn);
                });

                // Set up remove question button
                const removeQuestionBtn = questionPanel.querySelector('.remove-question');
                removeQuestionBtn.addEventListener('click', function() {
                    questionsContainer.removeChild(questionPanel);
                    renumberQuestions();
                    questionCount--;
                    updateSubmitButtonState();
                });

                // Add to container
                questionsContainer.appendChild(questionPanel);
                questionCount++;
                updateSubmitButtonState();

                // Update the initial state of the remove option button
                updateRemoveOptionButton(optionsContainer, removeOptionBtn);
            }

            // Function to add an option to a question
            function addOption(container, questionIndex, optionIndex, isCorrect = false) {
                const optionRow = optionTemplate.content.cloneNode(true).querySelector('.option-row');

                // Update option number and indices in names
                optionRow.querySelector('.option-number').textContent = optionIndex + 1;

                const optionInput = optionRow.querySelector('input[type="text"]');
                optionInput.name = optionInput.name
                    .replace('Q_INDEX', questionIndex)
                    .replace('OPT_INDEX', optionIndex);

                const radioButton = optionRow.querySelector('.correct-answer');
                radioButton.name = radioButton.name.replace('Q_INDEX', questionIndex);
                radioButton.value = optionIndex;

                if (isCorrect) {
                    radioButton.checked = true;
                }

                // Handle radio button change to update the is_jawaban_benar fields
                radioButton.addEventListener('change', function() {
                    if (this.checked) {
                        updateIsJawabanBenar(container, questionIndex, optionIndex);
                    }
                });

                container.appendChild(optionRow);

                // Add initial hidden fields for jawaban benar
                if (container.querySelectorAll('.option-row').length === 1) {
                    updateIsJawabanBenar(container, questionIndex, optionIndex);
                }
            }

            // Function to update hidden is_jawaban_benar fields when radio selection changes
            function updateIsJawabanBenar(container, questionIndex, selectedOptionIndex) {
                // Remove any existing hidden fields
                const existingFields = container.querySelectorAll('input[name*="is_jawaban_benar"]');
                existingFields.forEach(field => field.remove());

                // Add hidden fields for each option
                const optionCount = container.querySelectorAll('.option-row').length;
                for (let i = 0; i < optionCount; i++) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = `questions[${questionIndex}].options[${i}].is_jawaban_benar`;
                    hiddenField.value = (i === parseInt(selectedOptionIndex)) ? 'true' : 'false';
                    container.appendChild(hiddenField);
                }
            }

            // Function to ensure at least one option is selected as correct
            function ensureCorrectAnswerSelected(container, questionIndex) {
                const radios = container.querySelectorAll('.correct-answer');
                let hasChecked = false;

                radios.forEach(radio => {
                    if (radio.checked) {
                        hasChecked = true;
                    }
                });

                if (!hasChecked && radios.length > 0) {
                    radios[0].checked = true;
                    updateIsJawabanBenar(container, questionIndex, radios[0].value);
                }
            }

            // Function to update the remove option button state
            function updateRemoveOptionButton(container, button) {
                const optionCount = container.querySelectorAll('.option-row').length;
                button.disabled = optionCount <= 2;
            }

            // Function to renumber questions after removing one
            function renumberQuestions() {
                const questionPanels = questionsContainer.querySelectorAll('.question-panel');
                questionPanels.forEach((panel, index) => {
                    panel.querySelector('.question-number').textContent = index + 1;
                });
            }

            // Function to update submit button state
            function updateSubmitButtonState() {
                submitButton.disabled = questionCount === 0;
            }

            // Add event listener to the add question button
            addQuestionButton.addEventListener('click', function() {
                addQuestion();
            });

            // Form validation before submit
            document.getElementById('bulk-form').addEventListener('submit', function(event) {
                let isValid = true;

                // Check if quiz is selected
                const quizSelect = document.getElementById('quiz_id');
                if (!quizSelect.value) {
                    alert('Anda harus memilih Quiz terlebih dahulu!');
                    event.preventDefault();
                    return false;
                }

                // Check if there are questions
                if (questionCount === 0) {
                    alert('Anda harus menambahkan minimal satu soal!');
                    event.preventDefault();
                    return false;
                }

                // Check each question for correct answer selection
                const questionPanels = questionsContainer.querySelectorAll('.question-panel');
                questionPanels.forEach((panel, questionIndex) => {
                    const optionsContainer = panel.querySelector('.options-container');
                    const radios = optionsContainer.querySelectorAll('.correct-answer:checked');

                    if (radios.length === 0) {
                        alert(`Soal #${questionIndex + 1} harus memiliki satu jawaban benar!`);
                        isValid = false;
                        event.preventDefault();
                        return false;
                    }
                });

                return isValid;
            });

            // Add an initial question
            addQuestion();
        });
    </script>
@endpush
