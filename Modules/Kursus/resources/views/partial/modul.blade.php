@extends('kursus::show')

@section('title', 'Modul Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Modul Kursus')

@section('detail-content')
    <div class="">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModulModal">
            <i class="bi bi-plus-circle"></i> Tambah Modul
        </button>
    </div>
    <div class="mt-4">
        <div class="accordion" id="accordionModul">
            @forelse ($kursus->modul as $modul)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $modul->id }}">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $modul->id }}" aria-expanded="true"
                            aria-controls="collapse{{ $modul->id }}">
                            <b>
                                {{ "Modul $modul->urutan: $modul->nama_modul" }}
                            </b>
                            @if ($modul->is_published)
                                <span class="badge bg-success ms-3 ">Publish</span>
                            @else
                                <span class="badge bg-warning ms-3 ">Draft</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapse{{ $modul->id }}" class="accordion-collapse collapse"
                        aria-labelledby="heading{{ $modul->id }}" data-bs-parent="#accordionModul" style="">
                        <div class="accordion-body">
                            <div class="d-flex justify-content-between mb-4">
                                <div>
                                    <button type="button" id="btn-create-materi" class="btn btn-sm btn-primary"
                                        data-modul="{{ $modul->id }}">
                                        <i class="bi bi-plus-circle"></i> Tambah Materi
                                    </button>
                                </div>
                                <div class="d-flex gap-1">
                                    <button id="btn-edit-modul" class="btn btn-sm btn-success"
                                        data-id="{{ $modul->id }}" data-nama="{{ $modul->nama_modul }}"
                                        data-urutan="{{ $modul->urutan }}" data-deskripsi="{{ $modul->deskripsi }}"
                                        data-publish="{{ $modul->is_published }}">
                                        <i class="bi bi-pencil-fill"></i> Edit Modul
                                    </button>
                                    <button id="btn-delete-modul" class="btn btn-sm btn-danger"
                                        data-id="{{ $modul->id }}"
                                        data-judul="{{ 'Modul ' . $modul->urutan . ': ' . $modul->nama_modul }}">
                                        <i class="bi bi-trash-fill"></i> Hapus Modul
                                    </button>
                                </div>
                            </div>

                            {!! $modul->deskripsi !!}

                            {{-- Materi --}}
                            @forelse ($modul->materis as $materi)
                                <div class="border-top py-3">
                                    <div class="d-flex align-items-start justify-content-start">
                                        <h5>{{ "Materi $materi->urutan: $materi->judul_materi" }}</h5>
                                        <div>
                                            @if ($materi->published_at)
                                                <span class="badge rounded bg-success ms-3">Publish</span>
                                            @else
                                                <span class="badge rounded bg-warning ms-3">Draft</span>
                                            @endif
                                            @if ($materi->is_wajib)
                                                <span class="badge rounded bg-info ms-1">Wajib</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        {!! $materi->deskripsi !!}
                                    </div>
                                    @php
                                        $link;
                                        $href;
                                        if ($materi->tipe_konten == 'link') {
                                            $link = $materi->file_path;
                                            $href = $materi->file_path;
                                        } else {
                                            $link = "";
                                            $href = asset(
                                                '/storage/materi/files/' .
                                                    $materi->tipe_konten .
                                                    '/' .
                                                    $materi->file_path,
                                            );
                                        }
                                    @endphp

                                    <a href="{{ $href }}" target="_blank" class="btn btn-outline-primary btn-sm"><i
                                            class="bi bi-eye"></i>Lihat
                                        Konten</a>
                                    <button id="btn-edit-materi" class="btn btn-sm btn-outline-success"
                                        data-id="{{ $materi->id }}" data-nama="{{ $materi->judul_materi }}"
                                        data-urutan="{{ $materi->urutan }}"
                                        data-deskripsi="{{ $materi->deskripsi ?? '' }}"
                                        data-tipe="{{ $materi->tipe_konten }}" data-link="{{ $link ?? '' }}"
                                        data-publish="{{ $materi->published_at ? '1' : '0' }}"
                                        data-wajib="{{ $materi->is_wajib }}">
                                        <i class="bi bi-pencil-fill"></i> Edit Materi
                                    </button>
                                    <button id="btn-delete-materi" class="btn btn-sm btn-outline-danger" data-id="{{ $materi->id }}"
                                        data-judul="{{ 'Materi ' . $materi->urutan . ': ' . $materi->judul_materi }}">
                                        <i class="bi bi-trash-fill"></i> Hapus Materi
                                    </button>
                                </div>
                            @empty
                                <p class="text-center">Belum ada materi</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center">Belum ada modul</div>
            @endforelse
        </div>
    </div>


    {{-- Create Modul --}}
    <div class="modal fade" id="createModulModal" tabindex="-1" aria-labelledby="createModulModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModulModalLabel">Tambah Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('modul.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                        <div class="row g-3">
                            <div class="">
                                <label for="nama_modul" class="col-form-label">Nama Modul<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="text" class="form-control @error('nama_modul') is-invalid @enderror"
                                        id="nama_modul" name="nama_modul" value="{{ old('nama_modul') }}" required>
                                    @error('nama_modul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="urutan" class="col-form-label">Urutan<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="number" min="1"
                                        class="form-control @error('urutan') is-invalid @enderror" id="urutan"
                                        name="urutan" value="{{ old('urutan') }}" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="deskripsi" class="col-form-label">Deskripsi</label>
                                <div class="">
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi"
                                        rows="3">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="publish" name="is_published" class="form-check-input" type="checkbox"
                                        value="1">
                                    <label class="form-check-label" for="publish">
                                        Publish
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modul --}}
    <div class="modal fade" id="editModulModal" tabindex="-1" aria-labelledby="editModulModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModulModalLabel">Edit Modul</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditModul" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                        <div class="row g-3">
                            <div class="">
                                <label for="edit_nama_modul" class="col-form-label">Nama Modul<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="text" class="form-control @error('nama_modul') is-invalid @enderror"
                                        id="edit_nama_modul" name="nama_modul" value="{{ old('nama_modul') }}" required>
                                    @error('nama_modul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="edit_urutan" class="col-form-label">Urutan<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="number" min="1"
                                        class="form-control @error('urutan') is-invalid @enderror" id="edit_urutan"
                                        name="urutan" value="{{ old('urutan') }}" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="edit_deskripsi" class="col-form-label">Deskripsi</label>
                                <div class="">
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="edit_deskripsi" name="deskripsi"
                                        rows="3">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <div class="form-check">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_publish" name="is_published" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_publish">
                                        Publish
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


    {{-- Create Materi --}}
    <div class="modal fade" id="createMateriModal" tabindex="-1" aria-labelledby="createMateriModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createMateriModalLabel">Tambah Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('modul.materi.store') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" id="kursus_id" name="kursus_id" value="{{ $kursus->id }}">
                        <input type="hidden" id="modul_id" name="modul_id" value="{{ old('modul_id') }}">
                        <div class="row g-3">
                            <div class="">
                                <label for="judul_materi" class="col-form-label">Judul Materi<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="text" class="form-control @error('judul_materi') is-invalid @enderror"
                                        id="judul_materi" name="judul_materi" value="{{ old('judul_materi') }}"
                                        required>
                                    @error('judul_materi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="urutan" class="col-form-label">Urutan<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="number" min="1"
                                        class="form-control @error('urutan') is-invalid @enderror" id="urutan"
                                        name="urutan" value="{{ old('urutan') }}" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="deskripsi" class="col-form-label">Deskripsi</label>
                                <div class="">
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi"
                                        rows="3">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tipe_konten" class="form-label">Tipe Konten <span
                                        class="text-danger">*</span></label>
                                <select id="tipe_konten" name="tipe_konten"
                                    class="form-select @error('tipe_konten') is-invalid @enderror" required>
                                    <option value="" disabled selected>Pilih Tipe Konten</option>
                                    <option value="pdf" @selected(old('tipe_konten') == 'pdf')>PDF</option>
                                    <option value="doc" @selected(old('tipe_konten') == 'doc')>Doc</option>
                                    <option value="video" @selected(old('tipe_konten') == 'video')>Video</option>
                                    <option value="audio" @selected(old('tipe_konten') == 'audio')>Audio</option>
                                    <option value="gambar" @selected(old('tipe_konten') == 'gambar')>Gambar</option>
                                    <option value="link" @selected(old('tipe_konten') == 'link')>Link</option>
                                    <option value="scorm" @selected(old('tipe_konten') == 'scorm')>Scorm</option>
                                </select>
                                @error('tipe_konten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="">
                                <label for="path_link" class="col-form-label">Link/URL</label>
                                <div class="">
                                    <input type="text" class="form-control @error('path_link') is-invalid @enderror"
                                        id="path_link" name="path_link" value="{{ old('path_link') }}">
                                    @error('path_link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Biarkan kosong jika tidak memilih link di tipe konten</div>
                                </div>
                            </div>
                            <div class="">
                                <label for="file" class="col-form-label">File</label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror"
                                    id="file" name="file" value="{{ old('file') }}">
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload sesuai tipe konten yang dipilih</div>
                            </div>
                            <div class="row g-3 ms-2">
                                <div class="form-check col-3">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="publish" name="is_published" class="form-check-input" type="checkbox"
                                        value="1">
                                    <label class="form-check-label" for="publish">
                                        Publish
                                    </label>
                                </div>
                                <div class="form-check col-3">
                                    <input name="is_wajib" class="form-check-input" type="hidden" value="0">
                                    <input id="wajib" name="is_wajib" class="form-check-input" type="checkbox"
                                        value="1">
                                    <label class="form-check-label" for="wajib">
                                        Wajib
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Materi --}}
    <div class="modal fade" id="editMateriModal" tabindex="-1" aria-labelledby="editMateriModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createMateriModalLabel">Edit Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditMateri" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                        <div class="row g-3">
                            <div class="">
                                <label for="judul_materi" class="col-form-label">Judul Materi<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="text"
                                        class="form-control @error('judul_materi') is-invalid @enderror"
                                        id="edit_judul_materi" name="judul_materi" value="{{ old('judul_materi') }}"
                                        required>
                                    @error('judul_materi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="urutan" class="col-form-label">Urutan<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="number" min="1"
                                        class="form-control @error('urutan') is-invalid @enderror"
                                        id="edit_urutan_materi" name="urutan" value="{{ old('urutan') }}" required>
                                    @error('urutan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <label for="deskripsi" class="col-form-label">Deskripsi</label>
                                <div class="">
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="edit_deskripsi_materi" name="deskripsi"
                                        rows="3">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tipe_konten" class="form-label">Tipe Konten <span
                                        class="text-danger">*</span></label>
                                <select id="edit_tipe_konten" name="tipe_konten"
                                    class="form-select @error('tipe_konten') is-invalid @enderror" required>
                                    <option value="" disabled selected>Pilih Tipe Konten</option>
                                    <option value="pdf" @selected(old('tipe_konten') == 'pdf')>PDF</option>
                                    <option value="doc" @selected(old('tipe_konten') == 'doc')>Doc</option>
                                    <option value="video" @selected(old('tipe_konten') == 'video')>Video</option>
                                    <option value="audio" @selected(old('tipe_konten') == 'audio')>Audio</option>
                                    <option value="gambar" @selected(old('tipe_konten') == 'gambar')>Gambar</option>
                                    <option value="link" @selected(old('tipe_konten') == 'link')>Link</option>
                                    <option value="scorm" @selected(old('tipe_konten') == 'scorm')>Scorm</option>
                                </select>
                                @error('tipe_konten')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="">
                                <label for="edit_path_link" class="col-form-label">Link/URL</label>
                                <div class="">
                                    <input type="text" class="form-control @error('path_link') is-invalid @enderror"
                                        id="edit_path_link" name="path_link" value="{{ old('path_link') }}">
                                    @error('path_link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Biarkan kosong jika tidak memilih link di tipe konten</div>
                                </div>
                            </div>
                            <div class="">
                                <label for="file" class="col-form-label">File</label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror"
                                    id="file" name="file" value="{{ old('file') }}">
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload sesuai tipe konten yang dipilih</div>
                            </div>
                            <div class="row g-3 ms-2">
                                <div class="form-check col-3">
                                    <input name="is_published" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_publish_materi" name="is_published" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="publish">
                                        Publish
                                    </label>
                                </div>
                                <div class="form-check col-3">
                                    <input name="is_wajib" class="form-check-input" type="hidden" value="0">
                                    <input id="edit_wajib_materi" name="is_wajib" class="form-check-input"
                                        type="checkbox" value="1">
                                    <label class="form-check-label" for="wajib">
                                        Wajib
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
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
        //  deskripsi text area
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#deskripsi, #edit_deskripsi, #edit_deskripsi_materi',
                height: 200,
                menubar: false,
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save(); // Update textarea value
                    });
                }
            });
        }

        /// MODUL ///
        // Edit Modul
        $(document).on('click', '#btn-edit-modul', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let deskripsi = $(this).data('deskripsi');
            let urutan = $(this).data('urutan');
            let publish = $(this).data('publish');

            // Isi input
            $('#edit_nama_modul').val(nama);
            // $('#edit_deskripsi').val(deskripsi);
            tinymce.get('edit_deskripsi').setContent(deskripsi || '');
            $('#edit_urutan').val(urutan);
            $('#edit_publish').prop('checked', publish == 1);

            // Set action form PATCH
            let url = "{{ route('modul.update', ':id') }}".replace(':id', id);
            $('#formEditModul').attr('action', url);

            // Tampilkan modal
            $('#editModulModal').modal('show');
        });

        // Delete Modul
        $(document).on('click', '#btn-delete-modul', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const judul = $(this).data('judul');

            Swal.fire({
                title: 'Yakin ingin menghapus modul ini?',
                html: `${judul}`,
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
                            Swal.showLoading();
                        },
                        success(res) {
                            // Setelah delete sukses → reload halaman
                            window.location.href = res.redirect;
                        },
                        error(err) {
                            console.log(err)
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
        /// END MODUL ///


        /// MATERI ///
        // Tambah Materi
        $(document).on('click', '#btn-create-materi', function() {
            let modul_id = $(this).data('modul'); //ambil materi id

            // Isi input
            $('#modul_id').val(modul_id);

            $('#createMateriModal').modal('show');
        });

        // Edit Materi
        $(document).on('click', '#btn-edit-materi', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let deskripsi = $(this).data('deskripsi');
            let urutan = $(this).data('urutan');
            let tipe = $(this).data('tipe');
            let link = $(this).data('link');
            let wajib = $(this).data('wajib');
            let publish = $(this).data('publish');

            // Isi input
            $('#edit_judul_materi').val(nama);
            tinymce.get('edit_deskripsi_materi').setContent(deskripsi || '');
            $('#edit_urutan_materi').val(urutan);
            $('#edit_tipe_konten').val(tipe);
            $('#edit_path_link').val(link);
            $('#edit_wajib_materi').prop('checked', wajib == 1);
            $('#edit_publish_materi').prop('checked', publish == 1);

            // Set action form PATCH
            let url = "{{ route('modul.materi.update', ':id') }}".replace(':id', id);
            $('#formEditMateri').attr('action', url);

            // Tampilkan modal
            $('#editMateriModal').modal('show');
        });

        // Delete Materi
        $(document).on('click', '#btn-delete-materi', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const judul = $(this).data('judul');

            Swal.fire({
                title: 'Yakin ingin menghapus materi ini?',
                html: `${judul}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('modul.materi.destroy', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE",
                        },
                        beforeSend() {
                            Swal.showLoading();
                        },
                        success(res) {
                            // Setelah delete sukses → reload halaman
                            window.location.href = res.redirect;
                        },
                        error(err) {
                            console.log(err)
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
        /// END MATERI ///

        // Show modals if errors
        @if (session('error_modal') == 'create_materi')
            new bootstrap.Modal(document.getElementById('createMateriModal')).show();
        @endif
    </script>

    @if (session('error'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        </script>
    @endif
@endpush
