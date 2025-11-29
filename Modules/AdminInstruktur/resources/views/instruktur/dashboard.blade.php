@extends('layouts.main')

@section('title', 'Dashboard Instruktur')
@section('page-title', 'Dashboard Instruktur')

@section('content')
    <div class="row">
        <!-- Total Kursus Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Total Kursus</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $stats['totalKursus'] }}</h6>
                            <span class="text-success small pt-1 fw-bold">{{ $stats['kursusAktif'] }}</span> 
                            <span class="text-muted small pt-2 ps-1">aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Peserta Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Total Peserta</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ number_format($stats['totalPeserta']) }}</h6>
                            <span class="text-muted small pt-2 ps-1">semua kursus</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tugas Pending Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">Tugas Pending</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $stats['tugasBelumDinilai'] }}</h6>
                            <span class="text-{{ $stats['tugasBelumDinilai'] > 0 ? 'danger' : 'success' }} small pt-1 fw-bold">
                                {{ $stats['tugasBelumDinilai'] > 0 ? 'Perlu dinilai' : 'Semua dinilai' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Konten Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Total Konten</h5>
                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-collection"></i>
                        </div>
                        <div class="ps-3">
                            <h6>{{ $stats['totalModul'] + $stats['totalMateri'] }}</h6>
                            <span class="text-success small pt-1 fw-bold">{{ $stats['totalModul'] }}</span> 
                            <span class="text-muted small pt-2 ps-1">modul</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kursus Saya -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Kursus Saya</h5>

                    @if($myCourses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Judul Kursus</th>
                                    <th scope="col">Tanggal Mulai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Peserta</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myCourses as $index => $kursus)
                                <tr>
                                    <th scope="row">{{ $index + 1 }}</th>
                                    <td>{{ \Str::limit($kursus->judul, 40) }}</td>
                                    <td>{{ $kursus->tanggal_mulai_kursus ? \Carbon\Carbon::parse($kursus->tanggal_mulai_kursus)->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($kursus->status == 'aktif')
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($kursus->status == 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif($kursus->status == 'nonaktif')
                                            <span class="badge bg-danger">Nonaktif</span>
                                        @else
                                            <span class="badge bg-info">Selesai</span>
                                        @endif
                                    </td>
                                    <td>{{ $kursus->peserta_count ?? 0 }}</td>
                                    <td>
                                        <a href="{{ route('course.show', $kursus->id) }}" class="btn btn-sm btn-primary" title="Lihat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('course.edit', $kursus->id) }}" class="btn btn-sm btn-success" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada kursus</p>
                        <a href="{{ route('course.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Buat Kursus Baru
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Chart Distribusi Peserta -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Distribusi Peserta</h5>

                    @if(count($chartData['labels']) > 0)
                    <div id="pesertaChart" style="min-height: 300px;" class="echart"></div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-bar-chart" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">Belum ada data</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- <!-- Quick Actions -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Aksi Cepat</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('course.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Buat Kursus Baru
                        </a>
                        <a href="{{ route('course.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-journal-text me-2"></i>Kelola Kursus
                        </a>
                        <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-person-lines-fill me-2"></i>Edit Profil
                        </a>
                    </div>
                </div>
            </div> --}}
        </div>

        <!-- Tugas Belum Dinilai -->
        @if($recentSubmissions->count() > 0)
        <div class="col-12">
            <div class="card recent-sales overflow-auto">
                <div class="card-body">
                    <h5 class="card-title">Tugas Belum Dinilai <span>| Terbaru</span></h5>

                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Peserta</th>
                                <th scope="col">Kursus</th>
                                <th scope="col">Tugas</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSubmissions as $index => $submission)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $submission->nama_peserta }}</td>
                                <td>{{ \Str::limit($submission->judul_kursus, 30) }}</td>
                                <td><a href="#" class="text-primary">{{ \Str::limit($submission->judul_tugas, 30) }}</a></td>
                                <td>{{ \Carbon\Carbon::parse($submission->tanggal_submit)->diffForHumans() }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square me-1"></i>Nilai
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Initialize ECharts untuk Distribusi Peserta
        const chartDom = document.querySelector("#pesertaChart");
        if (chartDom && typeof echarts !== 'undefined') {
            const chartData = @json($chartData);
            
            if (chartData.labels.length > 0) {
                const myChart = echarts.init(chartDom);
                
                const option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: '{b}: {c} peserta ({d}%)'
                    },
                    legend: {
                        orient: 'vertical',
                        left: 'left',
                        textStyle: {
                            fontSize: 11
                        }
                    },
                    series: [{
                        name: 'Peserta',
                        type: 'pie',
                        radius: '50%',
                        data: chartData.labels.map((label, index) => ({
                            value: chartData.data[index],
                            name: label.length > 25 ? label.substring(0, 25) + '...' : label,
                            itemStyle: { color: chartData.colors[index] }
                        })),
                        emphasis: {
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        }
                    }]
                };
                
                myChart.setOption(option);
                
                // Responsive
                window.addEventListener('resize', function() {
                    myChart.resize();
                });
            }
        }
    });
</script>
@endpush