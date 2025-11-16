@extends('kursus::show')

@section('title', 'Peserta Kursus')
@section('page-title', 'Daftar Peserta Kursus')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
    <li class="breadcrumb-item active">Peserta</li>
@endsection

@section('detail-content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Peserta Kursus: {{ $kursus->judul }}</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($kursus->peserta as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>{{ $p->nama_lengkap }}</td>

                                <td>{{ $p->email }}</td>

                                <td>
                                    @php $status = $p->pivot->status; @endphp
                                    <span
                                        class="badge 
                                    @if ($status == 'aktif') bg-success
                                    @elseif($status == 'pending') bg-warning
                                    @elseif($status == 'disetujui') bg-primary
                                    @elseif($status == 'ditolak') bg-danger
                                    @elseif($status == 'selesai') bg-info 
                                    @else bg-secondary @endif">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td>{{ $p->pivot->nilai_akhir ?? '-' }}</td>

                                <td>
                                    <button class="btn btn-sm btn-info btn-detail" data-bs-toggle="modal"
                                        data-bs-target="#modalDetail" data-id="{{ $p->id }}"
                                        data-nama="{{ $p->nama_lengkap }}" data-email="{{ $p->email }}"
                                        data-opd="{{ $p->opd->nama_opd ?? '-' }}" data-status="{{ $p->pivot->status }}"
                                        data-nilai="{{ $p->pivot->nilai_akhir ?? '-' }}"
                                        data-predikat="{{ $p->pivot->predikat ?? '-' }}"
                                        data-daftar="{{ $p->pivot->tanggal_daftar }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <button class="btn btn-sm btn-warning btn-status" data-bs-toggle="modal"
                                        data-bs-target="#modalStatus" data-id="{{ $p->id }}"
                                        data-status="{{ $p->pivot->status }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada peserta.</td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>


    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p><strong>Nama:</strong> <span id="d-nama"></span></p>
                    <p><strong>Email:</strong> <span id="d-email"></span></p>
                    <p><strong>OPD:</strong> <span id="d-opd"></span></p>
                    <p><strong>Status:</strong> <span id="d-status"></span></p>
                    <p><strong>Tanggal Daftar:</strong> <span id="d-daftar"></span></p>
                    <p><strong>Nilai Akhir:</strong> <span id="d-nilai"></span></p>
                    <p><strong>Predikat:</strong> <span id="d-predikat"></span></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Ubah Status -->
    <div class="modal fade" id="modalStatus" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="formStatus" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Ubah Status Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <label class="form-label">Status Peserta</label>
                    <select class="form-select" name="status" id="status_peserta">
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                        <option value="batal">Batal</option>
                    </select>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // Modal Detail
        $(document).on('click', '.btn-detail', function() {
            $('#d-nama').text($(this).data('nama'));
            $('#d-email').text($(this).data('email'));
            $('#d-opd').text($(this).data('opd'));
            $('#d-status').text($(this).data('status'));
            $('#d-daftar').text($(this).data('daftar'));
            $('#d-nilai').text($(this).data('nilai'));
            $('#d-predikat').text($(this).data('predikat'));
        });

        // Modal Ubah Status
        $(document).on('click', '.btn-status', function() {
            const pesertaId = $(this).data('id');
            const status = $(this).data('status');

            $('#status_peserta').val(status);

            const url =
                "{{ route('kursus.peserta.status.update', ['kursus' => $kursus->id, 'peserta' => ':id']) }}"
                .replace(':id', pesertaId);

            $('#formStatus').attr('action', url);
        });
    </script>
@endpush
