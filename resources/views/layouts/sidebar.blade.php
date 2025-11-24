<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <!-- Dashboard -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('/') ? '' : 'collapsed' }}" href="/admin/dashboard">
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
                {{-- <li>
                    <a href="/courses/modules" class="{{ request()->is('courses/modules*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Kelola Modul</span>
                    </a>
                </li> --}}
            </ul>
        </li><!-- End Course Management Nav -->

        {{-- <!-- Content Management -->
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
                        class="{{ request()->segment(2) == 'materials' && !request()->is('*/reorder*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Materi Pembelajaran</span>
                    </a>
                </li>
                <!-- Materi Pembelajaran Submenu -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->segment(1) == 'content' && in_array(request()->segment(2), ['materials', 'progress', 'progress-dashboard']) ? '' : 'collapsed' }}"
                        data-bs-target="#learning-materials-nav" data-bs-toggle="collapse" href="#">
                        <i class="bi bi-journal-text"></i><span>Materi Pembelajaran</span><i
                            class="bi bi-chevron-down ms-auto"></i>
                    </a>
                    <ul id="learning-materials-nav"
                        class="nav-content collapse {{ request()->segment(1) == 'content' && in_array(request()->segment(2), ['materials', 'progress', 'progress-dashboard']) ? 'show' : '' }}">
                        <li>
                            <a href="{{ route('materi.index') }}"
                                class="{{ request()->segment(2) == 'materials' && !request()->is('*/reorder*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Materi Pembelajaran</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('materi.reorder.form', ['modul_id' => request('modul_id') ?? '']) }}"
                                class="{{ request()->segment(2) == 'materials' && request()->is('*/reorder*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Pengaturan Urutan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('progres-materi.index') }}"
                                class="{{ request()->segment(2) == 'progress' && request()->segment(3) != 'dashboard' ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Progress Pembelajaran</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('progres-materi.dashboard') }}"
                                class="{{ request()->segment(2) == 'progress-dashboard' ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Dashboard Progress</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- Other Content Nav Items -->
                <li>
                    <a href="{{ route('tugas.index') }}"
                        class="{{ request()->is('content/assessments*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Soal & Tugas</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('submission.index') }}"
                        class="{{ request()->is('content/submissions*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Pengumpulan Tugas</span>
                    </a>
                </li>
                <li>
                    <a href="/content/exams" class="{{ request()->is('content/exams*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Ujian</span>
                    </a>
                </li>
                <!-- Quiz Management Section -->
                <li
                    class="sidebar-item has-sub {{ request()->is('content/quizzes*') || request()->is('content/soal-quiz*') || request()->is('content/hasil-quiz*') ? 'active' : '' }}">
                    <a href="#" class="sidebar-link">
                        <i class="bi bi-patch-question-fill"></i>
                        <span>Manajemen Quiz</span>
                    </a>
                    <ul
                        class="submenu {{ request()->is('content/quizzes*') || request()->is('content/soal-quiz*') || request()->is('content/hasil-quiz*') ? 'active' : '' }}">
                        <li>
                            <a href="/content/quizzes" class="{{ request()->is('content/quizzes*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Kuis</span>
                            </a>
                        </li>
                        <li>
                            <a href="/content/soal-quiz"
                                class="{{ request()->is('content/soal-quiz*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Soal Quiz</span>
                            </a>
                        </li>
                        <li>
                            <a href="/content/hasil-quiz"
                                class="{{ request()->is('content/hasil-quiz*') ? 'active' : '' }}">
                                <i class="bi bi-circle"></i><span>Hasil Quiz</span>
                            </a>
                        </li>
                    </ul>
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
                        <i class="bi bi-circle"></i><span>Daftar Peserta</span>
                    </a>
                </li>
                <li>
                    <a href="/participants/progress"
                        class="{{ request()->is('participants/progress*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Progress Peserta</span>
                    </a>
                </li>
                <li>
                    <a href="/participants/grades"
                        class="{{ request()->is('participants/grades*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Nilai & Evaluasi</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Participant Management Nav -->

        <!-- Ujian Management -->
        <li class="nav-item">
            <a class="nav-link {{ request()->is('content/ujians*') || request()->is('content/soal-ujian*') || request()->is('content/hasil-ujian*') ? '' : 'collapsed' }}"
                data-bs-target="#ujian-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-clipboard-check"></i><span>Ujian</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="ujian-nav"
                class="nav-content collapse {{ request()->is('content/ujians*') || request()->is('content/soal-ujian*') || request()->is('content/hasil-ujian*') ? 'show' : '' }}"
                data-bs-parent="#sidebar-nav">
                <li>
                    <a href="{{ route('ujians.index') }}"
                        class="{{ request()->routeIs('ujians.index') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Daftar Ujian</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('ujians.create') }}"
                        class="{{ request()->routeIs('ujians.create') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Buat Ujian</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('soal-ujian.create-bulk') }}"
                        class="{{ request()->routeIs('soal-ujian.create-bulk') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Buat Soal Massal</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('hasil-ujian.index') }}"
                        class="{{ request()->routeIs('hasil-ujian.*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>Hasil Ujian</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Ujian Management Nav --> --}}
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
        {{-- <li>
            <a href="{{ route('sertifikat.verify', ['nomor' => '']) }}"
                class="{{ request()->is('verify-certificate*') ? 'active' : '' }}">
                <i class="bi bi-circle"></i><span>Verifikasi Sertifikat</span>
            </a>
        </li> --}}
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
                    <a href="{{ route('admin.index') }}" class="{{ request()->is('admin.index') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>User</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('opd.index') }}"
                        class="{{ request()->is('settings/appearance*') ? 'active' : '' }}">
                        <i class="bi bi-circle"></i><span>OPD</span>
                    </a>
                </li>

            </ul>
        </li><!-- End Settings Nav -->

     


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
