@extends('kursus::show')

@section('title', 'Sesi Kehadiran')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Sesi Kehadiran')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSesiModal">
                <i class="bi bi-plus-circle"></i> Tambah Sesi Kehadiran
            </button>
        </div>

        <div class="col-md-12">
            @forelse ($kursus->sesiKehadiran as $sesi)
                <div class="card mb-3">
                    <div class="card-body p-2">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                @if ($sesi->status == 'scheduled')
                                    <i class="bi bi-clock-history" style="font-size: 2.5rem; color: #0d6efd;"></i>
                                @elseif($sesi->status == 'ongoing')
                                    <i class="bi bi-play-circle-fill" style="font-size: 2.5rem; color: #198754;"></i>
                                @elseif($sesi->status == 'completed')
                                    <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: #6c757d;"></i>
                                @else
                                    <i class="bi bi-x-circle-fill" style="font-size: 2.5rem; color: #dc3545;"></i>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    Pertemuan Ke-{{ $sesi->pertemuan_ke }}

                                    @if ($sesi->status == 'scheduled')
                                        <span class="badge bg-primary">Dijadwalkan</span>
                                    @elseif($sesi->status == 'ongoing')
                                        <span class="badge bg-success">Berlangsung</span>
                                    @elseif($sesi->status == 'completed')
                                        <span class="badge bg-secondary">Selesai</span>
                                    @else
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @endif
                                </h5>
                                <p class="text-muted mb-2">
                                    <small>
                                        <i class="bi bi-calendar-event"></i>
                                        {{ \Carbon\Carbon::parse($sesi->tanggal)->format('d M Y') }},
                                        {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                    </small>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-hourglass-split"></i>
                                    <strong>Durasi Check-in:</strong> {{ $sesi->durasi_berlaku_menit }} menit
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-people-fill"></i>
                                    <strong>Kehadiran:</strong>
                                    {{ $sesi->totalHadir() }} hadir / {{ $sesi->kehadirans->count() }} total peserta
                                    ({{ $sesi->persentaseKehadiran() }}%)
                                </p>
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <a href="{{ route('sesi-kehadiran.detail', $sesi->id) }}" class="btn btn-sm btn-info"
                                        title="Lihat Detail">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <button class="btn btn-sm btn-success btn-edit" data-id="{{ $sesi->id }}"
                                        data-pertemuan="{{ $sesi->pertemuan_ke }}"
                                        data-tanggal="{{ \Carbon\Carbon::parse($sesi->tanggal)->format('Y-m-d') }}"
                                        data-mulai="{{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }}"
                                        data-selesai="{{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}"
                                        data-durasi="{{ $sesi->durasi_berlaku_menit }}" data-status="{{ $sesi->status }}"
                                        title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $sesi->id }}"
                                        title="Hapus">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada sesi kehadiran. Silakan tambahkan sesi untuk memulai
                    pencatatan kehadiran peserta.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createSesiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sesi Kehadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('sesi-kehadiran.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="pertemuan_ke" class="form-label">Pertemuan Ke- <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('pertemuan_ke') is-invalid @enderror"
                                    id="pertemuan_ke" name="pertemuan_ke" value="{{ old('pertemuan_ke', 1) }}"
                                    min="1" required>
                                @error('pertemuan_ke')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                    id="tanggal" name="tanggal" value="{{ old('tanggal') }}" required>
                                @error('tanggal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="waktu_mulai" class="form-label">Waktu Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('waktu_mulai') is-invalid @enderror"
                                    id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                                @error('waktu_mulai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="waktu_selesai" class="form-label">Waktu Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('waktu_selesai') is-invalid @enderror"
                                    id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                                @error('waktu_selesai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>


                            <div class="col-12">
                                <label for="durasi_berlaku_menit" class="form-label">Durasi Check-in (menit) <span
                                        class="text-danger">*</span></label>
                                <input type="number"
                                    class="form-control @error('durasi_berlaku_menit') is-invalid @enderror"
                                    id="durasi_berlaku_menit" name="durasi_berlaku_menit"
                                    value="{{ old('durasi_berlaku_menit', 60) }}" min="1" required>
                                <small class="text-muted">Durasi waktu peserta dapat melakukan check-in</small>
                                @error('durasi_berlaku_menit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="status" class="form-label">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status"
                                    name="status" required>
                                    <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>
                                        Dijadwalkan</option>
                                    <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung
                                    </option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Selesai
                                    </option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>
                                        Dibatalkan</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
    <div class="modal fade" id="editSesiModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sesi Kehadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditSesi" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Pertemuan Ke- <span class="text-danger">*</span></label>
                                <input type="number" id="edit_pertemuan_ke" name="pertemuan_ke" class="form-control"
                                    min="1" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit_waktu_mulai" name="waktu_mulai"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit_waktu_selesai" name="waktu_selesai"
                                    required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Durasi Check-in (menit) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_durasi_berlaku_menit"
                                    name="durasi_berlaku_menit" min="1" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="scheduled">Dijadwalkan</option>
                                    <option value="ongoing">Berlangsung</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Dibatalkan</option>
                                </select>
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
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Function untuk convert 24 jam ke 12 jam dengan AM/PM
        function formatTo12Hour(time24) {
            if (!time24) return '';

            let [hours, minutes] = time24.split(':');
            hours = parseInt(hours);

            let period = hours >= 12 ? 'PM' : 'AM';
            let hours12 = hours % 12 || 12;

            // Keterangan tambahan
            let description = '';
            if (hours >= 0 && hours < 12) {
                description = ' (Pagi/Siang)';
            } else {
                description = ' (Siang/Malam)';
            }

            return `${hours12}:${minutes} ${period}${description}`;
        }

        // Preview waktu untuk Create Modal
        $('#waktu_mulai, #waktu_selesai').on('change', function() {
            let inputId = $(this).attr('id');
            let time24 = $(this).val();
            let time12 = formatTo12Hour(time24);

            // Hapus preview lama jika ada
            $(this).next('.time-preview').remove();

            // Tambah preview baru
            if (time24) {
                $(this).after(
                    `<div class="time-preview text-success mt-1"><small><strong>Preview:</strong> ${time12}</small></div>`
                );
            }
        });

        // Preview waktu untuk Edit Modal
        $('#edit_waktu_mulai, #edit_waktu_selesai').on('change', function() {
            let inputId = $(this).attr('id');
            let time24 = $(this).val();
            let time12 = formatTo12Hour(time24);

            // Hapus preview lama jika ada
            $(this).next('.time-preview').remove();

            // Tambah preview baru
            if (time24) {
                $(this).after(
                    `<div class="time-preview text-success mt-1"><small><strong>Preview:</strong> ${time12}</small></div>`
                );
            }
        });

        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let pertemuan = $(this).data('pertemuan');
            let tanggal = $(this).data('tanggal');
            let mulai = $(this).data('mulai');
            let selesai = $(this).data('selesai');
            let durasi = $(this).data('durasi');
            let status = $(this).data('status');

            // Set nilai form
            $('#edit_pertemuan_ke').val(pertemuan);
            $('#edit_tanggal').val(tanggal);
            $('#edit_durasi_berlaku_menit').val(durasi);
            $('#edit_status').val(status);

            // Split waktu mulai dan set select
            if (mulai) {
                let [jam_m, menit_m] = mulai.split(':');
                $('#edit_jam_mulai').val(jam_m);
                $('#edit_menit_mulai').val(menit_m);
                $('#edit_waktu_mulai').val(mulai);
            }

            // Split waktu selesai dan set select
            if (selesai) {
                let [jam_s, menit_s] = selesai.split(':');
                $('#edit_jam_selesai').val(jam_s);
                $('#edit_menit_selesai').val(menit_s);
                $('#edit_waktu_selesai').val(selesai);
            }

            let url = "{{ route('sesi-kehadiran.update', ':id') }}".replace(':id', id);
            $('#formEditSesi').attr('action', url);
            $('#editSesiModal').modal('show');
        });

        // Delete
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus sesi ini?',
                text: 'Data kehadiran terkait juga akan terhapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = $('<form>', {
                        'method': 'POST',
                        'action': "{{ route('sesi-kehadiran.destroy', ':id') }}".replace(':id', id)
                    });

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': "{{ csrf_token() }}"
                    }));

                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'DELETE'
                    }));

                    $('body').append(form);
                    form.submit();
                }
            });
        });
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#waktu_mulai", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });

        flatpickr("#waktu_selesai", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    </script>
@endpush
