<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/dashboard') || request()->is('/') ? '' : 'collapsed' }}" href="/admin/dashboard">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <!-- Instructor Section -->
        <li class="nav-heading">Instruktur</li>

        <!-- Course Management -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('courses*') ? '' : 'collapsed' }}" data-bs-target="#courses-nav"
                data-bs-toggle="collapse" href="#">
                <i class="bi bi-journal-text"></i><span>Manajemen Kursus</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="courses-nav" class="nav-content collapse {{ request()->is('courses*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/courses/create" class="{{ request()->is('courses/create') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Buat Kursus Baru</span>
                    </a>
                </li>
                <li>
                    <a href="/courses"
                        class="{{ request()->is('courses') && !request()->is('courses/*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Daftar Kursus</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Course Management Nav -->

        <!-- Instructor Reports -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('reports/instructor*') ? '' : 'collapsed' }}"
                data-bs-target="#instructor-reports-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-bar-chart"></i><span>Laporan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="instructor-reports-nav"
                class="nav-content collapse {{ request()->is('reports/instructor*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/reports/instructor/activities"
                        class="{{ request()->is('reports/instructor/activities*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Aktivitas Pembelajaran</span>
                    </a>
                </li>
                <li>
                    <a href="/reports/instructor/results"
                        class="{{ request()->is('reports/instructor/results*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Hasil Pembelajaran</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Instructor Reports Nav -->

        <!-- Admin Section -->
        <li class="nav-heading">Admin</li>

        
        <!-- Course Categories - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('content/kategori-kursus*') ? '' : 'collapsed' }}"
                href="{{ route('kategori.kategori-kursus.index') }}">
                <i class="bi bi-tag"></i>
                <span>Kategori Kursus</span>
            </a>
        </li><!-- End Course Categories Nav -->

        <!-- Jenis Kursus - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('content/jenis-kursus*') ? '' : 'collapsed' }}"
                href="{{ route('kategori.jenis-kursus.index') }}">
                <i class="bi bi-tags"></i>
                <span>Jenis Kursus</span>
            </a>
        </li><!-- End Jenis Kursus Nav -->

        <!-- Admin Reports -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('reports/admin*') ? '' : 'collapsed' }}"
                data-bs-target="#admin-reports-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-file-earmark-bar-graph"></i><span>Laporan Admin</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="admin-reports-nav" class="nav-content collapse {{ request()->is('reports/admin*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/reports/admin/system"
                        class="{{ request()->is('reports/admin/system*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Laporan Sistem</span>
                    </a>
                </li>
                <li>
                    <a href="/reports/admin/analytics"
                        class="{{ request()->is('reports/admin/analytics*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Analitik Platform</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Admin Reports Nav -->

        <!-- Certificate Management - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('sertifikat*') || request()->is('verify-certificate*') ? '' : 'collapsed' }}"
                data-bs-target="#certificates-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-patch-check"></i><span>Sertifikat</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="certificates-nav"
                class="nav-content collapse {{ request()->is('sertifikat*') || request()->is('verify-certificate*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('sertifikat.index') }}"
                        class="{{ request()->is('sertifikat') && !request()->is('sertifikat/create') && !request()->is('sertifikat/bulk*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Daftar Sertifikat</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sertifikat.create') }}"
                        class="{{ request()->is('sertifikat/create') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Tambah Sertifikat</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('sertifikat.bulk.generate-form') }}"
                        class="{{ request()->is('sertifikat/bulk*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Generate Massal</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Certificate Management Nav -->

        <!-- Settings - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('admin/admin') || request()->is('admin/admin/*') || request()->is('admin/peserta') || request()->is('admin/peserta/*') || request()->is('admin/opd') || request()->is('admin/opd/*') ? '' : 'collapsed' }}" 
                data-bs-target="#settings-nav"
                data-bs-toggle="collapse" 
                href="#">
                <i class="bi bi-gear"></i><span>Pengaturan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="settings-nav" class="nav-content collapse {{ request()->is('admin/admin') || request()->is('admin/admin/*') || request()->is('admin/peserta') || request()->is('admin/peserta/*') || request()->is('admin/opd') || request()->is('admin/opd/*') ? 'show' : '' }}" 
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('admin.index') }}" 
                        class="{{ request()->is('admin/admin') || request()->is('admin/admin/*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Admin/Instruktur</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('peserta.index') }}" 
                        class="{{ request()->is('admin/peserta') || request()->is('admin/peserta/*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Peserta</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('opd.index') }}"
                        class="{{ request()->is('admin/opd') || request()->is('admin/opd/*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>OPD</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Settings Nav -->
     
        <!-- Sidebar Profile Box -->
        <div class="sidebar-profile text-center mt-4 mb-3 p-3 rounded shadow-sm"
            style="background: #f8f9fa; border: 1px solid #e4e4e4;">

            <!-- Foto Profil -->
            <img src="{{ Auth::user()->foto_profil 
                ? asset('/storage/profile/foto/' . Auth::user()->foto_profil) 
                : asset('assets/img/avatar-laki-laki.webp') }}"
                alt="Profile"
                class="rounded-circle mb-2"
                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #dee2e6;">

            <!-- Nama -->
            <h6 class="mb-0 fw-bold">{{ Auth::user()->nama_lengkap ?? Auth::user()->username }}</h6>

            <!-- Role -->
            <small class="text-muted d-block mb-3">
                {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
            </small>

            <!-- Tombol Lihat Profil -->
            <a href="{{ route('profile.index') }}" class="btn btn-sm btn-outline-primary w-100 mb-2">
                <i class="bi bi-person-lines-fill me-1"></i> Profil Saya
            </a>

            <!-- Tombol Logout -->
            <button class="btn btn-sm btn-outline-danger w-100" onclick="sweetLogout()">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </div>

    </ul>

</aside>