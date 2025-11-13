@extends('layouts.main')

@section('title', 'Edit Ujian')
@section('page-title', 'Edit Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Edit Ujian</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('ujians.update', $ujian->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <label for="kursus_id" class="col-sm-2 col-form-label">Kursus <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select @error('kursus_id') is-invalid @enderror" id="kursus_id"
                                    name="kursus_id" required>
                                    <option value="">-- Pilih Kursus --</option>
                                    @foreach (\Modules\Kursus\Entities\Kursus::orderBy('judul')->get() as $kursus)
                                        <option value="{{ $kursus->id }}"
                                            {{ old('kursus_id', $ujian->kursus_id) == $kursus->id ? 'selected' : '' }}>
                                            {{ $kursus->judul }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kursus_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="judul_ujian" class="col-sm-2 col-form-label">Judul Ujian <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('judul_ujian') is-invalid @enderror"
                                    id="judul_ujian" name="judul_ujian"
                                    value="{{ old('judul_ujian', $ujian->judul_ujian) }}" required>
                                @error('judul_ujian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="deskripsi" class="col-sm-2 col-form-label">Deskripsi</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $ujian->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Waktu Pelaksanaan</label>
                            <div class="col-sm-5">
                                <label for="waktu_mulai" class="form-label">Waktu Mulai</label>
                                <input type="datetime-local" class="form-control @error('waktu_mulai') is-invalid @enderror"
                                    id="waktu_mulai" name="waktu_mulai"
                                    value="{{ old('waktu_mulai', $ujian->waktu_mulai ? $ujian->waktu_mulai->format('Y-m-d\TH:i') : '') }}">
                                @error('waktu_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kosongkan jika tidak ada batasan waktu mulai</div>
                            </div>
                            <div class="col-sm-5">
                                <label for="waktu_selesai" class="form-label">Waktu Selesai</label>
                                <input type="datetime-local"
                                    class="form-control @error('waktu_selesai') is-invalid @enderror" id="waktu_selesai"
                                    name="waktu_selesai"
                                    value="{{ old('waktu_selesai', $ujian->waktu_selesai ? $ujian->waktu_selesai->format('Y-m-d\TH:i') : '') }}">
                                @error('waktu_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Kosongkan jika tidak ada batasan waktu selesai</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="durasi_menit" class="col-sm-2 col-form-label">Durasi (menit) <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror"
                                    id="durasi_menit" name="durasi_menit"
                                    value="{{ old('durasi_menit', $ujian->durasi_menit) }}" min="1" required>
                                @error('durasi_menit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <label for="passing_grade" class="col-sm-2 col-form-label">Passing Grade (%) <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="number" class="form-control @error('passing_grade') is-invalid @enderror"
                                    id="passing_grade" name="passing_grade"
                                    value="{{ old('passing_grade', $ujian->passing_grade) }}" min="0" max="100"
                                    required>
                                @error('passing_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="bobot_nilai" class="col-sm-2 col-form-label">Bobot Nilai <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-4">
                                <input type="number" step="0.01"
                                    class="form-control @error('bobot_nilai') is-invalid @enderror" id="bobot_nilai"
                                    name="bobot_nilai" value="{{ old('bobot_nilai', $ujian->bobot_nilai) }}" min="0.1"
                                    max="100" required>
                                @error('bobot_nilai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> Ujian ini memiliki {{ $ujian->jumlah_soal }} soal.
                                    Untuk mengelola soal silahkan ke halaman <a
                                        href="{{ route('soal-ujian.by-ujian', $ujian->id) }}">Kelola Soal</a>.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-2">Pengaturan Tambahan</div>
                            <div class="col-sm-10">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="random_soal" name="random_soal"
                                        value="1" {{ old('random_soal', $ujian->random_soal) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="random_soal">
                                        Tampilkan soal secara acak
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tampilkan_hasil"
                                        name="tampilkan_hasil" value="1"
                                        {{ old('tampilkan_hasil', $ujian->tampilkan_hasil) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tampilkan_hasil">
                                        Tampilkan hasil setelah selesai ujian
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="aturan_ujian" class="col-sm-2 col-form-label">Aturan Ujian</label>
                            <div class="col-sm-10">
                                <textarea class="form-control @error('aturan_ujian') is-invalid @enderror" id="aturan_ujian" name="aturan_ujian"
                                    rows="4">{{ old('aturan_ujian', $ujian->aturan_ujian) }}</textarea>
                                @error('aturan_ujian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Petunjuk atau aturan yang akan ditampilkan kepada peserta sebelum
                                    memulai ujian</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Perbarui Ujian</button>
                                <a href="{{ route('ujians.index') }}" class="btn btn-secondary">Kembali</a>
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
            // Script untuk validasi waktu selesai harus setelah waktu mulai
            const waktuMulai = document.getElementById('waktu_mulai');
            const waktuSelesai = document.getElementById('waktu_selesai');

            function validateDateTime() {
                if (waktuMulai.value && waktuSelesai.value) {
                    if (new Date(waktuSelesai.value) <= new Date(waktuMulai.value)) {
                        waktuSelesai.setCustomValidity('Waktu selesai harus setelah waktu mulai');
                    } else {
                        waktuSelesai.setCustomValidity('');
                    }
                } else {
                    waktuSelesai.setCustomValidity('');
                }
            }

            waktuMulai.addEventListener('change', validateDateTime);
            waktuSelesai.addEventListener('change', validateDateTime);
        });
    </script>
@endpush
