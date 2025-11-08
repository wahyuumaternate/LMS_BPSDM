@extends('layouts.main')

@section('title', 'Edit Soal Quiz')
@section('page-title', 'Edit Soal Quiz')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Soal Quiz</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('soal-quiz.update', $soalQuiz->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="quiz_id" class="col-sm-2 col-form-label">Quiz</label>
                            <div class="col-sm-10">
                                <select class="form-select @error('quiz_id') is-invalid @enderror" name="quiz_id"
                                    id="quiz_id">
                                    <option value="">-- Pilih Quiz --</option>
                                    @foreach ($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}"
                                            {{ old('quiz_id', $soalQuiz->quiz_id) == $quiz->id ? 'selected' : '' }}>
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

                        <div class="row mb-3">
                            <label for="pertanyaan" class="col-sm-2 col-form-label">Pertanyaan</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan"
                                    rows="3" required>{{ old('pertanyaan', $soalQuiz->pertanyaan) }}</textarea>
                                @error('pertanyaan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Tuliskan pertanyaan dengan jelas dan lengkap.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="tingkat_kesulitan" class="col-sm-2 col-form-label">Tingkat Kesulitan</label>
                            <div class="col-sm-10">
                                <select class="form-select @error('tingkat_kesulitan') is-invalid @enderror"
                                    name="tingkat_kesulitan" id="tingkat_kesulitan">
                                    <option value="mudah"
                                        {{ old('tingkat_kesulitan', $soalQuiz->tingkat_kesulitan) == 'mudah' ? 'selected' : '' }}>
                                        Mudah
                                    </option>
                                    <option value="sedang"
                                        {{ old('tingkat_kesulitan', $soalQuiz->tingkat_kesulitan) == 'sedang' ? 'selected' : '' }}>
                                        Sedang
                                    </option>
                                    <option value="sulit"
                                        {{ old('tingkat_kesulitan', $soalQuiz->tingkat_kesulitan) == 'sulit' ? 'selected' : '' }}>
                                        Sulit
                                    </option>
                                </select>
                                @error('tingkat_kesulitan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="poin" class="col-sm-2 col-form-label">Poin</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control @error('poin') is-invalid @enderror"
                                    id="poin" name="poin" value="{{ old('poin', $soalQuiz->poin) }}"
                                    min="1">
                                @error('poin')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Nilai poin yang didapatkan jika menjawab benar.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pembahasan" class="col-sm-2 col-form-label">Pembahasan</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pembahasan') is-invalid @enderror" id="pembahasan" name="pembahasan"
                                    rows="3">{{ old('pembahasan', $soalQuiz->pembahasan) }}</textarea>
                                @error('pembahasan')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Tuliskan pembahasan atau penjelasan jawaban (opsional).</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5>Opsi Jawaban</h5>
                        <p class="text-muted">Edit opsi jawaban dan pilih salah satu sebagai jawaban yang benar.</p>

                        <div id="options-container">
                            <!-- Opsi jawaban akan di-render di sini -->
                            @foreach ($soalQuiz->options as $index => $option)
                                <div class="row mb-3 option-row">
                                    <label class="col-sm-2 col-form-label">Opsi {{ $index + 1 }}</label>
                                    <div class="col-sm-8">
                                        <input type="hidden" name="option_ids[{{ $index }}]"
                                            value="{{ $option->id }}">
                                        <input type="text" class="form-control" name="teks_opsi[{{ $index }}]"
                                            placeholder="Teks opsi jawaban"
                                            value="{{ old('teks_opsi.' . $index, $option->teks_opsi) }}" required>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input answer-radio" type="radio"
                                                name="is_jawaban_benar" value="{{ $index }}"
                                                {{ old('is_jawaban_benar', $option->is_jawaban_benar ? $index : '') == $index ? 'checked' : '' }}>
                                            <label class="form-check-label">Jawaban Benar</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="button" id="add-option" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-circle"></i> Tambah Opsi Jawaban
                                </button>
                                <button type="button" id="remove-option" class="btn btn-outline-danger btn-sm"
                                    {{ count($soalQuiz->options) <= 2 ? 'disabled' : '' }}>
                                    <i class="bi bi-dash-circle"></i> Hapus Opsi Terakhir
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                <a href="{{ route('soal-quiz.show', $soalQuiz->id) }}" class="btn btn-secondary">Batal</a>
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
            const optionsContainer = document.getElementById('options-container');
            const addOptionButton = document.getElementById('add-option');
            const removeOptionButton = document.getElementById('remove-option');

            let optionCount = {{ count($soalQuiz->options) }};

            // Function to add a new option field
            function addOptionField(value = '', isCorrect = false) {
                const optionIndex = optionCount;
                const optionHtml = `
                    <div class="row mb-3 option-row">
                        <label class="col-sm-2 col-form-label">Opsi ${optionIndex + 1}</label>
                        <div class="col-sm-8">
                            <input type="hidden" name="option_ids[${optionIndex}]" value="">
                            <input type="text" class="form-control" name="teks_opsi[${optionIndex}]" 
                                placeholder="Teks opsi jawaban" value="${value}" required>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-check mt-2">
                                <input class="form-check-input answer-radio" type="radio" name="is_jawaban_benar" 
                                    value="${optionIndex}" ${isCorrect ? 'checked' : ''}>
                                <label class="form-check-label">Jawaban Benar</label>
                            </div>
                        </div>
                    </div>
                `;

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = optionHtml;
                optionsContainer.appendChild(tempDiv.firstElementChild);
                optionCount++;

                // Enable remove button when we have more than 2 options
                if (optionCount > 2) {
                    removeOptionButton.disabled = false;
                }
            }

            // Function to remove the last option field
            function removeLastOption() {
                if (optionCount > 2) {
                    const options = optionsContainer.querySelectorAll('.option-row');
                    const lastOption = options[options.length - 1];
                    optionsContainer.removeChild(lastOption);
                    optionCount--;

                    // If we only have 2 options left, disable the remove button
                    if (optionCount <= 2) {
                        removeOptionButton.disabled = true;
                    }

                    // Ensure at least one option is marked as correct
                    const radios = document.querySelectorAll('.answer-radio');
                    let hasChecked = false;

                    radios.forEach(radio => {
                        if (radio.checked) {
                            hasChecked = true;
                        }
                    });

                    if (!hasChecked && radios.length > 0) {
                        radios[0].checked = true;
                    }
                }
            }

            // Add event listener to the add option button
            addOptionButton.addEventListener('click', function() {
                addOptionField();
            });

            // Add event listener to the remove option button
            removeOptionButton.addEventListener('click', function() {
                removeLastOption();
            });

            // Ensure that at least one option is marked as correct
            const formElement = document.querySelector('form');
            formElement.addEventListener('submit', function(event) {
                const radios = document.querySelectorAll('input[name="is_jawaban_benar"]:checked');

                if (radios.length === 0) {
                    event.preventDefault();
                    alert('Anda harus memilih salah satu opsi sebagai jawaban yang benar!');
                }
            });
        });
    </script>
@endpush
