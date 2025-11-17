@extends('kursus::show')

@section('title', 'Ujian Kursus')
@section('page-title', 'Daftar Ujian Kursus')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
    <li class="breadcrumb-item active">Ujian</li>
@endsection

@section('detail-content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Ujian Kursus: {{ $kursus->judul }}</h5>
                <small class="text-muted">Total {{ $kursus->ujians->count() ?? 0 }} ujian</small>
            </div>
            <div>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUjian">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Ujian
                </button>
            </div>
        </div>

        <div class="card-body">
            @if ($kursus->ujians && $kursus->ujians->count() > 0)
                <div class="row">
                    @foreach ($kursus->ujians as $ujian)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-file-text me-2"></i>{{ $ujian->judul_ujian }}
                                    </h6>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-link text-dark" type="button"
                                            data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('ujians.show', [$kursus->id, $ujian->id]) }}">
                                                    <i class="bi bi-eye me-2"></i>Lihat Detail
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('soal-ujian.by-ujian', [$kursus->id, $ujian->id]) }}">
                                                    <i class="bi bi-list-task me-2"></i>Kelola Soal
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item btn-edit-ujian" href="#"
                                                    data-id="{{ $ujian->id }}" data-judul="{{ $ujian->judul_ujian }}"
                                                    data-deskripsi="{{ $ujian->deskripsi }}"
                                                    data-waktu-mulai="{{ $ujian->waktu_mulai }}"
                                                    data-waktu-selesai="{{ $ujian->waktu_selesai }}"
                                                    data-durasi="{{ $ujian->durasi_menit }}"
                                                    data-bobot="{{ $ujian->bobot_nilai }}"
                                                    data-passing="{{ $ujian->passing_grade }}"
                                                    data-random="{{ $ujian->random_soal }}"
                                                    data-tampilkan="{{ $ujian->tampilkan_hasil }}"
                                                    data-aturan="{{ $ujian->aturan_ujian }}">
                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form action="{{ route('ujians.destroy', [$kursus->id, $ujian->id]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus ujian ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <p class="text-muted small mb-3">{{ Str::limit($ujian->deskripsi, 100) }}</p>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center">
                                                <small class="text-muted d-block">Jumlah Soal</small>
                                                <strong>{{ $ujian->jumlah_soal }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center">
                                                <small class="text-muted d-block">Durasi</small>
                                                <strong>{{ $ujian->durasi_menit }} menit</strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center">
                                                <small class="text-muted d-block">Bobot</small>
                                                <strong>{{ $ujian->bobot_nilai }}%</strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 text-center">
                                                <small class="text-muted d-block">Passing Grade</small>
                                                <strong>{{ $ujian->passing_grade }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($ujian->waktu_mulai && $ujian->waktu_selesai)
                                        <div class="alert alert-info py-2 px-3 mb-2">
                                            <small>
                                                <i class="bi bi-calendar-event me-1"></i>
                                                {{ \Carbon\Carbon::parse($ujian->waktu_mulai)->format('d M Y H:i') }} -
                                                {{ \Carbon\Carbon::parse($ujian->waktu_selesai)->format('d M Y H:i') }}
                                            </small>
                                        </div>
                                    @endif

                                    <div class="d-flex gap-1">
                                        @if ($ujian->random_soal)
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-shuffle"></i> Random
                                            </span>
                                        @endif
                                        @if ($ujian->tampilkan_hasil)
                                            <span class="badge bg-success">
                                                <i class="bi bi-eye"></i> Tampilkan Hasil
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i>
                                            {{ $ujian->ujianResults->count() ?? 0 }} peserta mengerjakan
                                        </small>
                                        <a href="{{ route('soal-ujian.by-ujian', [$kursus->id, $ujian->id]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Kelola Soal
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-file-text" style="font-size: 64px; color: #dee2e6;"></i>
                    <p class="text-muted mt-3">Belum ada ujian untuk kursus ini.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahUjian">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Ujian Pertama
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Tambah Ujian -->
    <div class="modal fade" id="modalTambahUjian" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('ujians.store') }}">
                @csrf
                <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Ujian Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Judul Ujian <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul_ujian" required
                                placeholder="Contoh: Ujian Tengah Semester">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi ujian..."></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="datetime-local" class="form-control" name="waktu_mulai">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="datetime-local" class="form-control" name="waktu_selesai">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (Menit) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="durasi_menit" required value="60"
                                min="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bobot Nilai (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="bobot_nilai" required value="1.0"
                                min="0" max="100" step="0.1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Grade <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="passing_grade" required value="70"
                                min="0" max="100">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Soal <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="jumlah_soal" required value="10"
                                min="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="random_soal" value="1"
                                    id="randomSoal">
                                <label class="form-check-label" for="randomSoal">
                                    Acak Urutan Soal
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tampilkan_hasil" value="1"
                                    id="tampilkanHasil" checked>
                                <label class="form-check-label" for="tampilkanHasil">
                                    Tampilkan Hasil Ujian
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Aturan Ujian</label>
                            <textarea class="form-control" name="aturan_ujian" rows="3" placeholder="Contoh: 1. Kerjakan dengan jujur..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Ujian -->
    <div class="modal fade" id="modalEditUjian" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" id="formEditUjian">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Ujian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Judul Ujian <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="judul_ujian" id="edit_judul_ujian"
                                required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Mulai</label>
                            <input type="datetime-local" class="form-control" name="waktu_mulai" id="edit_waktu_mulai">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu Selesai</label>
                            <input type="datetime-local" class="form-control" name="waktu_selesai"
                                id="edit_waktu_selesai">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (Menit) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="durasi_menit" id="edit_durasi_menit"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bobot Nilai (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="bobot_nilai" id="edit_bobot_nilai" required
                                step="0.1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Grade <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="passing_grade" id="edit_passing_grade"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Soal <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="jumlah_soal" id="edit_jumlah_soal"
                                required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="random_soal" value="1"
                                    id="edit_random_soal">
                                <label class="form-check-label" for="edit_random_soal">
                                    Acak Urutan Soal
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="tampilkan_hasil" value="1"
                                    id="edit_tampilkan_hasil">
                                <label class="form-check-label" for="edit_tampilkan_hasil">
                                    Tampilkan Hasil Ujian
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Aturan Ujian</label>
                            <textarea class="form-control" name="aturan_ujian" id="edit_aturan_ujian" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update Ujian
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            'use strict';

            function getElement(selector) {
                return document.querySelector(selector);
            }

            function getElements(selector) {
                return document.querySelectorAll(selector);
            }

            // Handle edit button click
            getElements('.btn-edit-ujian').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const ujianId = this.getAttribute('data-id');
                    const judul = this.getAttribute('data-judul');
                    const deskripsi = this.getAttribute('data-deskripsi');
                    const waktuMulai = this.getAttribute('data-waktu-mulai');
                    const waktuSelesai = this.getAttribute('data-waktu-selesai');
                    const durasi = this.getAttribute('data-durasi');
                    const bobot = this.getAttribute('data-bobot');
                    const passing = this.getAttribute('data-passing');
                    const random = this.getAttribute('data-random') === '1';
                    const tampilkan = this.getAttribute('data-tampilkan') === '1';
                    const aturan = this.getAttribute('data-aturan');

                    // Set form values
                    getElement('#edit_judul_ujian').value = judul;
                    getElement('#edit_deskripsi').value = deskripsi || '';
                    getElement('#edit_waktu_mulai').value = waktuMulai || '';
                    getElement('#edit_waktu_selesai').value = waktuSelesai || '';
                    getElement('#edit_durasi_menit').value = durasi;
                    getElement('#edit_bobot_nilai').value = bobot;
                    getElement('#edit_passing_grade').value = passing;
                    getElement('#edit_random_soal').checked = random;
                    getElement('#edit_tampilkan_hasil').checked = tampilkan;
                    getElement('#edit_aturan_ujian').value = aturan || '';

                    // Set form action
                    const url = "{{ route('ujians.update', [$kursus->id, ':id']) }}".replace(
                        ':id', ujianId);
                    getElement('#formEditUjian').setAttribute('action', url);

                    // Show modal
                    const modal = new bootstrap.Modal(getElement('#modalEditUjian'));
                    modal.show();
                });
            });
        })();
    </script>
@endpush
