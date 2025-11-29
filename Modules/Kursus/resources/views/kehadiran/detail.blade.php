@extends('kursus::show')

@section('title', 'Detail Sesi Kehadiran')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $sesi->kursus->id) }}">{{ $sesi->kursus->judul }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sesi-kehadiran.index', $sesi->kursus->id) }}">Sesi Kehadiran</a></li>
@endsection
@section('page-title', 'Detail Sesi Kehadiran - Pertemuan Ke-' . $sesi->pertemuan_ke)

@section('detail-content')
    <div class="row">
        <!-- Info Sesi -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Sesi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Pertemuan Ke-</strong></td>
                                    <td>: {{ $sesi->pertemuan_ke }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal</strong></td>
                                    <td>: {{ \Carbon\Carbon::parse($sesi->tanggal)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu</strong></td>
                                    <td>: {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Durasi Check-in</strong></td>
                                    <td>: {{ $sesi->durasi_berlaku_menit }} menit</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>:
                                        @if ($sesi->status == 'scheduled')
                                            <span class="badge bg-primary">Dijadwalkan</span>
                                        @elseif($sesi->status == 'ongoing')
                                            <span class="badge bg-success">Berlangsung</span>
                                        @elseif($sesi->status == 'completed')
                                            <span class="badge bg-secondary">Selesai</span>
                                        @else
                                            <span class="badge bg-danger">Dibatalkan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Peserta</strong></td>
                                    <td>: {{ $sesi->kehadirans->count() }} orang</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Kehadiran -->
        <div class="col-md-12 mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h6 class="card-title">Hadir</h6>
                            <h3 class="mb-0">{{ $sesi->kehadirans->where('status', 'hadir')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h6 class="card-title">Terlambat</h6>
                            <h3 class="mb-0">{{ $sesi->kehadirans->where('status', 'terlambat')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h6 class="card-title">Izin</h6>
                            <h3 class="mb-0">{{ $sesi->kehadirans->where('status', 'izin')->count() }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h6 class="card-title">Tidak Hadir</h6>
                            <h3 class="mb-0">{{ $sesi->kehadirans->where('status', 'tidak_hadir')->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Kehadiran -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people-fill"></i> Daftar Kehadiran Peserta</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Peserta</th>
                                    <th width="15%">NIP</th>
                                    <th width="15%">Check-in</th>
                                    <th width="15%">Check-out</th>
                                    <th width="10%">Durasi</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sesi->kehadirans as $index => $kehadiran)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $kehadiran->peserta->nama_lengkap }}</td>
                                        <td>{{ $kehadiran->peserta->nip }}</td>
                                        <td>
                                            @if ($kehadiran->waktu_checkin)
                                                {{ \Carbon\Carbon::parse($kehadiran->waktu_checkin)->format('H:i:s') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($kehadiran->waktu_checkout)
                                                {{ \Carbon\Carbon::parse($kehadiran->waktu_checkout)->format('H:i:s') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($kehadiran->durasi_menit)
                                                {{ $kehadiran->durasi_menit }} menit
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($kehadiran->status == 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($kehadiran->status == 'terlambat')
                                                <span class="badge bg-warning">Terlambat</span>
                                            @elseif($kehadiran->status == 'izin')
                                                <span class="badge bg-info">Izin</span>
                                            @elseif($kehadiran->status == 'sakit')
                                                <span class="badge bg-secondary">Sakit</span>
                                            @else
                                                <span class="badge bg-danger">Tidak Hadir</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary btn-edit-kehadiran"
                                                data-id="{{ $kehadiran->id }}" data-status="{{ $kehadiran->status }}"
                                                data-keterangan="{{ $kehadiran->keterangan }}" title="Edit Status">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada data kehadiran</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Status Kehadiran Modal -->
    <div class="modal fade" id="editKehadiranModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Status Kehadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditKehadiran" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status_kehadiran" name="status" required>
                                <option value="hadir">Hadir</option>
                                <option value="terlambat">Terlambat</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="tidak_hadir">Tidak Hadir</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" id="edit_keterangan_kehadiran" name="keterangan" rows="3"></textarea>
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


    <script>
        // Edit Status Kehadiran
        $(document).on('click', '.btn-edit-kehadiran', function() {
            let id = $(this).data('id');
            let status = $(this).data('status');
            let keterangan = $(this).data('keterangan');

            $('#edit_status_kehadiran').val(status);
            $('#edit_keterangan_kehadiran').val(keterangan);

            let url = "{{ route('kehadiran.update-status', ':id') }}".replace(':id', id);
            $('#formEditKehadiran').attr('action', url);
            $('#editKehadiranModal').modal('show');
        });
    </script>
@endpush
