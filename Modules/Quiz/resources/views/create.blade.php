@extends('layouts.main')

@section('title', 'Tambah Quiz Baru')
@section('page-title', 'Tambah Quiz Baru')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Formulir Tambah Quiz</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('quizzes.store') }}" method="POST">
                        @csrf

                        <!-- Hidden field for passing modul_id from query parameter -->
                        @if (request()->has('modul_id'))
                            <input type="hidden" name="modul_id" value="{{ request('modul_id') }}">
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="modul_id" class="form-label">Modul</label>
                                <select class="form-select @error('modul_id') is-invalid @enderror" name="modul_id"
                                    id="modul_id" required {{ request()->has('modul_id') ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Modul --</option>
                                    @foreach ($moduls as $modul)
                                        <option value="{{ $modul->id }}"
                                            {{ old('modul_id') == $modul->id || request('modul_id') == $modul->id ? 'selected' : '' }}>
                                            {{ $modul->nama_modul }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('modul_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="judul_quiz" class="form-label">Judul Quiz</label>
                                <input type="text" class="form-control @error('judul_quiz') is-invalid @enderror"
                                    id="judul_quiz" name="judul_quiz" value="{{ old('judul_quiz') }}" required>
                                @error('judul_quiz')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="durasi_menit" class="form-label">Durasi (menit)</label>
                                <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror"
                                    id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit') }}" min="1">
                                @error('durasi_menit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="bobot_nilai" class="form-label">Bobot Nilai</label>
                                <input type="number" step="0.01"
                                    class="form-control @error('bobot_nilai') is-invalid @enderror" id="bobot_nilai"
                                    name="bobot_nilai" value="{{ old('bobot_nilai') }}" min="0.01" max="100">
                                @error('bobot_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="passing_grade" class="form-label">Passing Grade (%)</label>
                                <input type="number" class="form-control @error('passing_grade') is-invalid @enderror"
                                    id="passing_grade" name="passing_grade" value="{{ old('passing_grade', 70) }}"
                                    min="1" max="100">
                                @error('passing_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="jumlah_soal" class="form-label">Jumlah Soal</label>
                                <input type="number" class="form-control @error('jumlah_soal') is-invalid @enderror"
                                    id="jumlah_soal" name="jumlah_soal" value="{{ old('jumlah_soal') }}" min="0">
                                @error('jumlah_soal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="max_attempt" class="form-label">Maks. Percobaan</label>
                                <input type="number" class="form-control @error('max_attempt') is-invalid @enderror"
                                    id="max_attempt" name="max_attempt" value="{{ old('max_attempt', 1) }}" min="0">
                                @error('max_attempt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Isi 0 untuk percobaan tidak terbatas</small>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="random_soal" name="random_soal"
                                        {{ old('random_soal') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="random_soal">
                                        Acak Urutan Soal
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="tampilkan_hasil"
                                        name="tampilkan_hasil" {{ old('tampilkan_hasil') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tampilkan_hasil">
                                        Tampilkan Hasil Langsung
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('quizzes.index', request()->query()) }}"
                                class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
