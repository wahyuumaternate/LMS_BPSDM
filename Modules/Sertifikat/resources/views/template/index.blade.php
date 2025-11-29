@extends('layouts.main')

@section('title', 'Template Sertifikat')
@section('page-title', 'Daftar Template Sertifikat')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Template Sertifikat</h5>
                        <a href="{{ route('template.sertifikat.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Template
                        </a>
                    </div>

                    <div class="mb-3">
                        <form action="{{ route('template.sertifikat.index') }}" method="GET" class="d-flex">
                            <div class="input-group me-2">
                                <input type="text" class="form-control" placeholder="Cari nama template..."
                                    name="search" value="{{ request('search') }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            @if (request('search'))
                                <a href="{{ route('template.sertifikat.index') }}" class="btn btn-outline-secondary">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No.</th>
                                    <th style="width: 15%">Preview</th>
                                    <th style="width: 25%">Nama Template</th>
                                    <th style="width: 15%">Logo BPSDM</th>
                                    <th style="width: 15%">Logo Pemda</th>
                                    <th style="width: 25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $key => $template)
                                    <tr>
                                        <td>{{ $templates->firstItem() + $key }}</td>
                                        <td>
                                            @if ($template->path_background)
                                                <img src="{{ Storage::url($template->path_background) }}" alt="Preview"
                                                    class="img-thumbnail" style="max-height: 80px;">
                                            @else
                                                <span class="badge bg-secondary">Tidak ada background</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->nama_template }}</td>
                                        <td>
                                            @if ($template->logo_bpsdm_path)
                                                <img src="{{ Storage::url($template->logo_bpsdm_path) }}" alt="Logo BPSDM"
                                                    class="img-thumbnail" style="max-height: 50px;">
                                            @else
                                                <span class="badge bg-secondary">Tidak ada logo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($template->logo_pemda_path)
                                                <img src="{{ Storage::url($template->logo_pemda_path) }}" alt="Logo Pemda"
                                                    class="img-thumbnail" style="max-height: 50px;">
                                            @else
                                                <span class="badge bg-secondary">Tidak ada logo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('template.sertifikat.show', $template->id) }}"
                                                    class="btn btn-sm btn-info text-white">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('template.sertifikat.preview', $template->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="bi bi-file-earmark-richtext"></i>
                                                </a>
                                                <a href="{{ route('template.sertifikat.edit', $template->id) }}"
                                                    class="btn btn-sm btn-warning text-white">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-id="{{ $template->id }}"
                                                    data-nama="{{ $template->nama_template }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data template sertifikat</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Form Hapus tersembunyi -->
    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete konfirmasi dengan SweetAlert
            document.querySelectorAll('.btn-delete').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        html: `Template: <b>${nama}</b> akan dihapus.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.getElementById('delete-form');
                            form.action =
                                "{{ route('template.sertifikat.destroy', '') }}/" + id;
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
