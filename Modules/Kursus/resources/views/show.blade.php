@extends('layouts.main')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Kursus - {{ $kursus->judul }}</h5>
            <ul class="nav nav-tabs nav-tabs-bordered d-flex">
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.show', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.show') ? 'active' : '' }}">
                        Detail
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.prasyarat', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.prasyarat') ? 'active' : '' }}">
                        Prasyarat
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.jadwal', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.jadwal') ? 'active' : '' }}">
                        Jadwal Kegiatan
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.modul', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.modul') ? 'active' : '' }}">
                        Modul
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.materi', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.materi') ? 'active' : '' }}">
                        Materi
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.tugas', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.tugas') ? 'active' : '' }}">
                        Tugas
                    </a>
                </li>

                <li class="nav-item flex-fill">
                    <a href="{{ route('course.forum', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.forum') ? 'active' : '' }}">
                        Forum
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.kuis', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.kuis') ? 'active' : '' }}">
                        Kuis
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.peserta', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.peserta') ? 'active' : '' }}">
                        Peserta
                    </a>
                </li>
                <li class="nav-item flex-fill">
                    <a href="{{ route('course.ujian', $kursus->id) }}"
                        class="nav-link text-center w-100 {{ Route::is('course.ujian') ? 'active' : '' }}">
                        Ujian
                    </a>
                </li>
            </ul>
            <div class="tab-content pt-2">
                <div class="pt-4">
                    @yield('detail-content')
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        </script>
    @endif
@endpush
