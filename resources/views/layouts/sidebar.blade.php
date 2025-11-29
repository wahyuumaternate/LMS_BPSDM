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

        <!-- Daftar Kursus - Instruktur & Super Admin -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('courses') && !request()->is('courses/*') ? '' : 'collapsed' }}" href="/courses">
                <i class="bi bi-journal-text"></i>
                <span>Daftar Kursus</span>
            </a>
        </li>

        <!-- Certificate Management - Super Admin Only -->
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
                {{-- <li>
                    <a href="{{ route('sertifikat.create') }}"
                        class="{{ request()->is('sertifikat/create') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Tambah Sertifikat</span>
                    </a>
                </li> --}}
                <li>
                    <a href="{{ route('sertifikat.bulk.generate-form') }}"
                        class="{{ request()->is('sertifikat/bulk*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Generate Sertifikat</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Certificate Management Nav -->

        <!-- Admin Section - HANYA SUPER ADMIN -->
        @if(Auth::guard('admin_instruktur')->user()->role === 'super_admin')
        <li class="nav-heading">Admin</li>
<!-- Buat Kursus Baru - Instruktur & Super Admin -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('courses/create') ? '' : 'collapsed' }}" href="/courses/create">
                <i class="bi bi-plus-circle"></i>
                <span>Buat Kursus Baru</span>
            </a>
        </li>
        <!-- Course Categories - Super Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('content/kategori-kursus*') ? '' : 'collapsed' }}"
                href="{{ route('kategori.kategori-kursus.index') }}">
                <i class="bi bi-tag"></i>
                <span>Kategori Kursus</span>
            </a>
        </li><!-- End Course Categories Nav -->

        <!-- Jenis Kursus - Super Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('content/jenis-kursus*') ? '' : 'collapsed' }}"
                href="{{ route('kategori.jenis-kursus.index') }}">
                <i class="bi bi-tags"></i>
                <span>Jenis Kursus</span>
            </a>
        </li><!-- End Jenis Kursus Nav -->

        

        <!-- Settings - Super Admin Only -->
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
        @endif
        <!-- End Super Admin Only Section -->
     
        <!-- Sidebar Profile Box -->
        <div class="sidebar-profile text-center mt-4 mb-3 p-3 rounded shadow-sm"
            style="background: #f8f9fa; border: 1px solid #e4e4e4;">

            <!-- Foto Profil -->
            <img src="{{ Auth::guard('admin_instruktur')->user()->foto_profil 
                ? asset('/storage/profile/foto/' . Auth::guard('admin_instruktur')->user()->foto_profil) 
                : asset('assets/img/avatar-laki-laki.webp') }}"
                alt="Profile"
                class="rounded-circle mb-2"
                style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #dee2e6;">

            <!-- Nama -->
            <h6 class="mb-0 fw-bold">{{ Auth::guard('admin_instruktur')->user()->nama_lengkap ?? Auth::guard('admin_instruktur')->user()->username }}</h6>

            <!-- Role -->
            <small class="text-muted d-block mb-3">
                {{ ucfirst(str_replace('_', ' ', Auth::guard('admin_instruktur')->user()->role)) }}
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