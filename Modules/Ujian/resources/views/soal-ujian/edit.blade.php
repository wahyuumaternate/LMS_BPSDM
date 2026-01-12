@extends('layouts.main')

@section('title', 'Edit Soal Ujian')
@section('page-title', 'Edit Soal Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Soal Ujian</h5>

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
                        <p class="mb-1"><strong>Judul Ujian:</strong> {{ $soal->ujian->judul_ujian }}</p>
                        <p class="mb-1"><strong>Kursus:</strong> {{ $soal->ujian->kursus->nama_kursus }}</p>
                        <p class="mb-0"><strong>Jumlah Soal Saat Ini:</strong> {{ $soal->ujian->jumlah_soal }}</p>
                    </div>

                    <form action="{{ route('soal-ujian.update', $soal->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tipe_soal" value="pilihan_ganda">
                        <input type="hidden" name="ujian_id" value="{{ $soal->ujian_id }}">

                        <div class="row mb-3">
                            <label for="pertanyaan" class="col-sm-2 col-form-label">Pertanyaan <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pertanyaan') is-invalid @enderror" id="pertanyaan" name="pertanyaan"
                                    rows="4" required>{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                                @error('pertanyaan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pilihan_a" class="col-sm-2 col-form-label">Pilihan A <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pilihan_a') is-invalid @enderror" id="pilihan_a" name="pilihan_a" rows="2"
                                    required>{{ old('pilihan_a', $soal->pilihan_a) }}</textarea>
                                @error('pilihan_a')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pilihan_b" class="col-sm-2 col-form-label">Pilihan B <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pilihan_b') is-invalid @enderror" id="pilihan_b" name="pilihan_b" rows="2"
                                    required>{{ old('pilihan_b', $soal->pilihan_b) }}</textarea>
                                @error('pilihan_b')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pilihan_c" class="col-sm-2 col-form-label">Pilihan C <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pilihan_c') is-invalid @enderror" id="pilihan_c" name="pilihan_c" rows="2"
                                    required>{{ old('pilihan_c', $soal->pilihan_c) }}</textarea>
                                @error('pilihan_c')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="pilihan_d" class="col-sm-2 col-form-label">Pilihan D <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('pilihan_d') is-invalid @enderror" id="pilihan_d" name="pilihan_d" rows="2"
                                    required>{{ old('pilihan_d', $soal->pilihan_d) }}</textarea>
                                @error('pilihan_d')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="jawaban_benar" class="col-sm-2 col-form-label">Jawaban Benar <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select @error('jawaban_benar') is-invalid @enderror" id="jawaban_benar"
                                    name="jawaban_benar" required>
                                    <option value="">-- Pilih Jawaban Benar --</option>
                                    <option value="A"
                                        {{ old('jawaban_benar', $soal->jawaban_benar) == 'A' ? 'selected' : '' }}>A
                                    </option>
                                    <option value="B"
                                        {{ old('jawaban_benar', $soal->jawaban_benar) == 'B' ? 'selected' : '' }}>B
                                    </option>
                                    <option value="C"
                                        {{ old('jawaban_benar', $soal->jawaban_benar) == 'C' ? 'selected' : '' }}>C
                                    </option>
                                    <option value="D"
                                        {{ old('jawaban_benar', $soal->jawaban_benar) == 'D' ? 'selected' : '' }}>D
                                    </option>
                                </select>
                                @error('jawaban_benar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="poin" class="col-sm-2 col-form-label">Poin <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control @error('poin') is-invalid @enderror"
                                    id="poin" name="poin" value="{{ old('poin', $soal->poin) }}" min="1"
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
                                    <option value="mudah"
                                        {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'mudah' ? 'selected' : '' }}>
                                        Mudah</option>
                                    <option value="sedang"
                                        {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'sedang' ? 'selected' : '' }}>
                                        Sedang</option>
                                    <option value="sulit"
                                        {{ old('tingkat_kesulitan', $soal->tingkat_kesulitan) == 'sulit' ? 'selected' : '' }}>
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
                                    rows="3">{{ old('pembahasan', $soal->pembahasan) }}</textarea>
                                @error('pembahasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Pembahasan akan ditampilkan kepada peserta setelah ujian selesai
                                    (opsional)</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Update Soal</button>
                                <a href="{{ route('soal-ujian.by-ujian', ['ujianId' => $soal->ujian_id]) }}"
                                    class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
