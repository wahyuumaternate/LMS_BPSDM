@extends('layouts.main')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Kursus Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card sales-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Kursus</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['totalKursus'] }}</h3>
                            
                        </div>
                        <div class="card-icon rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-journal-text text-primary fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Instruktur Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card revenue-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Instruktur</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['totalInstruktur'] }}</h3>
                            
                        </div>
                        <div class="card-icon rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-person-badge text-success fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Peserta Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card customers-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Peserta</h6>
                            <h3 class="mb-0 fw-bold">{{ number_format($stats['totalPeserta']) }}</h3>
                            
                        </div>
                        <div class="card-icon rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-people text-info fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kursus Aktif Card -->
        <div class="col-xxl-3 col-md-6">
            <div class="card info-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-2">Kursus Aktif</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['kursusAktif'] }}</h3>
                           
                        </div>
                        <div class="card-icon rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width: 60px; height: 60px;">
                            <i class="bi bi-lightning-charge text-warning fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Kursus Terbaru -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="card-title mb-1">
                            <i class="bi bi-bookmark-star text-primary me-2"></i>Kursus Terbaru
                        </h5>
                        <small class="text-muted">Daftar kursus yang baru dibuat bulan ini</small>
                    </div>
                    <div class="dropdown">
                       
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Hari Ini</a></li>
                            <li><a class="dropdown-item" href="#">Minggu Ini</a></li>
                            <li><a class="dropdown-item active" href="#">Bulan Ini</a></li>
                            <li><a class="dropdown-item" href="#">Tahun Ini</a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4" style="width: 5%;"></th>
                                    <th style="width: 30%;">Judul Kursus</th>
                                    <th style="width: 20%;">Instruktur</th>
                                    <th style="width: 15%;">Tanggal Mulai</th>
                                    <th style="width: 12%;">Status</th>
                                    <th class="text-center" style="width: 10%;">Peserta</th>
                                    <th class="text-center" style="width: 8%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentKursus as $index => $kursus)
                                <tr>
                                    <td class="px-4"><strong> <div class="flex-shrink-0 me-3">
                                                <div class="bg-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }} bg-opacity-10 rounded p-2">
                                                    <i class="bi bi-{{ ['mortarboard', 'briefcase', 'calculator', 'award', 'people'][$index % 5] }} text-{{ ['primary', 'success', 'info', 'warning', 'danger'][$index % 5] }}"></i>
                                                </div>
                                            </div></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                           
                                            <div>
                                                <div class="fw-semibold">{{ $kursus->judul }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 150px;">
                                            {{ $kursus->adminInstruktur->nama_lengkap ?? 'Belum ditentukan' }}
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $kursus->tanggal_mulai_kursus ? \Carbon\Carbon::parse($kursus->tanggal_mulai)->format('d M Y') : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($kursus->status == 'aktif')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i>Aktif
                                        </span>
                                        @elseif($kursus->status == 'persiapan')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-clock-history me-1"></i>Persiapan
                                        </span>
                                        @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <i class="bi bi-x-circle me-1"></i>{{ ucfirst($kursus->status) }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill">{{ $kursus->peserta->count() ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route("course.show", $kursus->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada kursus terbaru</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-0 text-center py-3">
                    <a href="/courses" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-box-arrow-up-right me-2"></i>Lihat Semua Kursus
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Statistik Pengguna Chart -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-1">
                        <i class="bi bi-pie-chart text-primary me-2"></i>Statistik Pengguna
                    </h5>
                    <small class="text-muted">Distribusi pengguna sistem</small>
                </div>
                <div class="card-body">
                    <div id="trafficChart" style="min-height: 280px;" class="echart"></div>
                </div>
            </div>

            {{-- <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-1">
                        <i class="bi bi-lightning-charge-fill text-warning me-2"></i>Aksi Cepat
                    </h5>
                    <small class="text-muted">Shortcut ke fitur utama</small>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.create') }}" class="btn btn-outline-primary text-start">
                            <i class="bi bi-person-plus me-2"></i>Tambah Admin/Instruktur
                        </a>
                        <a href="{{ route('peserta.create') }}" class="btn btn-outline-success text-start">
                            <i class="bi bi-people me-2"></i>Tambah Peserta
                        </a>
                        <a href="{{ route('opd.index') }}" class="btn btn-outline-info text-start">
                            <i class="bi bi-building me-2"></i>Kelola OPD
                        </a>
                        <a href="#" class="btn btn-outline-warning text-start">
                            <i class="bi bi-journal-plus me-2"></i>Buat Kursus Baru
                        </a>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-1">
                        <i class="bi bi-clock-history text-primary me-2"></i>Aktivitas Terbaru
                    </h5>
                    <small class="text-muted">Log aktivitas sistem terkini</small>
                </div>
                <div class="card-body">
                    @if(count($recentActivities) > 0)
                        <div class="activity">
                            @foreach($recentActivities as $activity)
                            <div class="activity-item d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="activite-label text-muted me-3" style="min-width: 80px;">
                                    <small>{{ $activity['time'] }}</small>
                                </div>
                                <i class="bi bi-circle-fill activity-badge text-{{ $activity['color'] }} me-3"></i>
                                <div class="activity-content">
                                    <strong>{{ $activity['message'] }}</strong>
                                    @if(isset($activity['details']))
                                    <span class="text-muted">"{{ $activity['details'] }}"</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4">Belum ada aktivitas terbaru</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .activity-badge {
        font-size: 8px;
        margin-top: 5px;
    }
    
    .table > :not(caption) > * > * {
        padding: 1rem 0.75rem;
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Initialize ECharts with real data from controller
        const chartDom = document.querySelector("#trafficChart");
        if (chartDom && typeof echarts !== 'undefined') {
            const myChart = echarts.init(chartDom);
            
            // Get chart data from controller
            const chartData = @json($chartData);
            
            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'horizontal',
                    bottom: '0%',
                    left: 'center'
                },
                series: [{
                    name: 'Pengguna',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    center: ['50%', '45%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '20',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: chartData.labels.map((label, index) => ({
                        value: chartData.data[index],
                        name: label,
                        itemStyle: { color: chartData.colors[index] }
                    }))
                }]
            };
            
            myChart.setOption(option);
            
            // Responsive
            window.addEventListener('resize', function() {
                myChart.resize();
            });
        }
    });
</script>
@endpush