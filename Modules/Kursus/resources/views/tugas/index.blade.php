@extends('kursus::show')

@section('title', 'Tugas Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Tugas Kursus')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTugasModal">
                <i class="bi bi-plus-circle"></i> Tambah Tugas
            </button>
        </div>

        <div class="col-md-12">
            @forelse ($kursus->modul as $modul)
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-folder"></i> {{ $modul->nama_modul }}
                            @if ($modul->deskripsi)
                                <small class="text-muted d-block mt-1">{{ $modul->deskripsi }}</small>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Judul Tugas</th>
                                        <th>Deadline</th>
                                        <th>Nilai Maks</th>
                                        <th>Bobot</th>
                                        <th>Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($modul->tugas as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                {{ $item->judul }}
                                                @if ($item->isOverdue())
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->tanggal_deadline)
                                                    <small>
                                                        <i class="bi bi-calendar-event"></i>
                                                        {{ $item->tanggal_deadline->format('d M Y') }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->nilai_maksimal ?? 100 }}</td>
                                            <td>{{ $item->bobot_nilai ?? 0 }}%</td>
                                            <td>
                                                @if ($item->is_published)
                                                    <span class="badge bg-success">Published</span>
                                                @else
                                                    <span class="badge bg-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-info btn-view"
                                                        data-id="{{ $item->id }}" data-judul="{{ $item->judul }}"
                                                        data-deskripsi="{{ $item->deskripsi }}"
                                                        data-petunjuk="{{ $item->petunjuk }}"
                                                        data-file="{{ $item->file_tugas }}"
                                                        data-mulai="{{ $item->tanggal_mulai?->format('d M Y') }}"
                                                        data-deadline="{{ $item->tanggal_deadline?->format('d M Y') }}"
                                                        data-nilai="{{ $item->nilai_maksimal }}"
                                                        data-bobot="{{ $item->bobot_nilai }}" title="Lihat Detail">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>

                                                    <a href="{{ route('tugas.submission', $item->id) }}"
                                                        class="btn btn-sm btn-primary" title="Lihat Pengumpulan">
                                                        <i class="bi bi-file-earmark-check"></i>
                                                    </a>

                                                    <button class="btn btn-sm btn-success btn-edit"
                                                        data-id="{{ $item->id }}" data-modul="{{ $item->modul_id }}"
                                                        data-judul="{{ $item->judul }}"
                                                        data-deskripsi="{{ $item->deskripsi }}"
                                                        data-petunjuk="{{ $item->petunjuk }}"
                                                        data-mulai="{{ $item->tanggal_mulai?->format('Y-m-d') }}"
                                                        data-deadline="{{ $item->tanggal_deadline?->format('Y-m-d') }}"
                                                        data-nilai="{{ $item->nilai_maksimal }}"
                                                        data-bobot="{{ $item->bobot_nilai }}"
                                                        data-published="{{ $item->is_published }}" title="Edit">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>

                                                    <button class="btn btn-sm btn-danger btn-delete"
                                                        data-id="{{ $item->id }}" title="Hapus">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Belum ada tugas di modul ini
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
                    href="{{ route('course.modul', $kursus->id) }}">Modul</a>.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createTugasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('tugas.store') }}" method="POST" enctype="multipart/form-data">
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
                                <label for="judul" class="form-label">Judul Tugas <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                    id="judul" name="judul" value="{{ old('judul') }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label for="petunjuk" class="form-label">Petunjuk Pengerjaan</label>
                                <textarea class="form-control" id="petunjuk" name="petunjuk" rows="4">{{ old('petunjuk') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label for="file_tugas" class="form-label">File Tugas (opsional)</label>
                                <input type="file" class="form-control" id="file_tugas" name="file_tugas">
                                <small class="text-muted">Format: PDF, DOC, DOCX, PPT, PPTX (Max: 10MB)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                                    value="{{ old('tanggal_mulai') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="tanggal_deadline" class="form-label">Deadline <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_deadline') is-invalid @enderror"
                                    id="tanggal_deadline" name="tanggal_deadline" value="{{ old('tanggal_deadline') }}"
                                    required>
                                @error('tanggal_deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nilai_maksimal" class="form-label">Nilai Maksimal</label>
                                <input type="number" class="form-control" id="nilai_maksimal" name="nilai_maksimal"
                                    min="0" value="{{ old('nilai_maksimal', 100) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="bobot_nilai" class="form-label">Bobot Nilai (%)</label>
                                <input type="number" class="form-control" id="bobot_nilai" name="bobot_nilai"
                                    min="0" max="100" value="{{ old('bobot_nilai', 0) }}">
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publish Tugas (Tampilkan ke peserta)
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
    <div class="modal fade" id="editTugasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditTugas" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
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
                                <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                                <input type="text" id="edit_judul" name="judul" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Petunjuk Pengerjaan</label>
                                <textarea class="form-control" id="edit_petunjuk" name="petunjuk" rows="4"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">File Tugas Baru (opsional)</label>
                                <input type="file" class="form-control" id="edit_file_tugas" name="file_tugas">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah file</small>
                                <div id="current-file-info" class="mt-2"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal_deadline"
                                    name="tanggal_deadline" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nilai Maksimal</label>
                                <input type="number" class="form-control" id="edit_nilai_maksimal"
                                    name="nilai_maksimal" min="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Bobot Nilai (%)</label>
                                <input type="number" class="form-control" id="edit_bobot_nilai" name="bobot_nilai"
                                    min="0" max="100">
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_is_published">
                                        Publish Tugas (Tampilkan ke peserta)
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
    <div class="modal fade" id="viewTugasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view-judul">Detail Tugas</h5>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Edit - Update untuk handle dropdown item
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault(); // Prevent dropdown from closing

            let id = $(this).data('id');
            let modulId = $(this).data('modul');
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let petunjuk = $(this).data('petunjuk');
            let mulai = $(this).data('mulai');
            let deadline = $(this).data('deadline');
            let nilai = $(this).data('nilai');
            let bobot = $(this).data('bobot');
            let published = $(this).data('published');

            $('#edit_modul_id').val(modulId);
            $('#edit_judul').val(judul);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_petunjuk').val(petunjuk);
            $('#edit_tanggal_mulai').val(mulai);
            $('#edit_tanggal_deadline').val(deadline);
            $('#edit_nilai_maksimal').val(nilai);
            $('#edit_bobot_nilai').val(bobot);
            $('#edit_is_published').prop('checked', published == 1);

            let url = "{{ route('tugas.update', ':id') }}".replace(':id', id);
            $('#formEditTugas').attr('action', url);
            $('#editTugasModal').modal('show');
        });

        // View
        $(document).on('click', '.btn-view', function() {
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let petunjuk = $(this).data('petunjuk');
            let file = $(this).data('file');
            let mulai = $(this).data('mulai');
            let deadline = $(this).data('deadline');
            let nilai = $(this).data('nilai');
            let bobot = $(this).data('bobot');

            $('#view-judul').text(judul);

            let content = `
                <div class="mb-3">
                    <strong>Deskripsi:</strong>
                    <p>${deskripsi || '-'}</p>
                </div>
                <div class="mb-3">
                    <strong>Petunjuk Pengerjaan:</strong>
                    <p>${petunjuk || '-'}</p>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tanggal Mulai:</strong>
                        <p>${mulai || '-'}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Deadline:</strong>
                        <p>${deadline || '-'}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nilai Maksimal:</strong>
                        <p>${nilai || 100}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Bobot Nilai:</strong>
                        <p>${bobot || 0}%</p>
                    </div>
                </div>
            `;

            if (file) {
                content += `
                    <div class="mb-3">
                        <strong>File Tugas:</strong><br>
                        <a href="/storage/tugas/${file}" target="_blank" class="btn btn-primary btn-sm mt-2">
                            <i class="bi bi-download"></i> Download File
                        </a>
                    </div>
                `;
            }

            $('#view-content').html(content);
            $('#viewTugasModal').modal('show');
        });

        // Delete - Update untuk handle dropdown item
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus tugas ini?',
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
                        url: "{{ route('tugas.destroy', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE",
                        },
                        beforeSend() {
                            Swal.fire({
                                title: 'Menghapus...',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = res.redirect;
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message
                                });
                            }
                        },
                        error(xhr) {
                            let errorMessage = 'Terjadi kesalahan saat menghapus data.';

                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: errorMessage
                            });
                        }
                    });
                }
            });
        });
    </script>
@endpush
