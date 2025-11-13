@extends('layouts.main')

@section('title', 'Daftar Hasil Ujian')
@section('page-title', 'Daftar Hasil Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Hasil Ujian</h5>
                    </div>

                    <!-- Filter Form -->
                    <form action="{{ route('hasil-ujian.index') }}" method="GET" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="ujian_id" class="form-label">Filter Ujian</label>
                            <select class="form-select" name="ujian_id" id="ujian_id" onchange="this.form.submit()">
                                <option value="">-- Semua Ujian --</option>
                                @foreach ($ujians as $u)
                                    <option value="{{ $u->id }}"
                                        {{ request('ujian_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->judul_ujian }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="peserta_id" class="form-label">Filter Peserta</label>
                            <select class="form-select" name="peserta_id" id="peserta_id" onchange="this.form.submit()">
                                <option value="">-- Semua Peserta --</option>
                                @foreach ($pesertas as $p)
                                    <option value="{{ $p->id }}"
                                        {{ request('peserta_id') == $p->id ? 'selected' : '' }}>
                                        {{ $p->user->name ?? $p->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" onchange="this.form.submit()">
                                <option value="">-- Semua Status --</option>
                                <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Lulus</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Tidak Lulus
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="date_from" name="date_from"
                                value="{{ request('date_from') }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="date_to" name="date_to"
                                value="{{ request('date_to') }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Filter</button>
                        </div>
                    </form>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">Peserta</th>
                                    <th scope="col">Ujian</th>
                                    <th scope="col">Waktu Pengerjaan</th>
                                    <th scope="col">Durasi</th>
                                    <th scope="col">Nilai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hasil as $key => $h)
                                    <tr class="{{ $h->is_simulation ? 'table-info' : '' }}">
                                        <td scope="row">{{ $hasil->firstItem() + $key }}</td>
                                        <td>
                                            @if ($h->is_simulation)
                                                <span class="badge bg-info">SIMULASI</span><br>
                                                {{ $h->peserta->nama_lengkap ?? 'Admin/Instruktur' }}
                                            @else
                                                {{ $h->peserta->nama_lengkap ?? 'Peserta #' . $h->peserta_id }}
                                                {{-- {{ $hasil->peserta->nama_lengkap }} --}}
                                            @endif
                                        </td>
                                        <td>{{ $h->ujian->judul_ujian ?? 'N/A' }}</td>
                                        <td>{{ $h->waktu_mulai ? $h->waktu_mulai->format('d M Y H:i') : '-' }}</td>
                                        <td>{{ $h->getDurationTaken() }}</td>
                                        <td>{{ number_format($h->nilai, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $h->getStatusBadgeClass() }}">
                                                {{ $h->getStatusText() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('hasil-ujian.show', $h->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i> Detail
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data hasil ujian</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($hasil, 'links'))
                        {{ $hasil->withQueryString()->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
