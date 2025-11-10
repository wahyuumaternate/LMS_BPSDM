@extends('layouts.main')

@section('title', 'Tambah Soal Ujian')
@section('page-title', 'Tambah Soal Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tambah Soal Ujian</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Ujian Info -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Info Ujian</h6>
                        <p class="mb-1"><strong>Judul Ujian:</strong> {{ $ujian->judul_ujian }}</p>
                        <p class="mb-1"><strong>Kursus:</strong> {{ $ujian->kursus->nama_kursus }}</p>
                        <p class="mb-0"><strong>Jumlah Soal Saat Ini:</strong> {{ $ujian->jumlah_soal }}</p>
                    </div>

                    <form action="{{ route('soal-ujian.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ujian_id" value="{{ $ujian->id }}">

                        <div class="row mb-3">
                            <label for="tipe_soal" class="col-sm-2 col-form-label">Tipe Soal <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select @error('tipe_soal') is-invalid @enderror" id="tipe_soal"
                                    name="tipe_soal" required>
                                    <option value="">-- Pilih Tipe Soal --</option>
                                    <option value="pilihan_ganda"
                                        {{ old('tipe_soal') == 'pilihan_ganda' ? 'selected' : '' }}>Pilihan Ganda</option>
                                    <option value="essay" {{ old('tipe_soal') == 'essay' ? 'selected' : '' }}>Essay
                                    </option>
                                    <option value="benar_salah" {{ old('tipe_soal') == 'benar_salah' ? 'selected' : '' }}>
                                        Benar/Salah</option>
                                </select>
                                @error('tipe_soal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pertanyaan" class="col-sm-2 col-form-label">Pertanyaan <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan"
                                    rows="4" required>{{ old('pertanyaan') }}</textarea>
                                @error('pertanyaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Pilihan untuk Pilihan Ganda -->
                        <div id="pilihan_ganda_section" style="display: none;">
                            <div class="row mb-3">
                                <label for="pilihan_a" class="col-sm-2 col-form-label">Pilihan A <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <textarea class="form-control @error('pilihan_a') is-invalid @enderror" id="pilihan_a" name="pilihan_a" rows="2">{{ old('pilihan_a') }}</textarea>
                                    @error('pilihan_a')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="pilihan_b" class="col-sm-2 col-form-label">Pilihan B <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <textarea class="form-control @error('pilihan_b') is-invalid @enderror" id="pilihan_b" name="pilihan_b" rows="2">{{ old('pilihan_b') }}</textarea>
                                    @error('pilihan_b')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="pilihan_c" class="col-sm-2 col-form-label">Pilihan C <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <textarea class="form-control @error('pilihan_c') is-invalid @enderror" id="pilihan_c" name="pilihan_c" rows="2">{{ old('pilihan_c') }}</textarea>
                                    @error('pilihan_c')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="pilihan_d" class="col-sm-2 col-form-label">Pilihan D <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <textarea class="form-control @error('pilihan_d') is-invalid @enderror" id="pilihan_d" name="pilihan_d" rows="2">{{ old('pilihan_d') }}</textarea>
                                    @error('pilihan_d')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="jawaban_benar_pg" class="col-sm-2 col-form-label">Jawaban Benar <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <select class="form-select @error('jawaban_benar') is-invalid @enderror"
                                        id="jawaban_benar_pg" name="jawaban_benar">
                                        <option value="">-- Pilih Jawaban Benar --</option>
                                        <option value="A" {{ old('jawaban_benar') == 'A' ? 'selected' : '' }}>A
                                        </option>
                                        <option value="B" {{ old('jawaban_benar') == 'B' ? 'selected' : '' }}>B
                                        </option>
                                        <option value="C" {{ old('jawaban_benar') == 'C' ? 'selected' : '' }}>C
                                        </option>
                                        <option value="D" {{ old('jawaban_benar') == 'D' ? 'selected' : '' }}>D
                                        </option>
                                    </select>
                                    @error('jawaban_benar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan untuk Benar/Salah -->
                        <div id="benar_salah_section" style="display: none;">
                            <div class="row mb-3">
                                <label for="jawaban_benar_bs" class="col-sm-2 col-form-label">Jawaban Benar <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-10">
                                    <select class="form-select @error('jawaban_benar') is-invalid @enderror"
                                        id="jawaban_benar_bs" name="jawaban_benar_bs">
                                        <option value="">-- Pilih Jawaban Benar --</option>
                                        <option value="Benar" {{ old('jawaban_benar_bs') == 'Benar' ? 'selected' : '' }}>
                                            Benar</option>
                                        <option value="Salah" {{ old('jawaban_benar_bs') == 'Salah' ? 'selected' : '' }}>
                                            Salah</option>
                                    </select>
                                    @error('jawaban_benar_bs')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Untuk Essay -->
                        <div id="essay_section" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Soal essay akan memerlukan penilaian manual dari
                                instruktur setelah ujian selesai.
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="poin" class="col-sm-2 col-form-label">Poin <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control @error('poin') is-invalid @enderror"
                                    id="poin" name="poin" value="{{ old('poin', 1) }}" min="1"
                                    max="100" required>
                                @error('poin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <label for="tingkat_kesulitan" class="col-sm-2 col-form-label">Tingkat Kesulitan <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <select class="form-select @error('tingkat_kesulitan') is-invalid @enderror"
                                    id="tingkat_kesulitan" name="tingkat_kesulitan" required>
                                    <option value="mudah" {{ old('tingkat_kesulitan') == 'mudah' ? 'selected' : '' }}>
                                        Mudah</option>
                                    <option value="sedang"
                                        {{ old('tingkat_kesulitan', 'sedang') == 'sedang' ? 'selected' : '' }}>Sedang
                                    </option>
                                    <option value="sulit" {{ old('tingkat_kesulitan') == 'sulit' ? 'selected' : '' }}>
                                        Sulit</option>
                                </select>
                                @error('tingkat_kesulitan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pembahasan" class="col-sm-2 col-form-label">Pembahasan</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pembahasan') is-invalid @enderror" id="pembahasan" name="pembahasan"
                                    rows="3">{{ old('pembahasan') }}</textarea>
                                @error('pembahasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Pembahasan akan ditampilkan kepada peserta setelah ujian selesai
                                    (opsional)</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Simpan Soal</button>
                                <a href="{{ route('soal-ujian.by-ujian', $ujian->id) }}"
                                    class="btn btn-secondary">Kembali</a>
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
            const tipeSoalSelect = document.getElementById('tipe_soal');
            const pilihanGandaSection = document.getElementById('pilihan_ganda_section');
            const benarSalahSection = document.getElementById('benar_salah_section');
            const essaySection = document.getElementById('essay_section');

            // Fields for different question types
            const pilihanGandaFields = document.querySelectorAll(
                '#pilihan_a, #pilihan_b, #pilihan_c, #pilihan_d, #jawaban_benar_pg');
            const benarSalahFields = document.querySelectorAll('#jawaban_benar_bs');

            function updateSections() {
                // Hide all sections first
                pilihanGandaSection.style.display = 'none';
                benarSalahSection.style.display = 'none';
                essaySection.style.display = 'none';

                // Remove required attribute from all fields
                pilihanGandaFields.forEach(field => {
                    field.removeAttribute('required');
                });
                benarSalahFields.forEach(field => {
                    field.removeAttribute('required');
                });

                // Show relevant section based on selected type
                const selectedType = tipeSoalSelect.value;

                if (selectedType === 'pilihan_ganda') {
                    pilihanGandaSection.style.display = 'block';
                    pilihanGandaFields.forEach(field => {
                        field.setAttribute('required', 'required');
                    });
                } else if (selectedType === 'benar_salah') {
                    benarSalahSection.style.display = 'block';
                    benarSalahFields.forEach(field => {
                        field.setAttribute('required', 'required');
                    });
                } else if (selectedType === 'essay') {
                    essaySection.style.display = 'block';
                }
            }

            // Initial update on page load
            updateSections();

            // Update when select changes
            tipeSoalSelect.addEventListener('change', updateSections);
        });
    </script>
@endpush
