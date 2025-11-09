@extends('layouts.main')

@section('title', 'Detail Template Sertifikat')
@section('page-title', 'Detail Template Sertifikat')

@section('content')
    <div class="row">
        <div class="col-md-8 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">{{ $template->nama_template }}</h5>
                        <div>
                            <a href="{{ route('template.sertifikat.preview', $template->id) }}" class="btn btn-primary">
                                <i class="bi bi-file-earmark-richtext"></i> Preview
                            </a>
                            <a href="{{ route('template.sertifikat.edit', $template->id) }}"
                                class="btn btn-warning text-white">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                    </div>

                    @if ($template->path_background)
                        <div class="mb-3">
                            <h6 class="fw-bold">Background</h6>
                            <img src="{{ Storage::url($template->path_background) }}" alt="Background"
                                class="img-fluid border rounded">
                        </div>
                    @endif

                    @if ($template->design_template)
                        <div class="mb-3">
                            <h6 class="fw-bold">Design Template HTML</h6>
                            <div class="bg-light p-3 border rounded">
                                <pre class="mb-0"><code>{{ $template->design_template }}</code></pre>
                            </div>
                        </div>
                    @endif

                    @if ($template->footer_text)
                        <div class="mb-3">
                            <h6 class="fw-bold">Footer Text</h6>
                            <p>{{ $template->footer_text }}</p>
                        </div>
                    @endif

                    @if ($template->signature_config)
                        <div class="mb-3">
                            <h6 class="fw-bold">Signature Configuration</h6>
                            <div class="bg-light p-3 border rounded">
                                <pre class="mb-0"><code>{{ $template->signature_config }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Logo</h5>

                    <div class="mb-3">
                        <h6 class="fw-bold">Logo BPSDM</h6>
                        @if ($template->logo_bpsdm_path)
                            <img src="{{ Storage::url($template->logo_bpsdm_path) }}" alt="Logo BPSDM"
                                class="img-thumbnail">
                        @else
                            <div class="alert alert-secondary">Tidak ada logo BPSDM</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Logo Pemda</h6>
                        @if ($template->logo_pemda_path)
                            <img src="{{ Storage::url($template->logo_pemda_path) }}" alt="Logo Pemda"
                                class="img-thumbnail">
                        @else
                            <div class="alert alert-secondary">Tidak ada logo Pemda</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Informasi Template</h5>

                    <table class="table">
                        <tr>
                            <th style="width:40%">Dibuat</th>
                            <td>{{ $template->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Terakhir Diperbarui</th>
                            <td>{{ $template->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Total Sertifikat</th>
                            <td>{{ $template->sertifikats()->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('template.sertifikat.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                <button type="button" class="btn btn-danger btn-delete" data-id="{{ $template->id }}"
                    data-nama="{{ $template->nama_template }}">
                    <i class="bi bi-trash"></i> Hapus Template
                </button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
