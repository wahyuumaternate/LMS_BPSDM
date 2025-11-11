@extends('kursus::show')

@section('title', 'Prasyarat Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Prasyarat Kursus')

@section('detail-content')
    <div class="row">
        <div class="col-md-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKategoriModal">
                <i class="bi bi-plus-circle"></i> Tambah Prasyarat
            </button>
        </div>
        <div class="col-md-7">
            <ol class="list-group list-group-numbered">
                @forelse ($kursus->prasyarats as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto d-fle g-3">
                            {{ $item->deskripsi }}
                            @if ($item->is_wajib)
                                <span class="badge bg-primary ms-3 ">Wajib</span>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button id="btn-edit" class="btn btn-sm btn-success" data-id="{{ $item->id }}"
                                data-deskripsi="{{ $item->deskripsi }}" data-wajib="{{ $item->is_wajib }}">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <button id="btn-delete" class="btn btn-sm btn-danger" data-id="{{ $item->id }}">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    @empty
                        <p class="text-center">Belum ada prasyarat</p>
                @endforelse
            </ol>
        </div>
    </div>



    <!-- Create Modal -->
    <div class="modal fade" id="createKategoriModal" tabindex="-1" aria-labelledby="createKategoriModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createKategoriModalLabel">Tambah Prasyarat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('prasyarat.store') }}" method="POST">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                        <div class="row g-3">
                            <div class="">
                                <label for="deskripsi" class="col-form-label">Deskripsi<span
                                        class="text-danger">*</span></label>
                                <div class="">
                                    <input type="text" class="form-control @error('deskripsi') is-invalid @enderror"
                                        id="deskripsi" name="deskripsi" value="{{ old('deskripsi') }}" required>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="">
                                <div class="form-check">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editKategoriModal" tabindex="-1" aria-labelledby="editKategoriModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editKategoriModalLabel">Edit Prasyarat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditPrasyarat" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <input type="text" id="edit_deskripsi" name="deskripsi" class="form-control" required>
                        </div>

                        <div class="form-check">
                            <input name="is_wajib" class="form-check-input" type="hidden" value="0">
                            <input id="edit_wajib" name="is_wajib" class="form-check-input" type="checkbox"
                                value="1">
                            <label class="form-check-label" for="edit_wajib">Wajib</label>
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
        //  Edit
        $(document).on('click', '#btn-edit', function() {
            let id = $(this).data('id');
            let deskripsi = $(this).data('deskripsi');
            let wajib = $(this).data('wajib');

            // Isi input
            $('#edit_deskripsi').val(deskripsi);
            $('#edit_wajib').prop('checked', wajib == 1);

            // Set action form PATCH
            let url = "{{ route('prasyarat.destroy', ':id') }}".replace(':id', id);
            $('#formEditPrasyarat').attr('action', url);
            console.log(url)
            // Tampilkan modal
            $('#editKategoriModal').modal('show');
        });

        //  Delete
        $(document).on('click', '#btn-delete', function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            console.log(id)

            Swal.fire({
                title: 'Yakin ingin menghapus prasyarat ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('prasyarat.destroy', ':id') }}".replace(':id', id),
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
    </script>

    @if (session('success'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        </script>
    @endif
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
