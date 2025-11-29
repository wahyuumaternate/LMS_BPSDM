@extends('kursus::show')

@section('title', 'Modul Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Modul Kursus')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModulModal">
                <i class="bi bi-plus-circle"></i> Tambah Modul
            </button>
        </div>

        <div class="col-md-12">
            @forelse ($kursus->modul as $index => $modul)
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-folder"></i>
                                <span class="badge bg-secondary me-2">{{ $modul->urutan }}</span>
                                {{ $modul->nama_modul }}
                                @if ($modul->is_published)
                                    <span class="badge bg-success ms-2">Published</span>
                                @else
                                    <span class="badge bg-warning ms-2">Draft</span>
                                @endif
                            </h5>
                            @if ($modul->deskripsi)
                                <small class="text-muted d-block mt-1">{{ $modul->deskripsi }}</small>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-success btn-edit" data-id="{{ $modul->id }}"
                                data-nama="{{ $modul->nama_modul }}" data-deskripsi="{{ $modul->deskripsi }}"
                                data-urutan="{{ $modul->urutan }}" data-published="{{ $modul->is_published }}">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $modul->id }}">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-file-earmark-text text-primary me-2"></i>
                                    <span><strong>{{ $modul->jumlah_materi }}</strong> Materi</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-clock text-info me-2"></i>
                                    <span><strong>{{ $modul->total_durasi }}</strong> Menit</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-question-circle text-warning me-2"></i>
                                    <span><strong>{{ $modul->quizzes->count() }}</strong> Quiz</span>
                                </div>
                            </div>
                        </div>

                        @if ($modul->materis->count() > 0)
                            <hr>
                            <h6 class="mb-3">Daftar Materi:</h6>
                            <div class="list-group">
                                @foreach ($modul->materis as $materi)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="badge bg-secondary me-2">{{ $materi->urutan }}</span>
                                                {{ $materi->judul_materi }}
                                                @if ($materi->is_wajib)
                                                    <span class="badge bg-danger ms-1">Wajib</span>
                                                @endif
                                                @if ($materi->tipe_konten == 'video')
                                                    <span class="badge bg-primary ms-1">
                                                        <i class="bi bi-play-circle"></i> Video
                                                    </span>
                                                @elseif($materi->tipe_konten == 'pdf')
                                                    <span class="badge bg-danger ms-1">
                                                        <i class="bi bi-file-pdf"></i> PDF
                                                    </span>
                                                @elseif($materi->tipe_konten == 'dokumen')
                                                    <span class="badge bg-info ms-1">
                                                        <i class="bi bi-file-earmark-text"></i> Dokumen
                                                    </span>
                                                @else
                                                    <span class="badge bg-success ms-1">
                                                        <i class="bi bi-link-45deg"></i> Link
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> {{ $materi->durasi_menit ?? 0 }} menit
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle"></i> Belum ada materi di modul ini.
                                <a href="{{ route('course.materi', $kursus->id) }}" class="alert-link">Tambah materi
                                    sekarang</a>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada modul. Silakan tambahkan modul pertama untuk kursus ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModulModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('modul.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="nama_modul" class="form-label">Nama Modul <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_modul') is-invalid @enderror"
                                    id="nama_modul" name="nama_modul" value="{{ old('nama_modul') }}" required>
                                @error('nama_modul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="urutan" class="form-label">Urutan</label>
                                <input type="number" class="form-control @error('urutan') is-invalid @enderror"
                                    id="urutan" name="urutan" min="1" value="{{ old('urutan') }}">
                                <small class="text-muted">Kosongkan untuk urutan otomatis</small>
                                @error('urutan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publish Modul (Tampilkan ke peserta)
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
    <div class="modal fade" id="editModulModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditModul" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Modul <span class="text-danger">*</span></label>
                                <input type="text" id="edit_nama_modul" name="nama_modul" class="form-control"
                                    required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="4"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="edit_urutan" name="urutan"
                                    min="1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_is_published">
                                        Publish Modul (Tampilkan ke peserta)
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
@endsection

@push('scripts')

    <script>
        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let deskripsi = $(this).data('deskripsi');
            let urutan = $(this).data('urutan');
            let published = $(this).data('published');

            $('#edit_nama_modul').val(nama);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_urutan').val(urutan);
            $('#edit_is_published').prop('checked', published == 1);

            let url = "{{ route('modul.update', ':id') }}".replace(':id', id);
            $('#formEditModul').attr('action', url);
            $('#editModulModal').modal('show');
        });

        // Delete
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus modul ini?',
                text: 'Semua materi di dalam modul ini juga akan terhapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('modul.destroy', ':id') }}".replace(':id', id),
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
