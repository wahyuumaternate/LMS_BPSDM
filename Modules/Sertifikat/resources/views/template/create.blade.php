@extends('layouts.main')

@section('title', 'Tambah Template Sertifikat')
@section('page-title', 'Tambah Template Sertifikat Baru')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Form Tambah Template Sertifikat</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('template.sertifikat.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="nama_template" class="form-label">Nama Template <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama_template') is-invalid @enderror"
                                        id="nama_template" name="nama_template" value="{{ old('nama_template') }}" required>
                                    @error('nama_template')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="background" class="form-label">Background Sertifikat</label>
                                    <input type="file" class="form-control @error('background') is-invalid @enderror"
                                        id="background" name="background" accept="image/*">
                                    @error('background')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Format gambar: JPG, PNG, GIF. Maksimal 2MB.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="design_template" class="form-label">Design Template HTML</label>
                                    <textarea class="form-control @error('design_template') is-invalid @enderror" id="design_template"
                                        name="design_template" rows="10">{{ old('design_template') }}</textarea>
                                    @error('design_template')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    {{-- <div class="form-text">
                                        Gunakan placeholder: {{ nama_peserta }}, {{ nomor_sertifikat }},
                                        {{ tanggal_terbit }},
                                        {{ nama_kursus }}, {{ nama_penandatangan }}, {{ jabatan_penandatangan }}
                                    </div> --}}
                                </div>

                                <div class="mb-3">
                                    <label for="signature_config" class="form-label">Konfigurasi Tanda Tangan (JSON)</label>
                                    <textarea class="form-control @error('signature_config') is-invalid @enderror" id="signature_config"
                                        name="signature_config" rows="5">{{ old('signature_config') }}</textarea>
                                    @error('signature_config')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="footer_text" class="form-label">Teks Footer</label>
                                    <input type="text" class="form-control @error('footer_text') is-invalid @enderror"
                                        id="footer_text" name="footer_text" value="{{ old('footer_text') }}">
                                    @error('footer_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Logo</h6>

                                        <div class="mb-3">
                                            <label for="logo_bpsdm" class="form-label">Logo BPSDM</label>
                                            <input type="file"
                                                class="form-control @error('logo_bpsdm') is-invalid @enderror"
                                                id="logo_bpsdm" name="logo_bpsdm" accept="image/*">
                                            @error('logo_bpsdm')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG. Maksimal 2MB.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="logo_pemda" class="form-label">Logo Pemda</label>
                                            <input type="file"
                                                class="form-control @error('logo_pemda') is-invalid @enderror"
                                                id="logo_pemda" name="logo_pemda" accept="image/*">
                                            @error('logo_pemda')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Format: JPG, PNG. Maksimal 2MB.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Simpan Template</button>
                            <a href="{{ route('template.sertifikat.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Preview gambar (opsional)
            const backgroundInput = document.getElementById('background');
            if (backgroundInput) {
                backgroundInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Bisa tambahkan preview image jika diinginkan
                            console.log('Background file selected');
                        }
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            }

            // Jika ingin menggunakan code editor untuk design_template
            if (typeof CodeMirror !== 'undefined') {
                CodeMirror.fromTextArea(document.getElementById('design_template'), {
                    lineNumbers: true,
                    mode: "htmlmixed",
                    theme: "default"
                });

                CodeMirror.fromTextArea(document.getElementById('signature_config'), {
                    lineNumbers: true,
                    mode: "javascript",
                    theme: "default"
                });
            }
        });
    </script>
@endpush
