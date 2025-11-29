@extends('layouts.main')

@section('title', 'Kursus')
@section('page-title', 'Daftar Kursus')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex my-3 justify-content-between">
                <div class="d-flex gap-2">
                    <div class="">
                        <select id="status" class="form-select">
                            <option selected="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="draft">Draft</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <input type="text" id="search" class="form-control" placeholder="Cari Kursus">
                </div>
            </div>
            <div id="loading">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div id="table-wrapper"></div>
        </div>
    </div>
@endsection

@push('scripts')
  

    <script>
        function loadTable(page = 1) {
            $.ajax({
                url: "{{ route('course.table') }}", // pastikan ini sesuai nama route
                data: {
                    search: $('#search').val(),
                    status: $('#status').val(),
                    page: page
                },
                beforeSend: function() {
                    $('#loading').show();
                    $('#table-wrapper').hide();
                },
                success: function(res) {
                    $('#table-wrapper').html(res);
                },
                complete: function() {
                    $('#loading').hide();
                    $('#table-wrapper').show();
                }
            });
        }

        // Load awal
        loadTable();

        // Event search
        $('#search').on('input', function() {
            loadTable();
        });

        // select filters
        $('#status').on('change', () => loadTable());

        // Pagination AJAX
        $(document).on('click', '#table-wrapper .pagination a', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            loadTable(url.searchParams.get('page'));
        });
    </script>



    <script>
        $(document).on('click', '#btn-delete', function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const judul = $(this).data('judul');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                html: `Kursus: <b>${judul}</b> akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('course.destroy', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: "DELETE",
                        },
                        beforeSend() {
                            Swal.showLoading();
                        },
                        success(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data berhasil dihapus.'
                            });

                            // reload table tanpa reload halaman
                            loadTable();
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
@endpush
