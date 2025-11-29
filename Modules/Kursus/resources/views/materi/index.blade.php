@extends('kursus::show')

@section('title', 'Materi Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Materi Kursus')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMateriModal">
                <i class="bi bi-plus-circle"></i> Tambah Materi
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
                                        <th>Judul Materi</th>
                                        <th>Tipe</th>
                                        <th>Durasi</th>
                                        <th>Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($modul->materis as $index => $item)
                                        <tr>
                                            <td>{{ $item->urutan }}</td>
                                            <td>
                                                {{ $item->judul_materi }}
                                                @if ($item->is_wajib)
                                                    <span class="badge bg-danger">Wajib</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->tipe_konten == 'video')
                                                    <span class="badge bg-primary">
                                                        <i class="bi bi-play-circle"></i> Video
                                                    </span>
                                                @elseif($item->tipe_konten == 'pdf')
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-file-pdf"></i> PDF
                                                    </span>
                                                @elseif($item->tipe_konten == 'dokumen')
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-file-earmark-text"></i> Dokumen
                                                    </span>
                                                @elseif($item->tipe_konten == 'link')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-link-45deg"></i> Link
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-file-text"></i> Teks
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $item->durasi_menit ?? '-' }} menit</td>
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
                                                        data-id="{{ $item->id }}" data-judul="{{ $item->judul_materi }}"
                                                        data-deskripsi="{{ $item->deskripsi }}"
                                                        data-tipe="{{ $item->tipe_konten }}"
                                                        data-file="{{ $item->file_path }}">
                                                        <i class="bi bi-eye-fill"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-success btn-edit"
                                                        data-id="{{ $item->id }}" data-modul="{{ $item->modul_id }}"
                                                        data-judul="{{ $item->judul_materi }}"
                                                        data-deskripsi="{{ $item->deskripsi }}"
                                                        data-tipe="{{ $item->tipe_konten }}"
                                                        data-file="{{ $item->file_path }}"
                                                        data-durasi="{{ $item->durasi_menit }}"
                                                        data-urutan="{{ $item->urutan }}"
                                                        data-wajib="{{ $item->is_wajib }}"
                                                        data-published="{{ $item->is_published }}">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-delete"
                                                        data-id="{{ $item->id }}">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada materi di modul ini
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
    <div class="modal fade" id="createMateriModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('materi.store') }}" method="POST" enctype="multipart/form-data">
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
                                <label for="judul_materi" class="form-label">Judul Materi <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('judul_materi') is-invalid @enderror"
                                    id="judul_materi" name="judul_materi" value="{{ old('judul_materi') }}" required>
                                @error('judul_materi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tipe_konten" class="form-label">Tipe Konten <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('tipe_konten') is-invalid @enderror" id="tipe_konten"
                                    name="tipe_konten" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="video">Video (YouTube)</option>
                                    <option value="pdf">PDF</option>
                                    <option value="dokumen">Dokumen (DOC, PPT)</option>
                                    <option value="link">Link Eksternal</option>
                                </select>
                                @error('tipe_konten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="durasi_menit" class="form-label">Durasi (menit)</label>
                                <input type="number" class="form-control" id="durasi_menit" name="durasi_menit"
                                    min="0" value="{{ old('durasi_menit') }}">
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            </div>

                            <!-- Input untuk Video YouTube -->
                            <div class="col-12" id="video-wrapper" style="display: none;">
                                <label for="youtube_url" class="form-label">URL YouTube <span
                                        class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="youtube_url" name="youtube_url"
                                    placeholder="https://www.youtube.com/watch?v=...">
                                <small class="text-muted">Masukkan link YouTube, contoh:
                                    https://www.youtube.com/watch?v=dQw4w9WgXcQ</small>
                            </div>

                            <!-- Input untuk Link -->
                            <div class="col-12" id="link-wrapper" style="display: none;">
                                <label for="link_url" class="form-label">URL Link <span
                                        class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="link_url" name="link_url"
                                    placeholder="https://example.com">
                                <small class="text-muted">Masukkan URL lengkap</small>
                            </div>

                            <!-- Input untuk Upload File -->
                            <div class="col-12" id="file-wrapper" style="display: none;">
                                <label for="file" class="form-label">Upload File <span
                                        class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file" name="file">
                                <small class="text-muted" id="file-help">Format: PDF, DOC, DOCX, PPT, PPTX (Max:
                                    10MB)</small>
                            </div>

                            <div class="col-md-6">
                                <label for="urutan" class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="urutan" name="urutan" min="1"
                                    value="{{ old('urutan') }}">
                                <small class="text-muted">Kosongkan untuk urutan otomatis</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input name="is_wajib" class="form-check-input" type="hidden" value="0">
                                    <input id="is_wajib" name="is_wajib" class="form-check-input" type="checkbox"
                                        value="1" {{ old('is_wajib') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_wajib">Materi Wajib</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">
                                        Publish Materi (Tampilkan ke peserta)
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
    <div class="modal fade" id="editMateriModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditMateri" method="POST" enctype="multipart/form-data">
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
                                <label class="form-label">Judul Materi <span class="text-danger">*</span></label>
                                <input type="text" id="edit_judul_materi" name="judul_materi" class="form-control"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipe Konten <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_tipe_konten" name="tipe_konten" required>
                                    <option value="video">Video (YouTube)</option>
                                    <option value="pdf">PDF</option>
                                    <option value="dokumen">Dokumen (DOC, PPT)</option>
                                    <option value="link">Link Eksternal</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Durasi (menit)</label>
                                <input type="number" class="form-control" id="edit_durasi_menit" name="durasi_menit"
                                    min="0">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                            </div>

                            <!-- Edit Video YouTube -->
                            <div class="col-12" id="edit-video-wrapper" style="display: none;">
                                <label class="form-label">URL YouTube <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="edit_youtube_url" name="youtube_url">
                            </div>

                            <!-- Edit Link -->
                            <div class="col-12" id="edit-link-wrapper" style="display: none;">
                                <label class="form-label">URL Link <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" id="edit_link_url" name="link_url">
                            </div>

                            <!-- Edit File -->
                            <div class="col-12" id="edit-file-wrapper" style="display: none;">
                                <label class="form-label">Upload File Baru (opsional)</label>
                                <input type="file" class="form-control" id="edit_file" name="file">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah file</small>
                                <div id="current-file-info" class="mt-2"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="edit_urutan" name="urutan"
                                    min="1">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <div class="form-check">
                                    <input name="is_wajib" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_is_wajib" name="is_wajib" class="form-check-input" type="checkbox"
                                        value="1">
                                    <label class="form-check-label" for="edit_is_wajib">Materi Wajib</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_is_published" name="is_published" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_is_published">
                                        Publish Materi (Tampilkan ke peserta)
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
    <div class="modal fade" id="viewMateriModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="view-judul">Detail Materi</h5>
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
        // Function untuk convert YouTube URL ke Embed URL
        function getYouTubeEmbedUrl(url) {
            let videoId = '';

            // Handle berbagai format URL YouTube
            if (url.includes('youtube.com/watch?v=')) {
                videoId = url.split('v=')[1].split('&')[0];
            } else if (url.includes('youtu.be/')) {
                videoId = url.split('youtu.be/')[1].split('?')[0];
            } else if (url.includes('youtube.com/embed/')) {
                return url; // Sudah format embed
            }

            return videoId ? `https://www.youtube.com/embed/${videoId}` : url;
        }

        // Toggle input berdasarkan tipe konten - CREATE
        $('#tipe_konten').on('change', function() {
            const tipe = $(this).val();

            // Hide semua wrapper
            $('#video-wrapper, #link-wrapper, #file-wrapper').hide();
            $('#youtube_url, #link_url, #file').prop('required', false);

            // Show wrapper sesuai tipe
            if (tipe === 'video') {
                $('#video-wrapper').show();
                $('#youtube_url').prop('required', true);
            } else if (tipe === 'link') {
                $('#link-wrapper').show();
                $('#link_url').prop('required', true);
            } else if (tipe === 'pdf' || tipe === 'dokumen') {
                $('#file-wrapper').show();
                $('#file').prop('required', true);

                // Update file help text
                if (tipe === 'pdf') {
                    $('#file-help').text('Format: PDF (Max: 10MB)');
                } else {
                    $('#file-help').text('Format: DOC, DOCX, PPT, PPTX (Max: 10MB)');
                }
            }
        });

        // Toggle input berdasarkan tipe konten - EDIT
        $('#edit_tipe_konten').on('change', function() {
            const tipe = $(this).val();

            // Hide semua wrapper
            $('#edit-video-wrapper, #edit-link-wrapper, #edit-file-wrapper').hide();

            // Show wrapper sesuai tipe
            if (tipe === 'video') {
                $('#edit-video-wrapper').show();
            } else if (tipe === 'link') {
                $('#edit-link-wrapper').show();
            } else if (tipe === 'pdf' || tipe === 'dokumen') {
                $('#edit-file-wrapper').show();
            }
        });

        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let modulId = $(this).data('modul');
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let tipe = $(this).data('tipe');
            let file = $(this).data('file');
            let durasi = $(this).data('durasi');
            let urutan = $(this).data('urutan');
            let wajib = $(this).data('wajib');
            let published = $(this).data('published');

            $('#edit_modul_id').val(modulId);
            $('#edit_judul_materi').val(judul);
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_tipe_konten').val(tipe);
            $('#edit_durasi_menit').val(durasi);
            $('#edit_urutan').val(urutan);
            $('#edit_is_wajib').prop('checked', wajib == 1);
            $('#edit_is_published').prop('checked', published == 1);

            // Hide semua wrapper
            $('#edit-video-wrapper, #edit-link-wrapper, #edit-file-wrapper').hide();

            // Show wrapper dan isi nilai sesuai tipe
            if (tipe === 'video') {
                $('#edit-video-wrapper').show();
                $('#edit_youtube_url').val(file);
            } else if (tipe === 'link') {
                $('#edit-link-wrapper').show();
                $('#edit_link_url').val(file);
            } else if (tipe === 'pdf' || tipe === 'dokumen') {
                $('#edit-file-wrapper').show();
                $('#current-file-info').html(
                    `<small class="text-info"><i class="bi bi-file-earmark"></i> File saat ini: ${file}</small>`
                );
            }

            let url = "{{ route('materi.update', ':id') }}".replace(':id', id);
            $('#formEditMateri').attr('action', url);
            $('#editMateriModal').modal('show');
        });

        // View
        $(document).on('click', '.btn-view', function() {
            let judul = $(this).data('judul');
            let deskripsi = $(this).data('deskripsi');
            let tipe = $(this).data('tipe');
            let file = $(this).data('file');

            $('#view-judul').text(judul);

            let content = `<div class="mb-3"><strong>Deskripsi:</strong><p>${deskripsi || '-'}</p></div>`;

            if (tipe === 'video') {
                let embedUrl = getYouTubeEmbedUrl(file);
                content += `<div class="ratio ratio-16x9">
                    <iframe src="${embedUrl}" allowfullscreen frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>`;
            } else if (tipe === 'link') {
                content += `<p><strong>Link:</strong> <a href="${file}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right"></i> Buka Link
                </a></p>`;
            } else if (tipe === 'pdf') {
                content += `<p><strong>File PDF:</strong> <a href="/storage/materi/${file}" target="_blank" class="btn btn-danger btn-sm">
                    <i class="bi bi-file-pdf"></i> Lihat PDF
                </a></p>`;
            } else if (tipe === 'dokumen') {
                content += `<p><strong>File Dokumen:</strong> <a href="/storage/materi/${file}" download class="btn btn-info btn-sm">
                    <i class="bi bi-download"></i> Download Dokumen
                </a></p>`;
            }

            $('#view-content').html(content);
            $('#viewMateriModal').modal('show');
        });

        // Delete
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus materi ini?',
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
                        url: "{{ route('materi.destroy', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE",
                        },
                        beforeSend() {
                            Swal.showLoading();
                        },
                        success(res) {
                            window.location.href = res.redirect;
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
