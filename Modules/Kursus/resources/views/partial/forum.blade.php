@extends('kursus::show')

@section('title', 'Forum Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Forum Kursus')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createForumModal">
                <i class="bi bi-plus-circle"></i> Tambah Forum
            </button>
        </div>

        <div class="col-md-12">
            @forelse ($kursus->forums as $index => $forum)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                @if ($forum->platform == 'telegram')
                                    <i class="bi bi-telegram" style="font-size: 2.5rem; color: #0088cc;"></i>
                                @elseif($forum->platform == 'whatsapp')
                                    <i class="bi bi-whatsapp" style="font-size: 2.5rem; color: #25D366;"></i>
                                @elseif($forum->platform == 'discord')
                                    <i class="bi bi-discord" style="font-size: 2.5rem; color: #5865F2;"></i>
                                @else
                                    <i class="bi bi-chat-dots" style="font-size: 2.5rem; color: #6c757d;"></i>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5 class="mb-1 p-3">
                                    {{ $forum->judul }}
                                    @if ($forum->is_aktif)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </h5>
                                <p class="text-muted mb-2">
                                    <small>
                                        <i class="bi bi-grid"></i>
                                        Platform: <strong>{{ ucfirst($forum->platform) }}</strong>
                                    </small>
                                </p>
                                @if ($forum->deskripsi)
                                    <p class="mb-2">{{ $forum->deskripsi }}</p>
                                @endif
                                @if ($forum->link_grup)
                                    <a href="{{ $forum->link_grup }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka Forum
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-success btn-edit" data-id="{{ $forum->id }}"
                                        data-judul="{{ $forum->judul }}" data-deskripsi="{{ $forum->deskripsi }}"
                                        data-platform="{{ $forum->platform }}" data-link="{{ $forum->link_grup }}"
                                        data-aktif="{{ $forum->is_aktif }}" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $forum->id }}"
                                        title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada forum diskusi. Silakan tambahkan forum untuk memfasilitasi
                    diskusi antar peserta.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createForumModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Forum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('forum.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="judul" class="form-label">Judul Forum <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('judul') is-invalid @enderror"
                                    id="judul" name="judul" value="{{ old('judul') }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="platform" class="form-label">Platform <span class="text-danger">*</span></label>
                                <select class="form-select @error('platform') is-invalid @enderror" id="platform"
                                    name="platform" required>
                                    <option value="">Pilih Platform</option>
                                    <option value="telegram" {{ old('platform') == 'telegram' ? 'selected' : '' }}>
                                        Telegram
                                    </option>
                                    <option value="whatsapp" {{ old('platform') == 'whatsapp' ? 'selected' : '' }}>
                                        WhatsApp
                                    </option>
                                    <option value="discord" {{ old('platform') == 'discord' ? 'selected' : '' }}>
                                        Discord
                                    </option>
                                    <option value="other" {{ old('platform') == 'other' ? 'selected' : '' }}>
                                        Lainnya
                                    </option>
                                </select>
                                @error('platform')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="link_grup" class="form-label">Link Grup/Forum <span
                                        class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('link_grup') is-invalid @enderror"
                                    id="link_grup" name="link_grup" value="{{ old('link_grup') }}"
                                    placeholder="https://..." required>
                                @error('link_grup')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input name="is_aktif" class="form-check-input" type="hidden" value="0">
                                    <input id="is_aktif" name="is_aktif" class="form-check-input" type="checkbox"
                                        value="1" {{ old('is_aktif', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_aktif">
                                        Forum Aktif (Tampilkan ke peserta)
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
    <div class="modal fade" id="editForumModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Forum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditForum" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Judul Forum <span class="text-danger">*</span></label>
                                <input type="text" id="edit_judul" name="judul" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Platform <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_platform" name="platform" required>
                                    <option value="">Pilih Platform</option>
                                    <option value="telegram">Telegram</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="discord">Discord</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Link Grup/Forum <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="edit_link_grup" name="link_grup"
                                    placeholder="https://..." required>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input name="is_aktif" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_is_aktif" name="is_aktif" class="form-check-input" type="checkbox"
                                        value="1">
                                    <label class="form-check-label" for="edit_is_aktif">
                                        Forum Aktif (Tampilkan ke peserta)
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let platform = $(this).data('platform');
            let link = $(this).data('link');
            let aktif = $(this).data('aktif');

            $('#edit_judul').val(judul);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_platform').val(platform);
            $('#edit_link_grup').val(link);
            $('#edit_is_aktif').prop('checked', aktif == 1);

            let url = "{{ route('forum.update', ':id') }}".replace(':id', id);
            $('#formEditForum').attr('action', url);
            $('#editForumModal').modal('show');
        });

        // Delete
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus forum ini?',
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
                        url: "{{ route('forum.destroy', ':id') }}".replace(':id', id),
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
