@extends('kursus::show')

@section('title', 'Jadwal Kegiatan')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item"><a href="{{ route('course.show', $kursus->id) }}">{{ $kursus->judul }}</a></li>
@endsection
@section('page-title', 'Jadwal Kegiatan')

@section('detail-content')
    <div class="row">
        <div class="col-md-12 mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createJadwalModal">
                <i class="bi bi-plus-circle"></i> Tambah Jadwal
            </button>
        </div>

        <div class="col-md-12">
            @forelse ($kursus->jadwalKegiatan as $jadwal)
                <div class="card mb-3">
                    <div class="card-body p-2">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                @if ($jadwal->status == 'upcoming')
                                    <i class="bi bi-clock-history" style="font-size: 2.5rem; color: #0d6efd;"></i>
                                @elseif($jadwal->status == 'ongoing')
                                    <i class="bi bi-play-circle-fill" style="font-size: 2.5rem; color: #198754;"></i>
                                @else
                                    <i class="bi bi-check-circle-fill" style="font-size: 2.5rem; color: #6c757d;"></i>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    {{ $jadwal->nama_kegiatan }}


                                    @if ($jadwal->tipe == 'online')
                                        <span class="badge bg-info">Online</span>
                                    @elseif($jadwal->tipe == 'offline')
                                        <span class="badge bg-warning">Offline</span>
                                    @else
                                        <span class="badge bg-dark">Hybrid</span>
                                    @endif
                                </h5>
                                <p class="text-muted mb-2">
                                    <small>
                                        <i class="bi bi-calendar-event"></i>
                                        {{ $jadwal->waktu_mulai_kegiatan->format('d M Y, H:i') }} -
                                        {{ $jadwal->waktu_selesai_kegiatan->format('d M Y, H:i') }}
                                        {{-- ({{ floor($jadwal->durasi / 60) }}j {{ $jadwal->durasi % 60 }}m) --}}
                                    </small>
                                </p>
                                @if ($jadwal->lokasi)
                                    <p class="mb-2">
                                        <i class="bi bi-geo-alt"></i>
                                        <strong>Lokasi:</strong> {{ $jadwal->lokasi }}
                                    </p>
                                @endif
                                @if ($jadwal->keterangan)
                                    <p class="mb-2">{{ $jadwal->keterangan }}</p>
                                @endif
                                @if ($jadwal->link_meeting)
                                    <a href="{{ $jadwal->link_meeting }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-camera-video"></i> Buka Meeting
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button class="btn btn-sm btn-success btn-edit" data-id="{{ $jadwal->id }}"
                                        data-nama="{{ $jadwal->nama_kegiatan }}"
                                        data-mulai="{{ $jadwal->waktu_mulai_kegiatan->format('Y-m-d\TH:i') }}"
                                        data-selesai="{{ $jadwal->waktu_selesai_kegiatan->format('Y-m-d\TH:i') }}"
                                        data-tipe="{{ $jadwal->tipe }}" data-lokasi="{{ $jadwal->lokasi }}"
                                        data-link="{{ $jadwal->link_meeting }}"
                                        data-keterangan="{{ $jadwal->keterangan }}" title="Edit">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $jadwal->id }}"
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
                    <i class="bi bi-info-circle"></i> Belum ada jadwal kegiatan. Silakan tambahkan jadwal untuk
                    membantu peserta mengatur waktu mereka.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createJadwalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jadwal Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('jadwal.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                    id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required>
                                @error('nama_kegiatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="waktu_mulai_kegiatan" class="form-label">Waktu Mulai <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local"
                                    class="form-control @error('waktu_mulai_kegiatan') is-invalid @enderror"
                                    id="waktu_mulai_kegiatan" name="waktu_mulai_kegiatan"
                                    value="{{ old('waktu_mulai_kegiatan') }}" required>
                                @error('waktu_mulai_kegiatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="waktu_selesai_kegiatan" class="form-label">Waktu Selesai <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local"
                                    class="form-control @error('waktu_selesai_kegiatan') is-invalid @enderror"
                                    id="waktu_selesai_kegiatan" name="waktu_selesai_kegiatan"
                                    value="{{ old('waktu_selesai_kegiatan') }}" required>
                                @error('waktu_selesai_kegiatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tipe" class="form-label">Tipe Kegiatan <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('tipe') is-invalid @enderror" id="tipe"
                                    name="tipe" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="online" {{ old('tipe') == 'online' ? 'selected' : '' }}>Online
                                    </option>
                                    <option value="offline" {{ old('tipe') == 'offline' ? 'selected' : '' }}>Offline
                                    </option>
                                    <option value="hybrid" {{ old('tipe') == 'hybrid' ? 'selected' : '' }}>Hybrid
                                    </option>
                                </select>
                                @error('tipe')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="lokasi" class="form-label">Lokasi</label>
                                <input type="text" class="form-control @error('lokasi') is-invalid @enderror"
                                    id="lokasi" name="lokasi" value="{{ old('lokasi') }}"
                                    placeholder="Ruangan/Alamat">
                                @error('lokasi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="link_meeting" class="form-label">Link Meeting</label>
                                <input type="url" class="form-control @error('link_meeting') is-invalid @enderror"
                                    id="link_meeting" name="link_meeting" value="{{ old('link_meeting') }}"
                                    placeholder="https://...">
                                @error('link_meeting')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan"
                                    rows="3">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
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
    <div class="modal fade" id="editJadwalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Jadwal Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditJadwal" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="kursus_id" value="{{ $kursus->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" id="edit_nama_kegiatan" name="nama_kegiatan" class="form-control"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="edit_waktu_mulai"
                                    name="waktu_mulai_kegiatan" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="edit_waktu_selesai"
                                    name="waktu_selesai_kegiatan" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipe Kegiatan <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_tipe" name="tipe" required>
                                    <option value="">Pilih Tipe</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="edit_lokasi" name="lokasi"
                                    placeholder="Ruangan/Alamat">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Link Meeting</label>
                                <input type="url" class="form-control" id="edit_link_meeting" name="link_meeting"
                                    placeholder="https://...">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Keterangan</label>
                                <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"></textarea>
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

    <script>
        // Edit
        $(document).on('click', '.btn-edit', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let mulai = $(this).data('mulai');
            let selesai = $(this).data('selesai');
            let tipe = $(this).data('tipe');
            let lokasi = $(this).data('lokasi');
            let link = $(this).data('link');
            let keterangan = $(this).data('keterangan');

            $('#edit_nama_kegiatan').val(nama);
            $('#edit_waktu_mulai').val(mulai);
            $('#edit_waktu_selesai').val(selesai);
            $('#edit_tipe').val(tipe);
            $('#edit_lokasi').val(lokasi);
            $('#edit_link_meeting').val(link);
            $('#edit_keterangan').val(keterangan);

            let url = "{{ route('jadwal.update', ':id') }}".replace(':id', id);
            $('#formEditJadwal').attr('action', url);
            $('#editJadwalModal').modal('show');
        });

        // Delete
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus jadwal ini?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form delete
                    let form = $('<form>', {
                        'method': 'POST',
                        'action': "{{ route('jadwal.destroy', ':id') }}".replace(':id', id)
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
@endpush
