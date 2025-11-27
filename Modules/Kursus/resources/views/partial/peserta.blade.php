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
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Peserta Kursus: {{ $kursus->judul }}</h5>
                <small class="text-muted">Total {{ $kursus->peserta->count() }} peserta</small>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" onclick="exportData()">
                    <i class="bi bi-file-excel"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="card-body">
            <!-- Bulk Action Bar (Hidden by default) -->
            <div id="bulkActionBar" class="alert alert-info align-items-center mb-3" style="display: none;">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong><span id="selectedCount">0</span> peserta dipilih</strong>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-sm btn-primary" onclick="bulkUpdateStatus()">
                            <i class="bi bi-pencil-square me-1"></i>Ubah Status
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
                            <i class="bi bi-x-circle me-1"></i>Batal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter dan Search -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Cari nama atau email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterOPD">
                        <option value="">Semua OPD</option>
                        @foreach ($kursus->peserta->pluck('opd.nama_opd')->unique()->filter() as $opd)
                            <option value="{{ $opd }}">{{ $opd }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" onclick="resetFilter()">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body p-2 text-center">
                            <small>Pending</small>
                            <h5 class="mb-0">{{ $kursus->peserta->where('pivot.status', 'pending')->count() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body p-2 text-center">
                            <small>Disetujui</small>
                            <h5 class="mb-0">{{ $kursus->peserta->where('pivot.status', 'disetujui')->count() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body p-2 text-center">
                            <small>Ditolak</small>
                            <h5 class="mb-0">{{ $kursus->peserta->where('pivot.status', 'ditolak')->count() }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body p-2 text-center">
                            <small>Selesai</small>
                            <h5 class="mb-0">{{ $kursus->peserta->where('pivot.status', 'selesai')->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablePeserta">
                    <thead class="table-light">
                        <tr>
                            <th width="3%">
                                <input type="checkbox" class="form-check-input" id="selectAll" title="Pilih Semua"
                                    onchange="toggleSelectAll(this)">
                            </th>
                            <th width="5%">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>OPD</th>
                            <th>Status</th>
                            <th>Tanggal Daftar</th>
                            <th>Nilai</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($kursus->peserta as $index => $p)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input peserta-checkbox"
                                        value="{{ $p->id }}" onchange="updateBulkActions()">
                                </td>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($p->foto_profil)
                                            <img src="{{ asset('storage/' . $p->foto_profil) }}"
                                                class="rounded-circle me-2" width="32" height="32"
                                                alt="{{ $p->nama_lengkap }}">
                                        @else
                                            <div class="bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; font-size: 14px;">
                                                {{ strtoupper(substr($p->nama_lengkap, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $p->nama_lengkap }}</strong>
                                            @if ($p->nip)
                                                <br><small class="text-muted">NIP: {{ $p->nip }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $p->email }}</td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $p->opd->kode_opd ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    @php $status = $p->pivot->status; @endphp
                                    <span
                                        class="badge 
                                        @if ($status == 'pending') bg-warning text-dark
                                        @elseif($status == 'disetujui') bg-success
                                        @elseif($status == 'ditolak') bg-danger
                                        @elseif($status == 'selesai') bg-info
                                        @else bg-secondary @endif">
                                        <i
                                            class="bi 
                                            @if ($status == 'pending') bi-clock
                                            @elseif($status == 'disetujui') bi-check-circle
                                            @elseif($status == 'ditolak') bi-x-circle
                                            @elseif($status == 'selesai') bi-trophy @endif"></i>
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>

                                <td>{{ \Carbon\Carbon::parse($p->pivot->tanggal_daftar)->format('d M Y') }}</td>

                                <td>
                                    @if ($p->pivot->nilai_akhir)
                                        <span
                                            class="badge 
                                            @if ($p->pivot->nilai_akhir >= 80) bg-success
                                            @elseif($p->pivot->nilai_akhir >= 70) bg-info
                                            @elseif($p->pivot->nilai_akhir >= 60) bg-warning text-dark
                                            @else bg-danger @endif">
                                            {{ number_format($p->pivot->nilai_akhir, 2) }}
                                        </span>
                                        @if ($p->pivot->predikat)
                                            <br><small
                                                class="text-muted">{{ ucwords(str_replace('_', ' ', $p->pivot->predikat)) }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info btn-detail" data-bs-toggle="modal"
                                            data-bs-target="#modalDetail" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}" data-email="{{ $p->email }}"
                                            data-nip="{{ $p->nip ?? '-' }}" data-jabatan="{{ $p->jabatan ?? '-' }}"
                                            data-opd="{{ $p->opd->nama_opd ?? '-' }}"
                                            data-status="{{ $p->pivot->status }}"
                                            data-nilai="{{ $p->pivot->nilai_akhir ?? '-' }}"
                                            data-predikat="{{ $p->pivot->predikat ? ucwords(str_replace('_', ' ', $p->pivot->predikat)) : '-' }}"
                                            data-daftar="{{ \Carbon\Carbon::parse($p->pivot->tanggal_daftar)->format('d M Y H:i') }}"
                                            data-disetujui="{{ $p->pivot->tanggal_disetujui ? \Carbon\Carbon::parse($p->pivot->tanggal_disetujui)->format('d M Y H:i') : '-' }}"
                                            data-selesai="{{ $p->pivot->tanggal_selesai ? \Carbon\Carbon::parse($p->pivot->tanggal_selesai)->format('d M Y H:i') : '-' }}"
                                            data-alasan="{{ $p->pivot->alasan_ditolak ?? '-' }}" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <button class="btn btn-sm btn-warning btn-status" data-bs-toggle="modal"
                                            data-bs-target="#modalStatus" data-id="{{ $p->id }}"
                                            data-nama="{{ $p->nama_lengkap }}" data-status="{{ $p->pivot->status }}"
                                            title="Ubah Status">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        @if ($p->pivot->status == 'selesai')
                                            <button class="btn btn-sm btn-primary btn-nilai" data-bs-toggle="modal"
                                                data-bs-target="#modalNilai" data-id="{{ $p->id }}"
                                                data-nama="{{ $p->nama_lengkap }}"
                                                data-nilai="{{ $p->pivot->nilai_akhir ?? '' }}"
                                                data-predikat="{{ $p->pivot->predikat ?? '' }}" title="Input Nilai">
                                                <i class="bi bi-file-text"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px;"></i>
                                    <p class="mt-2">Belum ada peserta yang terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>


    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Detail Peserta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="bi bi-person me-2"></i>Informasi Pribadi</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nama</th>
                                    <td><span id="d-nama"></span></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><span id="d-email"></span></td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td><span id="d-nip"></span></td>
                                </tr>
                                <tr>
                                    <th>Jabatan</th>
                                    <td><span id="d-jabatan"></span></td>
                                </tr>
                                <tr>
                                    <th>OPD</th>
                                    <td><span id="d-opd"></span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3"><i class="bi bi-clipboard-check me-2"></i>Status Kursus</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Status</th>
                                    <td><span id="d-status-badge"></span></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Daftar</th>
                                    <td><span id="d-daftar"></span></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Disetujui</th>
                                    <td><span id="d-disetujui"></span></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Selesai</th>
                                    <td><span id="d-selesai"></span></td>
                                </tr>
                                <tr>
                                    <th>Nilai Akhir</th>
                                    <td><span id="d-nilai"></span></td>
                                </tr>
                                <tr>
                                    <th>Predikat</th>
                                    <td><span id="d-predikat"></span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3" id="alasan-section" style="display: none;">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <strong><i class="bi bi-exclamation-triangle me-2"></i>Alasan Ditolak:</strong>
                                <p class="mb-0 mt-2" id="d-alasan"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ubah Status (Single) -->
    <div class="modal fade" id="modalStatus" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="formStatus" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Ubah Status Peserta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <p class="mb-2"><strong>Peserta:</strong> <span id="s-nama"></span></p>
                        <p class="mb-3"><strong>Status Saat Ini:</strong> <span id="s-status-current"></span></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Baru <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="status_peserta" required>
                            <option value="pending">Pending</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="selesai">Selesai</option>
                        </select>
                        <small class="form-text text-muted">Pilih status baru untuk peserta</small>
                    </div>

                    <div class="mb-3" id="alasan-ditolak-section" style="display: none;">
                        <label class="form-label">Alasan Ditolak <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alasan_ditolak" id="alasan_ditolak" rows="3"
                            placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Modal Bulk Update Status -->
    <div class="modal fade" id="modalBulkStatus" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="formBulkStatus" method="POST"
                action="{{ route('kursus.peserta.bulk.status', $kursus->id) }}">
                @csrf

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-people me-2"></i>Ubah Status Multiple Peserta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Anda akan mengubah status untuk <strong><span id="bulk-count">0</span> peserta</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status Baru <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="bulk_status" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="pending">Pending</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>

                    <div class="mb-3" id="bulk-alasan-section" style="display: none;">
                        <label class="form-label">Alasan Ditolak <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alasan_ditolak" id="bulk_alasan_ditolak" rows="3"
                            placeholder="Masukkan alasan penolakan untuk semua peserta yang dipilih..."></textarea>
                    </div>

                    <!-- Hidden input untuk peserta IDs -->
                    <input type="hidden" name="peserta_ids" id="bulk_peserta_ids">

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Ubah Status Semua
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Modal Input Nilai -->
    <div class="modal fade" id="modalNilai" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" id="formNilai" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Input Nilai Akhir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3"><strong>Peserta:</strong> <span id="n-nama"></span></p>

                    <div class="mb-3">
                        <label class="form-label">Nilai Akhir (0-100) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="nilai_akhir" id="nilai_akhir" min="0"
                            max="100" step="0.01" required placeholder="Masukkan nilai...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Predikat <span class="text-danger">*</span></label>
                        <select class="form-select" name="predikat" id="predikat" required>
                            <option value="">-- Pilih Predikat --</option>
                            <option value="sangat_baik">Sangat Baik (80-100)</option>
                            <option value="baik">Baik (70-79)</option>
                            <option value="cukup">Cukup (60-69)</option>
                            <option value="kurang">Kurang (0-59)</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Nilai
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Wait for DOM
        (function() {
            'use strict';

            // Helper functions
            function getElement(selector) {
                return document.querySelector(selector);
            }

            function getElements(selector) {
                return document.querySelectorAll(selector);
            }

            function setText(selector, text) {
                const el = getElement(selector);
                if (el) el.textContent = text;
            }

            function setHTML(selector, html) {
                const el = getElement(selector);
                if (el) el.innerHTML = html;
            }

            function getData(element, attr) {
                return element.getAttribute('data-' + attr) || '';
            }

            // Initialize when DOM is ready
            function init() {
                // Modal Detail
                getElements('.btn-detail').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const status = getData(this, 'status');
                        const alasan = getData(this, 'alasan');

                        setText('#d-nama', getData(this, 'nama'));
                        setText('#d-email', getData(this, 'email'));
                        setText('#d-nip', getData(this, 'nip'));
                        setText('#d-jabatan', getData(this, 'jabatan'));
                        setText('#d-opd', getData(this, 'opd'));
                        setText('#d-daftar', getData(this, 'daftar'));
                        setText('#d-disetujui', getData(this, 'disetujui'));
                        setText('#d-selesai', getData(this, 'selesai'));
                        setText('#d-nilai', getData(this, 'nilai'));
                        setText('#d-predikat', getData(this, 'predikat'));

                        // Status badge
                        let badgeClass = 'bg-secondary';
                        if (status == 'pending') badgeClass = 'bg-warning text-dark';
                        else if (status == 'disetujui') badgeClass = 'bg-success';
                        else if (status == 'ditolak') badgeClass = 'bg-danger';
                        else if (status == 'selesai') badgeClass = 'bg-info';

                        setHTML('#d-status-badge',
                            `<span class="badge ${badgeClass}">${status.toUpperCase()}</span>`);

                        // Show/hide alasan ditolak
                        const alasanSection = getElement('#alasan-section');
                        if (status === 'ditolak' && alasan !== '-') {
                            setText('#d-alasan', alasan);
                            alasanSection.style.display = 'block';
                        } else {
                            alasanSection.style.display = 'none';
                        }
                    });
                });

                // Modal Ubah Status (Single)
                getElements('.btn-status').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const pesertaId = getData(this, 'id');
                        const status = getData(this, 'status');
                        const nama = getData(this, 'nama');

                        setText('#s-nama', nama);
                        setHTML('#s-status-current',
                            `<span class="badge bg-secondary">${status.toUpperCase()}</span>`);
                        getElement('#status_peserta').value = status;

                        const url =
                            "{{ route('kursus.peserta.status.update', ['kursus' => $kursus->id, 'peserta' => ':id']) }}"
                            .replace(':id', pesertaId);

                        getElement('#formStatus').setAttribute('action', url);
                    });
                });

                // Show/hide alasan ditolak field (Single)
                const statusSelect = getElement('#status_peserta');
                if (statusSelect) {
                    statusSelect.addEventListener('change', function() {
                        const alasanSection = getElement('#alasan-ditolak-section');
                        const alasanField = getElement('#alasan_ditolak');

                        if (this.value === 'ditolak') {
                            alasanSection.style.display = 'block';
                            alasanField.setAttribute('required', 'required');
                        } else {
                            alasanSection.style.display = 'none';
                            alasanField.removeAttribute('required');
                        }
                    });
                }

                // Show/hide alasan ditolak field (Bulk)
                const bulkStatusSelect = getElement('#bulk_status');
                if (bulkStatusSelect) {
                    bulkStatusSelect.addEventListener('change', function() {
                        const alasanSection = getElement('#bulk-alasan-section');
                        const alasanField = getElement('#bulk_alasan_ditolak');

                        if (this.value === 'ditolak') {
                            alasanSection.style.display = 'block';
                            alasanField.setAttribute('required', 'required');
                        } else {
                            alasanSection.style.display = 'none';
                            alasanField.removeAttribute('required');
                        }
                    });
                }

                // Modal Input Nilai
                getElements('.btn-nilai').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const pesertaId = getData(this, 'id');
                        const nama = getData(this, 'nama');
                        const nilai = getData(this, 'nilai');
                        const predikat = getData(this, 'predikat');

                        setText('#n-nama', nama);
                        getElement('#nilai_akhir').value = nilai;
                        getElement('#predikat').value = predikat;

                        const url =
                            "{{ route('kursus.peserta.nilai.update', ['kursus' => $kursus->id, 'peserta' => ':id']) }}"
                            .replace(':id', pesertaId);

                        getElement('#formNilai').setAttribute('action', url);
                    });
                });

                // Auto-select predikat based on nilai
                const nilaiInput = getElement('#nilai_akhir');
                if (nilaiInput) {
                    nilaiInput.addEventListener('input', function() {
                        const nilai = parseFloat(this.value);
                        let predikat = '';

                        if (nilai >= 80) predikat = 'sangat_baik';
                        else if (nilai >= 70) predikat = 'baik';
                        else if (nilai >= 60) predikat = 'cukup';
                        else if (nilai >= 0) predikat = 'kurang';

                        getElement('#predikat').value = predikat;
                    });
                }

                // Search functionality
                const searchInput = getElement('#searchInput');
                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        const value = this.value.toLowerCase();
                        const rows = getElements('#tablePeserta tbody tr');

                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.indexOf(value) > -1 ? '' : 'none';
                        });
                    });
                }

                // Filter by status
                const filterStatus = getElement('#filterStatus');
                if (filterStatus) {
                    filterStatus.addEventListener('change', function() {
                        const value = this.value.toLowerCase();
                        const rows = getElements('#tablePeserta tbody tr');

                        rows.forEach(row => {
                            if (value === '') {
                                row.style.display = '';
                            } else {
                                const statusCell = row.querySelector('td:nth-child(6)');
                                if (statusCell) {
                                    const status = statusCell.textContent.toLowerCase();
                                    row.style.display = status.indexOf(value) > -1 ? '' : 'none';
                                }
                            }
                        });
                    });
                }

                // Filter by OPD
                const filterOPD = getElement('#filterOPD');
                if (filterOPD) {
                    filterOPD.addEventListener('change', function() {
                        const value = this.value;
                        const rows = getElements('#tablePeserta tbody tr');

                        rows.forEach(row => {
                            if (value === '') {
                                row.style.display = '';
                            } else {
                                const opdCell = row.querySelector('td:nth-child(5)');
                                if (opdCell) {
                                    const opd = opdCell.textContent.trim();
                                    row.style.display = opd === value ? '' : 'none';
                                }
                            }
                        });
                    });
                }
            }

            // Global functions for bulk actions
            window.toggleSelectAll = function(checkbox) {
                const checkboxes = getElements('.peserta-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = checkbox.checked;
                });
                updateBulkActions();
            };

            window.updateBulkActions = function() {
                const checkboxes = getElements('.peserta-checkbox:checked');
                const count = checkboxes.length;
                const bulkBar = getElement('#bulkActionBar');

                setText('#selectedCount', count);

                if (count > 0) {
                    bulkBar.style.display = 'block';
                } else {
                    bulkBar.style.display = 'none';
                    getElement('#selectAll').checked = false;
                }
            };

            window.clearSelection = function() {
                const checkboxes = getElements('.peserta-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });
                getElement('#selectAll').checked = false;
                updateBulkActions();
            };

            window.bulkUpdateStatus = function() {
                const checkboxes = getElements('.peserta-checkbox:checked');
                const ids = Array.from(checkboxes).map(cb => cb.value);

                if (ids.length === 0) {
                    alert('Pilih minimal 1 peserta!');
                    return;
                }

                // Set count and IDs
                setText('#bulk-count', ids.length);
                getElement('#bulk_peserta_ids').value = JSON.stringify(ids);

                // Show modal
                const modal = new bootstrap.Modal(getElement('#modalBulkStatus'));
                modal.show();
            };

            // Reset filter function
            window.resetFilter = function() {
                const searchInput = getElement('#searchInput');
                const filterStatus = getElement('#filterStatus');
                const filterOPD = getElement('#filterOPD');

                if (searchInput) searchInput.value = '';
                if (filterStatus) filterStatus.value = '';
                if (filterOPD) filterOPD.value = '';

                const rows = getElements('#tablePeserta tbody tr');
                rows.forEach(row => row.style.display = '');
            };

            // Export function
            window.exportData = function() {
                window.location.href = "{{ route('kursus.peserta.export', $kursus->id) }}";
            };

            // Initialize on DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush