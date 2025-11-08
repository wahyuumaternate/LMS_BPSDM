<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('/') ? '' : 'collapsed' }}" href="/">
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
                <li>
                    <a href="/courses/modules" class="{{ request()->is('courses/modules*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Kelola Modul</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Course Management Nav -->

        <!-- Content Management -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('content*') ? '' : 'collapsed' }}" data-bs-target="#content-nav"
                data-bs-toggle="collapse" href="#">
                <i class="bi bi-file-earmark-text"></i><span>Konten Pembelajaran</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="content-nav" class="nav-content collapse {{ request()->is('content*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('materi.index') }}"
                        class="{{ request()->is('content/materials*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Materi Pembelajaran</span>
                    </a>
                </li>
                <li>
                    <a href="/content/assessments" class="{{ request()->is('content/assessments*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Soal & Tugas</span>
                    </a>
                </li>
                <li>
                    <a href="/content/quizzes" class="{{ request()->is('content/quizzes*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span> Ujian</span>
                    </a>
                </li>
                <li>
                    <a href="/content/quizzes" class="{{ request()->is('content/quizzes*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Kuis</span>
                    </a>
                </li>
                <li>
                    <a href="/content/resources" class="{{ request()->is('content/resources*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Resource & Referensi</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Content Management Nav -->

        <!-- Participant Management -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('participants*') ? '' : 'collapsed' }}"
                data-bs-target="#participants-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-person-badge"></i><span>Manajemen Peserta</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="participants-nav" class="nav-content collapse {{ request()->is('participants*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/participants/enrollment"
                        class="{{ request()->is('participants/enrollment*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Pendaftaran Peserta</span>
                    </a>
                </li>
                <li>
                    <a href="/participants/progress"
                        class="{{ request()->is('participants/progress*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Progress Peserta</span>
                    </a>
                </li>
                <li>
                    <a href="/participants/grades" class="{{ request()->is('participants/grades*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Nilai & Evaluasi</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Participant Management Nav -->

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

        <!-- Certificates for Instructors -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('certificates/instructor*') ? '' : 'collapsed' }}"
                data-bs-target="#instructor-certificates-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-award"></i><span>Sertifikat</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="instructor-certificates-nav"
                class="nav-content collapse {{ request()->is('certificates/instructor*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/certificates/instructor/issue"
                        class="{{ request()->is('certificates/instructor/issue*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Penerbitan Sertifikat</span>
                    </a>
                </li>
                <li>
                    <a href="/certificates/instructor/list"
                        class="{{ request()->is('certificates/instructor/list*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Daftar Sertifikat</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Certificates for Instructors Nav -->

        <!-- Admin Section -->
        <li class="nav-heading">Admin</li>

        <!-- User Management - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('users*') ? '' : 'collapsed' }}" data-bs-target="#users-nav"
                data-bs-toggle="collapse" href="#">
                <i class="bi bi-people"></i><span>Manajemen User</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="users-nav" class="nav-content collapse {{ request()->is('users*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/users/create" class="{{ request()->is('users/create') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Registrasi User Baru</span>
                    </a>
                </li>
                <li>
                    <a href="/users/manage-roles" class="{{ request()->is('users/manage-roles') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Kelola Peran & Otorisasi</span>
                    </a>
                </li>
                <li>
                    <a href="/users"
                        class="{{ request()->is('users') && !request()->is('users/*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Daftar User</span>
                    </a>
                </li>
            </ul>
        </li><!-- End User Management Nav -->

        <!-- Course Categories - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('courses/categories*') ? '' : 'collapsed' }}"
                href="/courses/categories">
                <i class="bi bi-tag"></i>
                <span>Kategori Kursus</span>
            </a>
        </li><!-- End Course Categories Nav -->

        <!-- Admin Reports -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('reports/admin*') ? '' : 'collapsed' }}"
                data-bs-target="#admin-reports-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-file-earmark-bar-graph"></i><span>Laporan Admin</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="admin-reports-nav"
                class="nav-content collapse {{ request()->is('reports/admin*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/reports/admin/system"
                        class="{{ request()->is('reports/admin/system*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Laporan Sistem</span>
                    </a>
                </li>
                <li>
                    <a href="/reports/admin/summary"
                        class="{{ request()->is('reports/admin/summary*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Ringkasan Laporan</span>
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
            <a class="nav-link {{ request()->is('certificates/admin*') ? '' : 'collapsed' }}"
                data-bs-target="#admin-certificates-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-patch-check"></i><span>Manajemen Sertifikat</span><i
                    class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="admin-certificates-nav"
                class="nav-content collapse {{ request()->is('certificates/admin*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/certificates/admin/templates"
                        class="{{ request()->is('certificates/admin/templates*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Template Sertifikat</span>
                    </a>
                </li>
                <li>
                    <a href="/certificates/admin/validation"
                        class="{{ request()->is('certificates/admin/validation*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Validasi Sertifikat</span>
                    </a>
                </li>
                <li>
                    <a href="/certificates/admin/all"
                        class="{{ request()->is('certificates/admin/all*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Semua Sertifikat</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Certificate Management Nav -->

        <!-- Settings - Admin Only -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('settings*') ? '' : 'collapsed' }}" data-bs-target="#settings-nav"
                data-bs-toggle="collapse" href="#">
                <i class="bi bi-gear"></i><span>Pengaturan</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="settings-nav" class="nav-content collapse {{ request()->is('settings*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="/settings/general" class="{{ request()->is('settings/general*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Umum</span>
                    </a>
                </li>
                <li>
                    <a href="/settings/appearance"
                        class="{{ request()->is('settings/appearance*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Tampilan</span>
                    </a>
                </li>
                <li>
                    <a href="/settings/notifications"
                        class="{{ request()->is('settings/notifications*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Notifikasi</span>
                    </a>
                </li>
                <li>
                    <a href="/settings/security" class="{{ request()->is('settings/security*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Keamanan</span>
                    </a>
                </li>
                <li>
                    <a href="/settings/backup" class="{{ request()->is('settings/backup*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Backup & Restore</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Settings Nav -->

        <!-- User Profile Section -->
        <li class="nav-heading">User</li>

        <li class="nav-item">
            <a class="nav-link {{ request()->is('profile*') ? '' : 'collapsed' }}" href="/profile">
                <i class="bi bi-person"></i>
                <span>Profil</span>
            </a>
        </li><!-- End Profile Page Nav -->

        <li class="nav-item">
            <a class="nav-link {{ request()->is('help*') ? '' : 'collapsed' }}" href="/help">
                <i class="bi bi-question-circle"></i>
                <span>Bantuan</span>
            </a>
        </li><!-- End Help Page Nav -->

        <li class="nav-item">
            <a class="nav-link" href="#"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li><!-- End Logout Page Nav -->

    </ul>

</aside>
