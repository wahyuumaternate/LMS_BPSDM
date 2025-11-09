@extends('layouts.main')

@section('title', 'Preview Template Sertifikat')
@section('page-title', 'Preview Template Sertifikat: ' . $template->nama_template)

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Preview {{ $template->nama_template }}</h5>
                        <div>
                            <a href="{{ route('template.sertifikat.edit', $template->id) }}"
                                class="btn btn-warning text-white">
                                <i class="bi bi-pencil"></i> Edit Template
                            </a>
                            <a href="{{ route('template.sertifikat.show', $template->id) }}" class="btn btn-info text-white">
                                <i class="bi bi-arrow-left"></i> Kembali ke Detail
                            </a>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Ini adalah preview dengan data contoh. Sertifikat asli akan berisi data sesuai dengan peserta dan
                        kursus terkait.
                    </div>

                    {{-- <!-- PDF Preview -->
                    <div class="row mb-4">
                        <div class="col-md-12 text-center">
                            <div class="embed-responsive" style="height: 500px;">
                                <iframe class="embed-responsive-item border rounded shadow"
                                    src="{{ route('template.sertifikat.preview.pdf', $template->id) }}"
                                    style="width: 100%; height: 100%;" allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Download/Print Section -->
                    <div class="text-center mb-4">
                        <a href="{{ route('template.sertifikat.preview.pdf', $template->id) }}" class="btn btn-primary"
                            target="_blank">
                            <i class="bi bi-eye"></i> Buka PDF di Tab Baru
                        </a>
                        <a href="{{ route('template.sertifikat.download.pdf', $template->id) }}" class="btn btn-success">
                            <i class="bi bi-download"></i> Download PDF
                        </a>
                    </div>

                    <!-- Placeholder Data Section -->
                    <div class="mt-4">
                        <h6>Data Placeholder yang Digunakan:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 25%">Placeholder</th>
                                        <th style="width: 35%">Nilai Contoh</th>
                                        <th style="width: 40%">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>@{{ nama_institusi }}</code></td>
                                        <td>BADAN PENGEMBANGAN SUMBER DAYA MANUSIA</td>
                                        <td>Nama institusi penerbit sertifikat</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nama_acara }}</code></td>
                                        <td>PELATIHAN PROFESIONAL 2023</td>
                                        <td>Nama acara, pelatihan, atau lomba</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nomor_sertifikat }}</code></td>
                                        <td>NO/SERT/2023/001</td>
                                        <td>Nomor sertifikat</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nama_peserta }}</code></td>
                                        <td>Dr. BUDI SANTOSO, S.Pd., M.Kom.</td>
                                        <td>Nama lengkap penerima sertifikat</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ detail_peserta }}</code></td>
                                        <td>NIP. 198012252005011002</td>
                                        <td>Detail tambahan peserta (opsional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ peringkat_penghargaan }}</code></td>
                                        <td>PESERTA TERBAIK</td>
                                        <td>Pencapaian atau peringkat yang diberikan</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ detail_penghargaan }}</code></td>
                                        <td>KATEGORI PENGEMBANGAN APLIKASI</td>
                                        <td>Detail tambahan pencapaian (opsional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nama_jabatan1 }}</code></td>
                                        <td>Kepala BPSDM</td>
                                        <td>Jabatan penandatangan pertama</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nama_penandatangan1 }}</code></td>
                                        <td>Dr. Ahmad Wijaya, M.Si.</td>
                                        <td>Nama penandatangan pertama</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nip_penandatangan1 }}</code></td>
                                        <td>NIP. 196705061991031001</td>
                                        <td>NIP penandatangan pertama</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ tempat_tanggal_terbit }}</code></td>
                                        <td>Jakarta, 10 Desember 2023</td>
                                        <td>Tempat dan tanggal penerbitan</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nama_jabatan2 }}</code></td>
                                        <td>Ketua Panitia</td>
                                        <td>Jabatan penandatangan kedua</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nama_penandatangan2 }}</code></td>
                                        <td>Ir. Siti Rahma, M.M.</td>
                                        <td>Nama penandatangan kedua</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ nip_penandatangan2 }}</code></td>
                                        <td>NIP. 197503112006041009</td>
                                        <td>NIP penandatangan kedua</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ url_tanda_tangan1 }}</code></td>
                                        <td>[URL gambar tanda tangan]</td>
                                        <td>URL gambar tanda tangan pertama</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ url_tanda_tangan2 }}</code></td>
                                        <td>[URL gambar tanda tangan]</td>
                                        <td>URL gambar tanda tangan kedua</td>
                                    </tr>
                                    <tr>
                                        <td><code>@{{ url_logo }}</code></td>
                                        <td>[URL gambar logo]</td>
                                        <td>URL gambar logo</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        code {
            background-color: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            color: #e83e8c;
            font-family: 'Courier New', Courier, monospace;
        }

        .embed-responsive {
            position: relative;
            display: block;
            width: 100%;
            overflow: hidden;
        }

        .embed-responsive-item {
            position: relative;
            width: 100%;
            border: none;
        }
    </style>
@endpush
