@extends('layouts.main')

@section('title', 'Tambah Progress Pembelajaran')
@section('page-title', 'Tambah Progress Pembelajaran')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tambah Progress</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('progres-materi.store') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <label for="peserta_id" class="col-sm-2 col-form-label">Peserta</label>
                            <div class="col-sm-10">
                                <select class="form-select @error('peserta_id') is-invalid @enderror" id="peserta_id"
                                    name="peserta_id" required>
                                    <option value="">-- Pilih Peserta --</option>
                                    @foreach ($pesertas as $peserta)
                                        <option value="{{ $peserta->id }}"
                                            {{ old('peserta_id') == $peserta->id ? 'selected' : '' }}>
                                            {{ $peserta->nama_lengkap }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('peserta_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="materi_id" class="col-sm-2 col-form-label">Materi</label>
                            <div class="col-sm-10">
                                <select class="form-select @error('materi_id') is-invalid @enderror" id="materi_id"
                                    name="materi_id" required>
                                    <option value="">-- Pilih Materi --</option>
                                    @foreach ($materis as $materi)
                                        <option value="{{ $materi->id }}"
                                            {{ old('materi_id') == $materi->id ? 'selected' : '' }}>
                                            {{ $materi->judul_materi }}
                                            @if ($materi->modul)
                                                ({{ $materi->modul->nama_modul }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('materi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="progress_persen" class="col-sm-2 col-form-label">Progress (%)</label>
                            <div class="col-sm-10">
                                <input type="number" class="form-control @error('progress_persen') is-invalid @enderror"
                                    id="progress_persen" name="progress_persen" min="0" max="100"
                                    value="{{ old('progress_persen', 0) }}">
                                <div class="form-text">Masukkan nilai 0-100</div>
                                @error('progress_persen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="durasi_belajar_menit" class="col-sm-2 col-form-label">Durasi Belajar (menit)</label>
                            <div class="col-sm-10">
                                <input type="number"
                                    class="form-control @error('durasi_belajar_menit') is-invalid @enderror"
                                    id="durasi_belajar_menit" name="durasi_belajar_menit" min="0"
                                    value="{{ old('durasi_belajar_menit', 0) }}">
                                @error('durasi_belajar_menit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_selesai" name="is_selesai"
                                        {{ old('is_selesai') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_selesai">
                                        Tandai sebagai Selesai
                                    </label>
                                </div>
                                <div class="form-text">Jika dicentang, progress akan otomatis diset 100% dan tanggal selesai
                                    akan dicatat</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="{{ route('progres-materi.index') }}" class="btn btn-secondary">Kembali</a>
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
            // Auto-set progress to 100% when marking as complete
            const isSelesaiCheckbox = document.getElementById('is_selesai');
            const progressInput = document.getElementById('progress_persen');

            isSelesaiCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    progressInput.value = 100;
                }
            });

            // Select2 for better dropdowns (if available)
            if (typeof $.fn.select2 !== 'undefined') {
                $('#peserta_id').select2({
                    placeholder: "-- Pilih Peserta --",
                    allowClear: true
                });

                $('#materi_id').select2({
                    placeholder: "-- Pilih Materi --",
                    allowClear: true
                });
            }
        });
    </script>
@endpush
