@extends('layouts.main')

@section('title', 'Dashboard Instruktur')
@section('page-title', 'Dashboard Instruktur')

@section('content')
    <div class="row">
        <!-- Courses Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card sales-card">
                <div class="card-body">
                    <h5 class="card-title">Kursus Saya <span>| Aktif</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="ps-3">
                            <h6>5</h6>
                            <span class="text-success small pt-1 fw-bold">2</span> <span
                                class="text-muted small pt-2 ps-1">kursus baru</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End Courses Card -->

        <!-- Students Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card revenue-card">
                <div class="card-body">
                    <h5 class="card-title">Total Peserta <span>| Semua Kursus</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ps-3">
                            <h6>143</h6>
                            <span class="text-success small pt-1 fw-bold">18%</span> <span
                                class="text-muted small pt-2 ps-1">peningkatan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End Students Card -->

        <!-- Assignments Card -->
        <div class="col-xxl-4 col-xl-12">
            <div class="card info-card customers-card">
                <div class="card-body">
                    <h5 class="card-title">Tugas <span>| Belum Dinilai</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <div class="ps-3">
                            <h6>24</h6>
                            <span class="text-danger small pt-1 fw-bold">3</span> <span
                                class="text-muted small pt-2 ps-1">mendekati tenggat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End Assignments Card -->

        <!-- My Courses Section -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Kursus Saya</h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Judul Kursus</th>
                                    <th scope="col">Tanggal Mulai</th>
                                    <th scope="col">Tanggal Selesai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Peserta</th>
                                    <th scope="col">Progress</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">1</th>
                                    <td>Pelatihan Dasar CPNS Golongan III</td>
                                    <td>15 Nov 2025</td>
                                    <td>15 Dec 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>45</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 25%"
                                                aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">25%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="btn btn-sm btn-success"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">2</th>
                                    <td>Manajemen Kepegawaian Lanjutan</td>
                                    <td>10 Nov 2025</td>
                                    <td>10 Dec 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>32</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 35%"
                                                aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">35%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="btn btn-sm btn-success"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">3</th>
                                    <td>Penyusunan Anggaran Berbasis Kinerja</td>
                                    <td>5 Nov 2025</td>
                                    <td>5 Dec 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>28</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 45%"
                                                aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">45%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="btn btn-sm btn-success"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">4</th>
                                    <td>Pelatihan Kepemimpinan Tingkat Menengah</td>
                                    <td>1 Nov 2025</td>
                                    <td>1 Jan 2026</td>
                                    <td><span class="badge bg-warning">Persiapan</span></td>
                                    <td>0</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 10%"
                                                aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">10%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="btn btn-sm btn-success"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">5</th>
                                    <td>Etika Pelayanan Publik</td>
                                    <td>25 Oct 2025</td>
                                    <td>25 Nov 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>38</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 65%"
                                                aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">65%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="btn btn-sm btn-success"><i class="bi bi-pencil"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div><!-- End My Courses Section -->

        <!-- Recent Submissions -->
        <div class="col-12">
            <div class="card recent-sales overflow-auto">
                <div class="card-body">
                    <h5 class="card-title">Pengumpulan Tugas <span>| Terbaru</span></h5>

                    <table class="table table-borderless datatable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Peserta</th>
                                <th scope="col">Kursus</th>
                                <th scope="col">Tugas</th>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row"><a href="#">#2457</a></th>
                                <td>Brandon Jacob</td>
                                <td>Pelatihan Dasar CPNS Golongan III</td>
                                <td><a href="#" class="text-primary">Tugas Analisis Kasus</a></td>
                                <td>5 Nov 2025</td>
                                <td><span class="badge bg-warning">Belum Dinilai</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">Nilai</a></td>
                            </tr>
                            <tr>
                                <th scope="row"><a href="#">#2147</a></th>
                                <td>Bridie Kessler</td>
                                <td>Manajemen Kepegawaian Lanjutan</td>
                                <td><a href="#" class="text-primary">Laporan Studi Kasus</a></td>
                                <td>4 Nov 2025</td>
                                <td><span class="badge bg-warning">Belum Dinilai</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">Nilai</a></td>
                            </tr>
                            <tr>
                                <th scope="row"><a href="#">#2049</a></th>
                                <td>Ashleigh Langosh</td>
                                <td>Etika Pelayanan Publik</td>
                                <td><a href="#" class="text-primary">Presentasi Video</a></td>
                                <td>3 Nov 2025</td>
                                <td><span class="badge bg-warning">Belum Dinilai</span></td>
                                <td><a href="#" class="btn btn-sm btn-primary">Nilai</a></td>
                            </tr>
                            <tr>
                                <th scope="row"><a href="#">#2644</a></th>
                                <td>Angus Grady</td>
                                <td>Etika Pelayanan Publik</td>
                                <td><a href="#" class="text-primary">Kuis Modul 3</a></td>
                                <td>2 Nov 2025</td>
                                <td><span class="badge bg-success">Sudah Dinilai</span></td>
                                <td><a href="#" class="btn btn-sm btn-secondary">Lihat</a></td>
                            </tr>
                            <tr>
                                <th scope="row"><a href="#">#2644</a></th>
                                <td>Raheem Lehner</td>
                                <td>Penyusunan Anggaran Berbasis Kinerja</td>
                                <td><a href="#" class="text-primary">Rancangan Anggaran</a></td>
                                <td>1 Nov 2025</td>
                                <td><span class="badge bg-success">Sudah Dinilai</span></td>
                                <td><a href="#" class="btn btn-sm btn-secondary">Lihat</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- End Recent Submissions -->

        <!-- Student Progress Chart -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Progres Peserta <span>| Per Kursus</span></h5>

                    <!-- Line Chart -->
                    <div id="studentProgressChart" style="min-height: 400px;"></div>
                </div>
            </div>
        </div><!-- End Student Progress Chart -->
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Student Progress Chart
            const progressChart = echarts.init(document.querySelector("#studentProgressChart"));

            progressChart.setOption({
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: [
                        'Pelatihan Dasar CPNS Golongan III',
                        'Manajemen Kepegawaian Lanjutan',
                        'Penyusunan Anggaran Berbasis Kinerja',
                        'Etika Pelayanan Publik'
                    ]
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4']
                },
                yAxis: {
                    type: 'value',
                    axisLabel: {
                        formatter: '{value}%'
                    },
                    max: 100
                },
                series: [{
                        name: 'Pelatihan Dasar CPNS Golongan III',
                        type: 'line',
                        data: [10, 15, 20, 25],
                        smooth: true
                    },
                    {
                        name: 'Manajemen Kepegawaian Lanjutan',
                        type: 'line',
                        data: [15, 22, 30, 35],
                        smooth: true
                    },
                    {
                        name: 'Penyusunan Anggaran Berbasis Kinerja',
                        type: 'line',
                        data: [20, 28, 35, 45],
                        smooth: true
                    },
                    {
                        name: 'Etika Pelayanan Publik',
                        type: 'line',
                        data: [40, 48, 55, 65],
                        smooth: true
                    }
                ]
            });

            // Initialize datatables
            new simpleDatatables.DataTable('.datatable');

            // Handle window resize
            window.addEventListener("resize", () => {
                progressChart.resize();
            });
        });
    </script>
@endpush
