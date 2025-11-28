@extends('kursus::show')

@section('title', 'Detail Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Kursus</a></li>
    <li class="breadcrumb-item">{{ $kursus->judul }}</li>
@endsection
@section('page-title', 'Detail Kursus')

@section('detail-content')
    <div class="row g-3">
        @if($kursus->thumbnail)
        <div class="row">
            <p><b>Thumbnail</b></p>
            <div class="card col-3">
                <img src="{{ asset('/storage/kursus/thumbnail/' . $kursus->thumbnail) }}" class="card-img-top" alt="Thumbnail Kursus">
            </div>
        </div>
        @endif
        
        <div class="col-md-3">
            <p>
                <b>Instruktur</b>
                <br>{{ $kursus->adminInstruktur->nama_lengkap_dengan_gelar }}
            </p>
        </div>
        <div class="col-md-3">
            <p>
                <b>Kode Kursus</b>
                <br>{{ $kursus->kode_kursus }}
            </p>
        </div>
    </div>
    <div class="row g-3 mt-1">
        <div class="col-md-3">
            <p>
                <b>Judul Kursus</b>
                <br>{{ $kursus->judul }}
            </p>
        </div>
        
        {{-- Ganti kategori dengan jenisKursus --}}
        <div class="col-md-3">
            <p>
                <b>Jenis Kursus</b>
                <br>{{ $kursus->jenisKursus->nama_jenis }}
            </p>
        </div>
        <div class="col-md-3">
            <p>
                <b>Kategori</b>
                <br>{{ $kursus->jenisKursus->kategoriKursus->nama_kategori }}
            </p>
        </div>
        
        <div class="col-4 col-md-2">
            <p>
                <b>Level</b>
                <br>{{ ucwords($kursus->level) }}
            </p>
        </div>
        <div class="col-4 col-md-2">
            <p>
                <b>Tipe</b>
                <br>{{ ucwords($kursus->tipe) }}
            </p>
        </div>
        <div class="col-4 col-md-2">
            <p>
                <b>Status</b>
                <br>
                @switch($kursus->status)
                    @case('draft')
                        <span class="badge bg-warning">{{ strtoupper($kursus->status) }}</span>
                    @break

                    @case('aktif')
                        <span class="badge bg-success">{{ strtoupper($kursus->status) }}</span>
                    @break

                    @case('nonaktif')
                        <span class="badge bg-danger">{{ strtoupper($kursus->status) }}</span>
                    @break

                    @case('selesai')
                        <span class="badge bg-primary">{{ strtoupper($kursus->status) }}</span>
                    @break

                    @default
                @endswitch
            </p>
        </div>

        <div class="col-md-12">
            <p>
                <b>Deskripsi</b>
                <br>{!! $kursus->deskripsi ?? '-' !!}
            </p>
        </div>
        <div class="col-md-12">
            <p>
                <b>Tujuan Pembelajaran</b>
                <br>{!! $kursus->tujuan_pembelajaran ?? '-' !!}
            </p>
        </div>
        <div class="col-md-12">
            <p>
                <b>Sasaran Peserta</b>
                <br>{!! $kursus->sasaran_peserta ?? '-' !!}
            </p>
        </div>

        <div class="col-md-3">
            <p>
                <b>Durasi</b>
                <br>{{ $kursus->durasi_jam }} Jam
            </p>
        </div>
        <div class="col-md-3">
            <p>
                <b>Kuota Peserta</b>
                <br>{{ $kursus->kuota_peserta }} Peserta
            </p>
        </div>
        <div class="col-md-4">
            <p>
                <b>Passing Grade</b>
                <br>{{ $kursus->passing_grade }}
            </p>
        </div>

        <div class="col-md-3">
            <p>
                <b>Tanggal Buka Pendaftaran</b>
                <br>{{ $kursus->tanggal_buka_pendaftaran ? \Carbon\Carbon::parse($kursus->tanggal_buka_pendaftaran)->format('d-m-Y') : '-' }}
            </p>
        </div>
        <div class="col-md-3">
            <p>
                <b>Tanggal Tutup Pendaftaran</b>
                <br>{{ $kursus->tanggal_tutup_pendaftaran ? \Carbon\Carbon::parse($kursus->tanggal_tutup_pendaftaran)->format('d-m-Y') : '-' }}
            </p>
        </div>
        <div class="col-md-3">
            <p>
                <b>Tanggal Mulai Kursus</b>
                <br>{{ $kursus->tanggal_mulai_kursus ? \Carbon\Carbon::parse($kursus->tanggal_mulai_kursus)->format('d-m-Y') : '-' }}
            </p>
        </div>
        <div class="col-md-3">
            <p>
                <b>Tanggal Selesai Kursus</b>
                <br>{{ $kursus->tanggal_selesai_kursus ? \Carbon\Carbon::parse($kursus->tanggal_selesai_kursus)->format('d-m-Y') : '-' }}
            </p>
        </div>
        <div>
            <a href="{{ route('course.edit', $kursus->id) }}" class="btn btn-success btn-sm">
                <i class="bi bi-pencil-fill"></i> Edit Kursus
            </a>
        </div>
    </div>
@endsection