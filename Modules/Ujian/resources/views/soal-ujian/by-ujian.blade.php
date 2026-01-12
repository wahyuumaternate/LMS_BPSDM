@extends('layouts.main')

@section('title', 'Daftar Soal Ujian')
@section('page-title', 'Daftar Soal Ujian')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Daftar Soal Ujian: {{ $ujian->judul_ujian }}</h5>
                        <div>
                            <div class="btn-group">
                                <a href="{{ route('soal-ujian.create', ['ujian_id' => $ujian->id]) }}"
                                    class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Tambah Soal
                                </a>
                                {{-- <a href="{{ route('soal-ujian.create-bulk', ['ujian_id' => $ujian->id]) }}"
                                    class="btn btn-success">
                                    <i class="bi bi-upload"></i> Input Soal Massal
                                </a> --}}
                            </div>
                        </div>
                    </div>

                    <!-- Ujian Info -->
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Kursus:</strong> {{ $ujian->kursus->nama_kursus }}</p>
                                <p class="mb-1"><strong>Waktu:</strong>
                                    @if ($ujian->waktu_mulai && $ujian->waktu_selesai)
                                        {{ $ujian->waktu_mulai->format('d M Y H:i') }} -
                                        {{ $ujian->waktu_selesai->format('d M Y H:i') }}
                                    @else
                                        Tidak dibatasi
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Durasi:</strong> {{ $ujian->durasi_menit }} menit</p>
                                <p class="mb-1"><strong>Jumlah Soal:</strong> {{ $ujian->jumlah_soal }} soal</p>
                            </div>
                        </div>
                    </div>

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
                                    <th scope="col" style="width: 5%">No.</th>
                                    <th scope="col" style="width: 10%">Tipe</th>
                                    <th scope="col" style="width: 40%">Pertanyaan</th>
                                    <th scope="col" style="width: 15%">Jawaban Benar</th>
                                    <th scope="col" style="width: 10%">Tingkat</th>
                                    <th scope="col" style="width: 5%">Poin</th>
                                    <th scope="col" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($soals as $key => $soal)
                                    <tr>
                                        <th scope="row">{{ $key + 1 }}</th>
                                        <td>
                                            <span class="text-dark">
                                                {{ $soal->getFormattedType() }}
                                            </span>
                                        </td>
                                        <td class="text-dark">
                                            {!! Str::limit(strip_tags($soal->pertanyaan), 100) !!}
                                        </td>
                                        <td class="text-dark">
                                            @if ($soal->tipe_soal == 'pilihan_ganda')
                                                @if ($soal->jawaban_benar == 'A')
                                                    {{ Str::limit(strip_tags($soal->pilihan_a), 30) }}
                                                @elseif($soal->jawaban_benar == 'B')
                                                    {{ Str::limit(strip_tags($soal->pilihan_b), 30) }}
                                                @elseif($soal->jawaban_benar == 'C')
                                                    {{ Str::limit(strip_tags($soal->pilihan_c), 30) }}
                                                @elseif($soal->jawaban_benar == 'D')
                                                    {{ Str::limit(strip_tags($soal->pilihan_d), 30) }}
                                                @endif
                                            @elseif($soal->tipe_soal == 'essay')
                                                <span class="text-muted">Perlu penilaian manual</span>
                                            @elseif($soal->tipe_soal == 'benar_salah')
                                                {{ $soal->jawaban_benar }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-dark">
                                                {{ $soal->getFormattedDifficulty() }}
                                            </span>
                                        </td>
                                        <td>{{ $soal->poin }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                {{-- <a href="{{ route('soal-ujian.show', $soal->id) }}"
                                                    class="btn btn-info btn-sm" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a> --}}
                                                <a href="{{ route('soal-ujian.edit', $soal->id) }}"
                                                    class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('soal-ujian.destroy', $soal->id) }}" method="POST"
                                                    class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada soal untuk ujian ini</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($soals, 'links'))
                        {{ $soals->withQueryString()->links() }}
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('ujians.show', $ujian->id) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Detail Ujian
                        </a>
                    </div>
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
                            'Apakah Anda yakin ingin menghapus soal ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endpush
