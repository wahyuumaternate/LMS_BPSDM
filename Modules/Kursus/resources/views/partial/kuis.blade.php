@extends('kursus::show')

@section('title', 'Quiz Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Quiz Kursus')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                <i class="bi bi-plus-circle"></i> Tambah Quiz
            </button>
        </div>
        <div class="col-md-12">
            @forelse ($kursus->modul as $modul)
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-folder"></i> {{ $modul->nama_modul }}
                            @if ($modul->deskripsi)
                                <small class="text-muted d-block">{{ $modul->deskripsi }}</small>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Judul Quiz</th>
                                        <th>Jumlah Soal</th>
                                        <th>Durasi</th>
                                        <th>Passing Grade</th>
                                        <th>Max Attempt</th>
                                        <th>Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($modul->quizzes as $index => $quiz)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $quiz->judul_quiz }}
                                                @if ($quiz->random_soal)
                                                    <span class="badge bg-info">Random</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $quiz->soalQuiz->count() ?? 0 }}
                                                    Soal</span>
                                            </td>
                                            <td>{{ $quiz->durasi_menit }} menit</td>
                                            <td>
                                                <span class="badge bg-success">{{ $quiz->passing_grade }}%</span>
                                            </td>
                                            <td>
                                                @if ($quiz->max_attempt == 0)
                                                    <span class="badge bg-secondary">Unlimited</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $quiz->max_attempt }}x</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($quiz->is_published)
                                                    <span class="badge bg-success">Published</span>
                                                @else
                                                    <span class="badge bg-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-info btn-view"
                                                        data-id="{{ $quiz->id }}" data-judul="{{ $quiz->judul_quiz }}"
                                                        data-deskripsi="{{ $quiz->deskripsi }}"
                                                        data-durasi="{{ $quiz->durasi_menit }}"
                                                        data-passing="{{ $quiz->passing_grade }}"
                                                        data-jumlah="{{ $quiz->jumlah_soal }}"
                                                        data-random="{{ $quiz->random_soal }}"
                                                        data-tampilkan="{{ $quiz->tampilkan_hasil }}"
                                                        data-attempt="{{ $quiz->max_attempt }}">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success btn-edit"
                                                        data-id="{{ $quiz->id }}" data-modul="{{ $quiz->modul_id }}"
                                                        data-judul="{{ $quiz->judul_quiz }}"
                                                        data-deskripsi="{{ $quiz->deskripsi }}"
                                                        data-durasi="{{ $quiz->durasi_menit }}"
                                                        data-bobot="{{ $quiz->bobot_nilai }}"
                                                        data-passing="{{ $quiz->passing_grade }}"
                                                        data-jumlah="{{ $quiz->jumlah_soal }}"
                                                        data-random="{{ $quiz->random_soal }}"
                                                        data-tampilkan="{{ $quiz->tampilkan_hasil }}"
                                                        data-attempt="{{ $quiz->max_attempt }}"
                                                        data-published="{{ $quiz->is_published }}">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <a href="{{ route('quizzes.show', $quiz->id) }}"
                                                        class="btn btn-sm btn-primary" title="Kelola Soal">
                                                        <i class="bi bi-list-check"></i>
                                                    </a>
                                                    <a href="{{ route('hasil-quiz.index', $quiz->id) }}"
                                                        class="btn btn-sm btn-primary" title="Kelola Soal">
                                                       <i class="bi bi-trophy"></i>

                                                    </a>
                                                    <button class="btn btn-sm btn-danger btn-delete"
                                                        data-id="{{ $quiz->id }}">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Belum ada quiz di modul ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada modul. Silakan buat modul terlebih dahulu di tab
                    <a href="{{ route('course.modul', $kursus->id) }}">Modul</a>.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('quizzes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="modul_id" class="form-label">Modul <span class="text-danger">*</span></label>
                                <select class="form-select @error('modul_id') is-invalid @enderror" id="modul_id"
                                    name="modul_id" required>
                                    <option value="">Pilih Modul</option>
                                    @foreach ($kursus->modul as $modul)
                                        <option value="{{ $modul->id }}">{{ $modul->nama_modul }}</option>
                                    @endforeach
                                </select>
                                @error('modul_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="judul_quiz" class="form-label">Judul Quiz <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('judul_quiz') is-invalid @enderror"
                                    id="judul_quiz" name="judul_quiz" value="{{ old('judul_quiz') }}" required>
                                @error('judul_quiz')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="durasi_menit" class="form-label">Durasi (menit) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="durasi_menit" name="durasi_menit"
                                    min="0" value="{{ old('durasi_menit', 0) }}" required>
                                <small class="text-muted">0 = tidak ada batas waktu</small>
                            </div>

                            <div class="col-md-6">
                                <label for="bobot_nilai" class="form-label">Bobot Nilai (%)</label>
                                <input type="number" step="0.01" class="form-control" id="bobot_nilai"
                                    name="bobot_nilai" min="0" max="100" value="{{ old('bobot_nilai') }}">
                                <small class="text-muted">Kosongkan jika tidak ada bobot</small>
                            </div>

                            <div class="col-md-6">
                                <label for="passing_grade" class="form-label">Passing Grade (%) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="passing_grade" name="passing_grade"
                                    min="0" max="100" value="{{ old('passing_grade', 70) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="max_attempt" class="form-label">Maksimal Percobaan</label>
                                <input type="number" class="form-control" id="max_attempt" name="max_attempt"
                                    min="0" value="{{ old('max_attempt', 0) }}">
                                <small class="text-muted">0 = unlimited</small>
                            </div>

                            <div class="col-md-6">
                                <label for="jumlah_soal" class="form-label">Jumlah Soal Ditampilkan</label>
                                <input type="number" class="form-control" id="jumlah_soal" name="jumlah_soal"
                                    min="0" value="{{ old('jumlah_soal', 0) }}">
                                <small class="text-muted">0 = tampilkan semua soal</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input name="random_soal" class="form-check-input" type="hidden" value="0">
                                    <input id="random_soal" name="random_soal" class="form-check-input" type="checkbox"
                                        value="1" {{ old('random_soal') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="random_soal">Acak Urutan Soal</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input name="tampilkan_hasil" class="form-check-input" type="hidden"
                                        value="0">
                                    <input id="tampilkan_hasil" name="tampilkan_hasil" class="form-check-input"
                                        type="checkbox" value="1"
                                        {{ old('tampilkan_hasil', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tampilkan_hasil">
                                        Tampilkan Hasil Quiz ke Peserta
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publish Quiz (Tampilkan ke peserta)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditQuiz" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Modul <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_modul_id" name="modul_id" required>
                                    <option value="">Pilih Modul</option>
                                    @foreach ($kursus->modul as $modul)
                                        <option value="{{ $modul->id }}">{{ $modul->nama_modul }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Judul Quiz <span class="text-danger">*</span></label>
                                <input type="text" id="edit_judul_quiz" name="judul_quiz" class="form-control"
                                    required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_durasi_menit" name="durasi_menit"
                                    min="0" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Bobot Nilai (%)</label>
                                <input type="number" step="0.01" class="form-control" id="edit_bobot_nilai"
                                    name="bobot_nilai" min="0" max="100">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Passing Grade (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_passing_grade" name="passing_grade"
                                    min="0" max="100" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Maksimal Percobaan</label>
                                <input type="number" class="form-control" id="edit_max_attempt" name="max_attempt"
                                    min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jumlah Soal Ditampilkan</label>
                                <input type="number" class="form-control" id="edit_jumlah_soal" name="jumlah_soal"
                                    min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input name="random_soal" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_random_soal" name="random_soal" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_random_soal">Acak Urutan Soal</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check mb-2">
                                    <input name="tampilkan_hasil" class="form-check-input" type="hidden"
                                        value="0">
                                    <input id="edit_tampilkan_hasil" name="tampilkan_hasil" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_tampilkan_hasil">
                                        Tampilkan Hasil Quiz ke Peserta
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_is_published">
                                        Publish Quiz (Tampilkan ke peserta)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view-judul">Detail Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="view-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

    <script>
        // Edit Quiz
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let modulId = $(this).data('modul');
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let durasi = $(this).data('durasi');
            let bobot = $(this).data('bobot');
            let passing = $(this).data('passing');
            let jumlah = $(this).data('jumlah');
            let random = $(this).data('random');
            let tampilkan = $(this).data('tampilkan');
            let attempt = $(this).data('attempt');
            let published = $(this).data('published');

            $('#edit_modul_id').val(modulId);
            $('#edit_judul_quiz').val(judul);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_durasi_menit').val(durasi);
            $('#edit_bobot_nilai').val(bobot);
            $('#edit_passing_grade').val(passing);
            $('#edit_jumlah_soal').val(jumlah);
            $('#edit_max_attempt').val(attempt);
            $('#edit_random_soal').prop('checked', random == 1);
            $('#edit_tampilkan_hasil').prop('checked', tampilkan == 1);
            $('#edit_is_published').prop('checked', published == 1);

            let url = "{{ route('quizzes.update', ':id') }}".replace(':id', id);
            $('#formEditQuiz').attr('action', url);
            $('#editQuizModal').modal('show');
        });

        // View Quiz
        $(document).on('click', '.btn-view', function() {
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let durasi = $(this).data('durasi');
            let passing = $(this).data('passing');
            let jumlah = $(this).data('jumlah');
            let random = $(this).data('random');
            let tampilkan = $(this).data('tampilkan');
            let attempt = $(this).data('attempt');

            $('#view-judul').text(judul);

            let content = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Deskripsi:</strong>
                        <p>${deskripsi || '-'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Durasi:</strong>
                        <p>${durasi} menit ${durasi == 0 ? '(Unlimited)' : ''}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Passing Grade:</strong>
                        <p><span class="badge bg-success">${passing}%</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Jumlah Soal:</strong>
                        <p>${jumlah} soal ${jumlah == 0 ? '(Semua soal)' : ''}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Maksimal Percobaan:</strong>
                        <p>${attempt == 0 ? 'Unlimited' : attempt + 'x'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Pengaturan:</strong>
                        <p>
                            ${random == 1 ? '<span class="badge bg-info">Soal Diacak</span>' : '<span class="badge bg-secondary">Soal Berurutan</span>'}
                            ${tampilkan == 1 ? '<span class="badge bg-success ms-1">Tampilkan Hasil</span>' : '<span class="badge bg-warning ms-1">Sembunyikan Hasil</span>'}
                        </p>
                    </div>
                </div>
            `;

            $('#view-content').html(content);
            $('#viewQuizModal').modal('show');
        });

        // Delete Quiz
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus quiz ini?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('quizzes.destroy', ':id') }}".replace(':id', id),
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        beforeSend() {
                            Swal.showLoading();
                        },
                        success(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Quiz berhasil dihapus'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error(err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Terjadi kesalahan saat menghapus data.'
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
