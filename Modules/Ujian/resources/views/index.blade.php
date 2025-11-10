@extends('layouts.main')

@section('title', 'Daftar Ujian')
@section('page-title', 'Daftar Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Ujian Kursus</h5>
                        <div>
                            <a href="{{ route('ujians.create', request()->query()) }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Tambah Ujian
                            </a>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('ujians.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="kursus_id" class="form-label">Filter berdasarkan Kursus</label>
                            <select class="form-select" name="kursus_id" id="kursus_id" onchange="this.form.submit()">
                                <option value="">-- Semua Kursus --</option>
                                @foreach (\Modules\Kursus\Entities\Kursus::orderBy('judul')->get() as $kursus)
                                    <option value="{{ $kursus->id }}"
                                        {{ request('kursus_id') == $kursus->id ? 'selected' : '' }}>
                                        {{ $kursus->judul }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

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
                                    <th scope="col">No.</th>
                                    <th scope="col">Kursus</th>
                                    <th scope="col">Judul Ujian</th>
                                    <th scope="col">Waktu Pelaksanaan</th>
                                    <th scope="col">Durasi</th>
                                    <th scope="col">Passing Grade</th>
                                    <th scope="col">Jumlah Soal</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ujians as $key => $ujian)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>{{ $ujian->kursus->judul ?? 'N/A' }}</td>
                                        <td>{{ $ujian->judul_ujian }}</td>
                                        <td>
                                            @if ($ujian->waktu_mulai && $ujian->waktu_selesai)
                                                {{ $ujian->waktu_mulai->format('d M Y H:i') }} -
                                                {{ $ujian->waktu_selesai->format('d M Y H:i') }}
                                            @else
                                                Tidak dibatasi
                                            @endif
                                        </td>
                                        <td>{{ $ujian->durasi_menit ?? '-' }} {{ $ujian->durasi_menit ? 'menit' : '' }}
                                        </td>
                                        <td>{{ $ujian->passing_grade ?? '-' }}%</td>
                                        <td>{{ $ujian->jumlah_soal ?? '0' }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $ujian->isActive() ? 'success' : ($ujian->hasStarted() ? 'danger' : 'warning') }}">
                                                {{ $ujian->getStatusText() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('ujians.show', $ujian->id) }}">
                                                            <i class="bi bi-eye"></i> Detail
                                                        </a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('soal-ujian.by-ujian', $ujian->id) }}">
                                                            <i class="bi bi-list-check"></i> Kelola Soal
                                                        </a></li>
                                                    <li><a class="dropdown-item"
                                                            href="{{ route('ujians.edit', $ujian->id) }}">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </a></li>
                                                    <li>
                                                        <a class="dropdown-item text-info"
                                                            href="{{ route('ujians.simulate', $ujian->id) }}">
                                                            <i class="bi bi-play-circle"></i> Simulasi Ujian
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-primary"
                                                            href="{{ route('hasil-ujian.ujian-overview', $ujian->id) }}">
                                                            <i class="bi bi-bar-chart"></i> Lihat Hasil
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('ujians.destroy', $ujian->id) }}"
                                                            method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">Tidak ada data ujian</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($ujians, 'links'))
                        {{ $ujians->withQueryString()->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm(
                            'Apakah Anda yakin ingin menghapus ujian ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
