@extends('layouts.main')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
    <div class="row">
        <!-- Sales Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card sales-card">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <h5 class="card-title">Total Kursus <span>| Aktif</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div class="ps-3">
                            <h6>45</h6>
                            <span class="text-success small pt-1 fw-bold">12%</span> <span
                                class="text-muted small pt-2 ps-1">increase</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End Sales Card -->

        <!-- Revenue Card -->
        <div class="col-xxl-4 col-md-6">
            <div class="card info-card revenue-card">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <h5 class="card-title">Total Instruktur <span>| Aktif</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="ps-3">
                            <h6>28</h6>
                            <span class="text-success small pt-1 fw-bold">8%</span> <span
                                class="text-muted small pt-2 ps-1">increase</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End Revenue Card -->

        <!-- Customers Card -->
        <div class="col-xxl-4 col-xl-12">
            <div class="card info-card customers-card">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <h5 class="card-title">Total Peserta <span>| Terdaftar</span></h5>

                    <div class="d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="ps-3">
                            <h6>1,244</h6>
                            <span class="text-success small pt-1 fw-bold">15%</span> <span
                                class="text-muted small pt-2 ps-1">increase</span>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End Customers Card -->

        <!-- Recent Activity -->
        <div class="col-12">
            <div class="card">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>

                <div class="card-body">
                    <h5 class="card-title">Kursus Terbaru <span>| Bulan Ini</span></h5>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Judul Kursus</th>
                                    <th scope="col">Instruktur</th>
                                    <th scope="col">Tanggal Mulai</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Peserta</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">1</th>
                                    <td>Pelatihan Dasar CPNS Golongan III</td>
                                    <td>Dr. Budi Santoso, M.Si.</td>
                                    <td>15 Nov 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>45</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">2</th>
                                    <td>Pelatihan Manajemen ASN</td>
                                    <td>Dr. Siti Nurhaliza, M.Pd.</td>
                                    <td>10 Nov 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>32</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">3</th>
                                    <td>Teknik Penyusunan Anggaran</td>
                                    <td>Ahmad Wijaya, S.E., M.Ak.</td>
                                    <td>5 Nov 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>28</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">4</th>
                                    <td>Kepemimpinan Tingkat Dasar</td>
                                    <td>Dr. Rini Anggraini, M.M.</td>
                                    <td>1 Nov 2025</td>
                                    <td><span class="badge bg-warning">Persiapan</span></td>
                                    <td>40</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">5</th>
                                    <td>Manajemen Pelayanan Publik</td>
                                    <td>Prof. Dr. Hendra Wijaya, M.AP.</td>
                                    <td>25 Oct 2025</td>
                                    <td><span class="badge bg-success">Aktif</span></td>
                                    <td>38</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-4">
                        <a href="/courses" class="btn btn-primary">Lihat Semua Kursus</a>
                    </div>
                </div>
            </div>
        </div><!-- End Recent Activity -->

        <!-- Website Traffic -->
        <div class="col-12">
            <div class="card">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Filter</h6>
                        </li>
                        <li><a class="dropdown-item" href="#">Today</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>

                <div class="card-body pb-0">
                    <h5 class="card-title">Statistik Pengguna <span>| Tahun 2025</span></h5>

                    <div id="trafficChart" style="min-height: 400px;" class="echart"></div>
                </div>
            </div>
        </div><!-- End Website Traffic -->
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            echarts.init(document.querySelector("#trafficChart")).setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    top: '5%',
                    left: 'center'
                },
                series: [{
                    name: 'Statistik Pengguna',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '18',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: [{
                            value: 1048,
                            name: 'Peserta'
                        },
                        {
                            value: 735,
                            name: 'Instruktur'
                        },
                        {
                            value: 580,
                            name: 'Administrator'
                        },
                        {
                            value: 300,
                            name: 'Staf BPSDM'
                        }
                    ]
                }]
            });
        });
    </script>
@endpush
