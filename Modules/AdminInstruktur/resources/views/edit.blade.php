@extends('layouts.main')

@section('title', 'Edit Admin/Instruktur')
@section('page-title', 'Edit Admin/Instruktur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Alert Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-1"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Admin/Instruktur</h5>
                    <a href="{{ route('admin.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <!-- PENTING: enctype="multipart/form-data" untuk upload file -->
                    <form action="{{ route('admin.update', $admin->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Foto Profil Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-person-circle me-2"></i>Foto Profil
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="foto_profil" class="col-md-4 col-lg-3 col-form-label">Foto Profil</label>
                            <div class="col-md-8 col-lg-9">
                                <div class="text-center mb-3">
                                    @if($admin->foto_profil)
                                    <img src="{{ asset('storage/profile/foto/' . $admin->foto_profil) }}" 
                                        alt="Profile" id="preview-foto" 
                                        style="width: 120px; height: 120px; object-fit: cover;" 
                                        class="rounded-circle border border-3">
                                    @else
                                    <div id="preview-placeholder" class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 border border-3" 
                                        style="width: 120px; height: 120px; border-color: #e9ecef !important;">
                                        <i class="bi bi-person-fill text-muted" style="font-size: 60px;"></i>
                                    </div>
                                    <img src="" alt="Preview" id="preview-foto" 
                                        class="rounded-circle d-none border border-3" 
                                        style="width: 120px; height: 120px; object-fit: cover; border-color: #e9ecef !important;">
                                    @endif
                                </div>
                                <input type="file" class="form-control @error('foto_profil') is-invalid @enderror" 
                                    id="foto_profil" name="foto_profil" accept="image/*">
                                <div class="form-text">Format: JPG, PNG, JPEG. Max: 2MB. Kosongkan jika tidak ingin mengubah foto</div>
                                @error('foto_profil')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-person-badge me-2"></i>Informasi Akun
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="username" class="col-md-4 col-lg-3 col-form-label">Username <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                    id="username" name="username" 
                                    value="{{ old('username', $admin->username) }}" required>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-lg-3 col-form-label">Email <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                    id="email" name="email" 
                                    value="{{ old('email', $admin->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="role" class="col-md-4 col-lg-3 col-form-label">Role <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" name="role" required
                                    {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                                    <option value="">Pilih Role</option>
                                    <option value="super_admin" {{ old('role', $admin->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="instruktur" {{ old('role', $admin->role) == 'instruktur' ? 'selected' : '' }}>Instruktur</option>
                                </select>
                                @if(!auth()->user()->isSuperAdmin())
                                    <small class="text-muted">Hanya Super Admin yang dapat mengubah role</small>
                                @endif
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Section -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-shield-lock me-2"></i>Ubah Password (Opsional)
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-lg-3 col-form-label">Password Baru</label>
                            <div class="col-md-8 col-lg-9">
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                        id="password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                        <i class="bi bi-eye" id="password-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                
                                <!-- Password Strength Indicator -->
                                <div class="mt-2" id="password-strength-container" style="display: none;">
                                    <div class="progress" style="height: 5px;">
                                        <div id="password-strength-bar" class="progress-bar" role="progressbar" 
                                            style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small id="password-strength-text" class="text-muted"></small>
                                </div>

                                <!-- Password Requirements -->
                                <div class="mt-3" id="password-requirements-container" style="display: none;">
                                    <small class="fw-bold text-muted d-block mb-2">Persyaratan Password:</small>
                                    <ul class="list-unstyled small" id="password-requirements">
                                        <li id="req-length" class="text-muted">
                                            <i class="bi bi-x-circle"></i> Minimal 8 karakter
                                        </li>
                                        <li id="req-uppercase" class="text-muted">
                                            <i class="bi bi-x-circle"></i> Huruf besar (A-Z)
                                        </li>
                                        <li id="req-lowercase" class="text-muted">
                                            <i class="bi bi-x-circle"></i> Huruf kecil (a-z)
                                        </li>
                                        <li id="req-number" class="text-muted">
                                            <i class="bi bi-x-circle"></i> Angka (0-9)
                                        </li>
                                        <li id="req-special" class="text-muted">
                                            <i class="bi bi-x-circle"></i> Karakter khusus (!@#$%^&*)
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-person me-2"></i>Informasi Pribadi
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="gelar_depan" class="col-md-4 col-lg-3 col-form-label">Gelar Depan</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control @error('gelar_depan') is-invalid @enderror" 
                                    id="gelar_depan" name="gelar_depan" 
                                    value="{{ old('gelar_depan', $admin->gelar_depan) }}"
                                    placeholder="Dr., Prof., dll">
                                @error('gelar_depan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nama_lengkap" class="col-md-4 col-lg-3 col-form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                    id="nama_lengkap" name="nama_lengkap" 
                                    value="{{ old('nama_lengkap', $admin->nama_lengkap) }}" required>
                                @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="gelar_belakang" class="col-md-4 col-lg-3 col-form-label">Gelar Belakang</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control @error('gelar_belakang') is-invalid @enderror" 
                                    id="gelar_belakang" name="gelar_belakang" 
                                    value="{{ old('gelar_belakang', $admin->gelar_belakang) }}"
                                    placeholder="S.Kom., M.T., Ph.D., dll">
                                @error('gelar_belakang')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="nip" class="col-md-4 col-lg-3 col-form-label">NIP</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                                    id="nip" name="nip" 
                                    value="{{ old('nip', $admin->nip) }}">
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="no_telepon" class="col-md-4 col-lg-3 col-form-label">No. Telepon</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="text" class="form-control @error('no_telepon') is-invalid @enderror" 
                                    id="no_telepon" name="no_telepon" 
                                    value="{{ old('no_telepon', $admin->no_telepon) }}">
                                @error('no_telepon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="alamat" class="col-md-4 col-lg-3 col-form-label">Alamat</label>
                            <div class="col-md-8 col-lg-9">
                                <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                    id="alamat" name="alamat" rows="3">{{ old('alamat', $admin->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="bidang_keahlian" class="col-md-4 col-lg-3 col-form-label">Bidang Keahlian</label>
                            <div class="col-md-8 col-lg-9">
                                <textarea class="form-control @error('bidang_keahlian') is-invalid @enderror" 
                                    id="bidang_keahlian" name="bidang_keahlian" rows="3"
                                    placeholder="Contoh: Web Development, Machine Learning, Database Management">{{ old('bidang_keahlian', $admin->bidang_keahlian) }}</textarea>
                                @error('bidang_keahlian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="row mb-4 mt-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2">
                                    <i class="bi bi-envelope me-2"></i>Informasi Tambahan
                                </h6>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="email_verified_at" class="col-md-4 col-lg-3 col-form-label">Email Verified At</label>
                            <div class="col-md-8 col-lg-9">
                                <input type="datetime-local" class="form-control @error('email_verified_at') is-invalid @enderror" 
                                    id="email_verified_at" name="email_verified_at" 
                                    value="{{ old('email_verified_at', $admin->email_verified_at ? date('Y-m-d\TH:i', strtotime($admin->email_verified_at)) : '') }}">
                                <small class="text-muted">Kosongkan jika email belum diverifikasi</small>
                                @error('email_verified_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update
                            </button>
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary">
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
<script>
    // Preview foto before upload
    document.getElementById('foto_profil').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (max 2MB)
            if (file.size > 2048000) {
                alert('Ukuran file terlalu besar! Maksimal 2MB');
                this.value = '';
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                alert('File harus berupa gambar!');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview-foto');
                const placeholder = document.getElementById('preview-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Password Strength Checker
    const passwordField = document.getElementById('password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    const strengthContainer = document.getElementById('password-strength-container');
    const requirementsContainer = document.getElementById('password-requirements-container');

    // Password requirements
    const requirements = {
        length: { regex: /.{8,}/, element: document.getElementById('req-length') },
        uppercase: { regex: /[A-Z]/, element: document.getElementById('req-uppercase') },
        lowercase: { regex: /[a-z]/, element: document.getElementById('req-lowercase') },
        number: { regex: /[0-9]/, element: document.getElementById('req-number') },
        special: { regex: /[!@#$%^&*(),.?":{}|<>]/, element: document.getElementById('req-special') }
    };

    passwordField.addEventListener('focus', function() {
        if (this.value.length > 0) {
            strengthContainer.style.display = 'block';
            requirementsContainer.style.display = 'block';
        }
    });

    passwordField.addEventListener('input', function() {
        const password = this.value;
        
        // Show/hide indicators
        if (password.length > 0) {
            strengthContainer.style.display = 'block';
            requirementsContainer.style.display = 'block';
        } else {
            strengthContainer.style.display = 'none';
            requirementsContainer.style.display = 'none';
            return;
        }

        let strength = 0;
        let metRequirements = 0;

        // Check each requirement
        for (const [key, req] of Object.entries(requirements)) {
            if (req.regex.test(password)) {
                req.element.classList.remove('text-muted');
                req.element.classList.add('text-success');
                req.element.querySelector('i').classList.remove('bi-x-circle');
                req.element.querySelector('i').classList.add('bi-check-circle');
                strength += 20;
                metRequirements++;
            } else {
                req.element.classList.remove('text-success');
                req.element.classList.add('text-muted');
                req.element.querySelector('i').classList.remove('bi-check-circle');
                req.element.querySelector('i').classList.add('bi-x-circle');
            }
        }

        // Update strength bar
        strengthBar.style.width = strength + '%';
        strengthBar.setAttribute('aria-valuenow', strength);

        // Update color and text based on strength
        if (strength <= 40) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Lemah';
            strengthText.className = 'text-danger small';
        } else if (strength <= 60) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Sedang';
            strengthText.className = 'text-warning small';
        } else if (strength <= 80) {
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Baik';
            strengthText.className = 'text-info small';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Sangat Kuat';
            strengthText.className = 'text-success small';
        }
    });

    // Auto hide alerts
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush