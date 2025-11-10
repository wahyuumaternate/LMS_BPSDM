@extends('layouts.main')

@section('title', 'Edit Kursus')
@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('course.index') }}">Daftar Kursus</a></li>
@endsection
@section('page-title', 'Edit Kursus')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Form Edit Kursus</h5>

            <form class="row g-3" action={{ route('course.update', $kursus->id) }} method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="col-md-6 position-relative">
                    <label for="admin_instruktur_id" class="form-label">Instruktur <span class="text-danger">*</span></label>
                    <select id="admin_instruktur_id" name="admin_instruktur_id"
                        class="form-select @error('admin_instruktur_id') is-invalid @enderror" required>
                        @foreach ($instruktur as $item)
                            <option value={{ $item->id }} @selected(old('admin_instruktur_id') == $item->id)>
                                {{ $item->nama_lengkap_dengan_gelar }}
                            </option>
                        @endforeach
                    </select>
                    @error('admin_instruktur_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="kategori_id" class="form-label">Kategori<span class="text-danger">*</span></label>
                    <select id="kategori_id" name="kategori_id"
                        class="form-select @error('kategori_id') is-invalid @enderror" required>
                        @foreach ($kategori as $item)
                            <option value={{ $item->id }} @selected(old('kategori_id', $kursus->kategori->id) == $item->id)>
                                {{ $item->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                    <select id="level" name="level" class="form-select @error('level') is-invalid @enderror" required>
                        <option value="dasar" @selected(old('level', $kursus->level) == 'dasar')>Dasar</option>
                        <option value="menengah" @selected(old('level', $kursus->level) == 'menengah')>Menengah</option>
                        <option value="lanjut" @selected(old('level', $kursus->level) == 'lanjut')>Lanjut</option>
                    </select>
                    @error('level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                    <select id="tipe" name="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                        <option value="daring" @selected(old('tipe', $kursus->tipe) == 'daring')>Daring</option>
                        <option value="luring" @selected(old('tipe', $kursus->tipe) == 'luring')>Luring</option>
                        <option value="hybrid" @selected(old('tipe', $kursus->tipe) == 'hybrid')>Hybrid</option>
                    </select>
                    @error('tipe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror"
                        required>
                        <option value="draft" @selected(old('status', $kursus->status) == 'draft')>Draft</option>
                        <option value="aktif" @selected(old('status', $kursus->status) == 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(old('status', $kursus->status) == 'nonaktif')>Nonaktif</option>
                        <option value="selesai" @selected(old('status', $kursus->status) == 'selesai')>Selesai</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label for="judul" class="form-label">Judul Kursus <span class="text-danger">*</span></label>
                    <input type="text" name="judul" value="{{ old('judul', $kursus->judul) }}"
                        class="form-control @error('deskripsi') is-invalid @enderror" id="judul" required>
                </div>
                <div class="col-md-4">
                    <label for="kode_kursus" class="form-label">Kode Kursus <span class="text-danger">*</span></label>
                    <input type="text" name="kode_kursus" class="form-control @error('kode_kursus') is-invalid @enderror"
                        id="kode_kursus" value="{{ old('kode_kursus', $kursus->kode_kursus) }}" required>
                    @error('kode_kursus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                    <label for="deskripsi" class="col-form-label">Deskripsi <span class="text-danger">*</span></label>
                    <div class="">
                        <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3"
                            required>{{ old('deskripsi', $kursus->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="tujuan_pembelajaran" class="col-form-label">Tujuan Pembelajaran</label>
                    <div class="">
                        <textarea class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" id="tujuan_pembelajaran"
                            name="tujuan_pembelajaran" rows="3">{{ old('tujuan_pembelajaran', $kursus->tujuan_pembelajaran) }}</textarea>
                        @error('tujuan_pembelajaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="sasaran_peserta" class="col-form-label">Sasaran Peserta</label>
                    <div class="">
                        <textarea class="form-control @error('sasaran_peserta') is-invalid @enderror" id="sasaran_peserta"
                            name="sasaran_peserta" rows="3">{{ old('sasaran_peserta', $kursus->sasaran_peserta) }}</textarea>
                        @error('sasaran_peserta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="durasi_jam" class="col-form-label">Durasi (jam)</label>
                    <input type="number" min="0" class="form-control @error('durasi_jam') is-invalid @enderror"
                        id="durasi_jam" name="durasi_jam" value="{{ old('durasi_jam', $kursus->durasi_jam) }}">
                    @error('durasi_jam')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="kuota_peserta" class="col-form-label">Kuota Peserta</label>
                    <input type="number" min="0"
                        class="form-control @error('kuota_peserta') is-invalid @enderror" id="kuota_peserta"
                        name="kuota_peserta" value="{{ old('kuota_peserta', $kursus->kuota_peserta) }}">
                    @error('kuota_peserta')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="passing_grade" class="col-form-label">Passing Grade</label>
                    <input type="text" class="form-control @error('passing_grade') is-invalid @enderror"
                        id="passing_grade" name="passing_grade"
                        value="{{ old('passing_grade', $kursus->passing_grade) }}">
                    @error('passing_grade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="thumbnail" class="col-form-label">Thumbnail</label>
                    <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" id="thumbnail"
                        name="thumbnail" value="{{ old('thumbnail') }}">
                    @error('thumbnail')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">JPG, JPEG, PNG. Max: 2MB</div>
                </div>
                <div class="col-md-3">
                    <label for="tanggal_buka_pendaftaran" class="col-form-label">Tanggal Buka Pendaftaran</label>
                    <input type="date" class="form-control @error('tanggal_buka_pendaftaran') is-invalid @enderror"
                        id="tanggal_buka_pendaftaran" name="tanggal_buka_pendaftaran"
                        value="{{ old('tanggal_buka_pendaftaran', \Carbon\Carbon::parse($kursus->tanggal_buka_pendaftaran)->format('Y-m-d')) }}">
                    @error('tanggal_buka_pendaftaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="tanggal_tutup_pendaftaran" class="col-form-label">Tanggal Tutup Pendaftaran</label>
                    <input type="date" class="form-control @error('tanggal_tutup_pendaftaran') is-invalid @enderror"
                        id="tanggal_tutup_pendaftaran" name="tanggal_tutup_pendaftaran"
                        value="{{ old('tanggal_tutup_pendaftaran', \Carbon\Carbon::parse($kursus->tanggal_tutup_pendaftaran)->format('Y-m-d')) }}">
                    @error('tanggal_tutup_pendaftaran')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="tanggal_mulai_kursus" class="col-form-label">Tanggal Mulai Kursus</label>
                    <input type="date" class="form-control @error('tanggal_mulai_kursus') is-invalid @enderror"
                        id="tanggal_mulai_kursus" name="tanggal_mulai_kursus"
                        value="{{ old('tanggal_mulai_kursus', \Carbon\Carbon::parse($kursus->tanggal_mulai_kursus)->format('Y-m-d')) }}">
                    @error('tanggal_mulai_kursus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="tanggal_selesai_kursus" class="col-form-label">Tanggal Selesai Kursus</label>
                    <input type="date" class="form-control @error('tanggal_selesai_kursus') is-invalid @enderror"
                        id="tanggal_selesai_kursus" name="tanggal_selesai_kursus"
                        value="{{ old('tanggal_selesai_kursus', \Carbon\Carbon::parse($kursus->tanggal_selesai_kursus)->format('Y-m-d')) }}">
                    @error('tanggal_selesai_kursus')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('course.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    @endsection

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            //  cari instruktur
            $('#search_instruktur').on('input', function() {
                let query = $(this).val();
                if (query.length < 2) {
                    $('#search_result').addClass('d-none');
                    return;
                }

                $.ajax({
                    url: "{{ route('search.instruktur') }}",
                    type: "GET",
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let list = '';

                        data.forEach(function(item) {
                            list += `<div class="list-group-item list-group-item-action instructor-item py-1 px-2" data-id="${item.id}" data-name="${item.nama_gelar}">
                            ${item.nama_gelar}
                        </div>`;
                        });

                        $('#search_result').html(list).removeClass('d-none');
                    }
                });
            });

            //  ketika user klik salah satu instruktur
            $(document).on('click', '.instructor-item', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');

                $('#instruktur_id').val(id);
                $('#search_instruktur').val(name);
                $('#search_result').addClass('d-none');
            });

            //  deskripsi text area
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#deskripsi, #tujuan_pembelajaran, #sasaran_peserta',
                    height: 200,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code help wordcount'
                    ],
                    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                    setup: function(editor) {
                        editor.on('change', function() {
                            editor.save(); // Update textarea value
                        });
                    }
                });
            }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @if (session('error'))
            <script>
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'error',
                    title: "{{ session('error') }}"
                });
            </script>
        @endif
    @endpush
