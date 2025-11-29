@extends('layouts.main')

@section('title', 'Tambah Peserta')
@section('page-title', 'Tambah Peserta')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Tambah Peserta</h5>
                    <a href="{{ route('peserta.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('peserta.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Foto Profil -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-person-circle me-2"></i>Foto Profil
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Foto Profil</label>
                            <div class="col-md-8 col-lg-9">

                                <div class="text-center mb-3">
                                    <div id="preview-placeholder" 
                                        class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center border border-3"
                                        style="width:120px;height:120px;">
                                        <i class="bi bi-person-fill text-muted" style="font-size:60px"></i>
                                    </div>

                                    <img id="preview-foto" src="" 
                                        class="rounded-circle border border-3 d-none"
                                        style="width:120px;height:120px;object-fit:cover;">
                                </div>

                                <input type="file" 
                                    class="form-control @error('foto_profil') is-invalid @enderror"
                                    id="foto_profil" name="foto_profil" accept="image/*">

                                <div class="form-text">Format: JPG, JPEG, PNG, Max 2MB</div>

                                @error('foto_profil')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Informasi Akun -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-person-badge me-2"></i>Informasi Akun
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Username <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="username"
                                    class="form-control @error('username') is-invalid @enderror"
                                    value="{{ old('username') }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Email <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password + Strength -->
                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Password <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">

                                <div class="input-group">
                                    <input type="password" id="password"
                                        name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePassword('password')">
                                        <i id="password-icon" class="bi bi-eye"></i>
                                    </button>
                                </div>

                                @error('password')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror

                                <div id="password-strength-container" class="mt-2" style="display:none;">
                                    <div class="progress" style="height:5px;">
                                        <div id="password-strength-bar" class="progress-bar" style="width:0%;"></div>
                                    </div>
                                    <small id="password-strength-text" class="text-muted"></small>
                                </div>

                                <div id="password-requirements-container" class="mt-3" style="display:none;">
                                    <small class="fw-bold text-muted d-block">Persyaratan Password:</small>
                                    <ul class="list-unstyled small">
                                        <li id="req-length" class="text-muted"><i class="bi bi-x-circle"></i> Minimal 8 karakter</li>
                                        <li id="req-uppercase" class="text-muted"><i class="bi bi-x-circle"></i> Huruf besar</li>
                                        <li id="req-lowercase" class="text-muted"><i class="bi bi-x-circle"></i> Huruf kecil</li>
                                        <li id="req-number" class="text-muted"><i class="bi bi-x-circle"></i> Angka</li>
                                        <li id="req-special" class="text-muted"><i class="bi bi-x-circle"></i> Simbol</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <div class="input-group">
                                    <input type="password" id="password_confirmation"
                                        name="password_confirmation"
                                        class="form-control"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePassword('password_confirmation')">
                                        <i id="password_confirmation-icon" class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Data Pribadi -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-person me-2"></i>Data Pribadi
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="nama_lengkap"
                                    class="form-control @error('nama_lengkap') is-invalid @enderror"
                                    value="{{ old('nama_lengkap') }}" required>
                                @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">NIP</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="nip"
                                    class="form-control @error('nip') is-invalid @enderror"
                                    value="{{ old('nip') }}">
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Jenis Kelamin</label>
                            <div class="col-md-8 col-lg-9">
                                <select name="jenis_kelamin" id="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="laki_laki" {{ old('jenis_kelamin') == 'laki_laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="perempuan" {{ old('jenis_kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Tempat Lahir</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="tempat_lahir"
                                    class="form-control @error('tempat_lahir') is-invalid @enderror"
                                    value="{{ old('tempat_lahir') }}">
                                @error('tempat_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Tanggal Lahir</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="date" name="tanggal_lahir"
                                    class="form-control @error('tanggal_lahir') is-invalid @enderror"
                                    value="{{ old('tanggal_lahir') }}">
                                @error('tanggal_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Alamat</label>
                            <div class="col-md-8 col-lg-9">
                                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">No. Telepon</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="no_telepon"
                                    class="form-control @error('no_telepon') is-invalid @enderror"
                                    value="{{ old('no_telepon') }}">
                                @error('no_telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Data Kepegawaian -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-building me-2"></i>Data Kepegawaian
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">OPD <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <select name="opd_id" id="opd_id" class="form-select @error('opd_id') is-invalid @enderror" required>
                                    <option value="">Pilih OPD</option>
                                    @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>
                                        {{ $opd->nama_opd }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('opd_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Status Kepegawaian <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <select name="status_kepegawaian" id="status_kepegawaian" class="form-select @error('status_kepegawaian') is-invalid @enderror" required>
                                    <option value="">Pilih Status</option>
                                    <option value="pns" {{ old('status_kepegawaian') == 'pns' ? 'selected' : '' }}>PNS</option>
                                    <option value="pppk" {{ old('status_kepegawaian') == 'pppk' ? 'selected' : '' }}>PPPK</option>
                                    <option value="kontrak" {{ old('status_kepegawaian') == 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                                </select>
                                @error('status_kepegawaian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Pangkat/Golongan</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="pangkat_golongan"
                                    class="form-control @error('pangkat_golongan') is-invalid @enderror"
                                    value="{{ old('pangkat_golongan') }}"
                                    placeholder="Contoh: Penata Muda / III/a">
                                @error('pangkat_golongan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Jabatan</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" name="jabatan"
                                    class="form-control @error('jabatan') is-invalid @enderror"
                                    value="{{ old('jabatan') }}">
                                @error('jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-md-4 col-lg-3 col-form-label">Pendidikan Terakhir</label>
                            <div class="col-md-8 col-lg-9">
                                <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-select @error('pendidikan_terakhir') is-invalid @enderror">
                                    <option value="">Pilih Pendidikan</option>
                                    <option value="sma" {{ old('pendidikan_terakhir') == 'sma' ? 'selected' : '' }}>SMA/Sederajat</option>
                                    <option value="d3" {{ old('pendidikan_terakhir') == 'd3' ? 'selected' : '' }}>D3</option>
                                    <option value="s1" {{ old('pendidikan_terakhir') == 's1' ? 'selected' : '' }}>S1</option>
                                    <option value="s2" {{ old('pendidikan_terakhir') == 's2' ? 'selected' : '' }}>S2</option>
                                    <option value="s3" {{ old('pendidikan_terakhir') == 's3' ? 'selected' : '' }}>S3</option>
                                </select>
                                @error('pendidikan_terakhir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                            <a href="{{ route('peserta.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
  

    <!-- Select2 JS -->
    <script src="{{ asset('assets/select2/select2.min.js') }}"></script>

    <script>
        window.addEventListener('load', function() {
            // Check jQuery
            if (typeof jQuery === 'undefined') {
                console.error('jQuery not loaded!');
                return;
            }

            console.log('✓ Peserta create form initialized');

            // Initialize Select2 for OPD (searchable - many options)
            jQuery('#opd_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih OPD',
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 for Jenis Kelamin (no search - only 2 options)
            jQuery('#jenis_kelamin').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Jenis Kelamin',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1
            });

            // Initialize Select2 for Status Kepegawaian (no search - only 3 options)
            jQuery('#status_kepegawaian').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Status',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1
            });

            // Initialize Select2 for Pendidikan Terakhir (no search - only 5 options)
            jQuery('#pendidikan_terakhir').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Pendidikan',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: -1
            });

            console.log('✓ Select2 initialized on all dropdowns');
        });

        /* PREVIEW FOTO */
        document.getElementById('foto_profil').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    document.getElementById('preview-foto').src = ev.target.result;
                    document.getElementById('preview-foto').classList.remove('d-none');
                    document.getElementById('preview-placeholder').classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        /* TOGGLE PASSWORD */
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }

        /* PASSWORD STRENGTH */
        const pwd = document.getElementById('password');
        const bar = document.getElementById('password-strength-bar');
        const text = document.getElementById('password-strength-text');
        const reqWrap = document.getElementById('password-requirements-container');
        const strengthWrap = document.getElementById('password-strength-container');

        const req = {
            length: { regex:/^.{8,}$/, el:document.getElementById('req-length') },
            upper: { regex:/[A-Z]/, el:document.getElementById('req-uppercase') },
            lower: { regex:/[a-z]/, el:document.getElementById('req-lowercase') },
            number: { regex:/[0-9]/, el:document.getElementById('req-number') },
            special: { regex:/[^A-Za-z0-9]/, el:document.getElementById('req-special') }
        };

        pwd.addEventListener('input', () => {
            const val = pwd.value;
            if (!val.length) {
                strengthWrap.style.display = "none";
                reqWrap.style.display = "none";
                return;
            }

            strengthWrap.style.display = "block";
            reqWrap.style.display = "block";

            let score = 0;

            Object.values(req).forEach(r => {
                if (r.regex.test(val)) {
                    r.el.classList.remove('text-muted');
                    r.el.classList.add('text-success');
                    r.el.querySelector('i').className = "bi bi-check-circle";
                    score += 20;
                } else {
                    r.el.classList.add('text-muted');
                    r.el.classList.remove('text-success');
                    r.el.querySelector('i').className = "bi bi-x-circle";
                }
            });

            bar.style.width = score + "%";

            if (score < 40) { bar.className="progress-bar bg-danger"; text.textContent="Lemah"; }
            else if (score < 60) { bar.className="progress-bar bg-warning"; text.textContent="Sedang"; }
            else if (score < 80) { bar.className="progress-bar bg-info"; text.textContent="Baik"; }
            else { bar.className="progress-bar bg-success"; text.textContent="Sangat Kuat"; }
        });
    </script>
@endpush