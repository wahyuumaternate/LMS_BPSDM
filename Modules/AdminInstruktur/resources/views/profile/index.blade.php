@extends('layouts.main')

@section('title', 'Profile Saya')
@section('page-title', 'Profile Saya')

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
                <div class="card-body pt-3">
                    <!-- Bordered Tabs -->
                    <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" 
                                aria-selected="true" role="tab">
                                <i class="bi bi-person-circle me-1"></i> Overview
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" 
                                aria-selected="false" role="tab" tabindex="-1">
                                <i class="bi bi-pencil-square me-1"></i> Edit Profile
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" 
                                aria-selected="false" role="tab" tabindex="-1">
                                <i class="bi bi-shield-lock me-1"></i> Change Password
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <!-- Profile Overview Tab -->
                        <div class="tab-pane fade show active profile-overview" id="profile-overview" role="tabpanel">
                            <div class="row">
                                <div class="col-xl-4">
                                    <div class="card">
                                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                                            @if(auth()->user()->foto_profil)
                                            <img src="{{ asset('storage/profile/foto/' . auth()->user()->foto_profil) }}" 
                                                alt="Profile" class="rounded-circle border border-3" 
                                                style="width: 120px; height: 120px; object-fit: cover; border-color: #e9ecef !important;">
                                            @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-3" 
                                                style="width: 120px; height: 120px; border-color: #e9ecef !important;">
                                                <i class="bi bi-person-fill text-muted" style="font-size: 60px;"></i>
                                            </div>
                                            @endif
                                            
                                            <h2 class="mt-3">{{ auth()->user()->nama_lengkap_dengan_gelar }}</h2>
                                            
                                            @if(auth()->user()->role == 'super_admin')
                                            <h3 class="text-muted"><span class="badge bg-danger">Super Admin</span></h3>
                                            @else
                                            <h3 class="text-muted"><span class="badge bg-info">Instruktur</span></h3>
                                            @endif
                                            
                                            @if(auth()->user()->nip)
                                            <div class="mt-2">
                                                <small class="text-muted">NIP: {{ auth()->user()->nip }}</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-8">
                                    <div class="card">
                                        <div class="card-body pt-3">
                                            <h5 class="card-title">Profile Details</h5>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Username</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->username }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Email</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->email }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Nama Lengkap</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->nama_lengkap }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Gelar Depan</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->gelar_depan ?? '-' }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Gelar Belakang</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->gelar_belakang ?? '-' }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">NIP</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->nip ?? '-' }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">No. Telepon</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->no_telepon ?? '-' }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Alamat</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->alamat ?? '-' }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Bidang Keahlian</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->bidang_keahlian ?? '-' }}</div>
                                            </div>

                                            <div class="row mb-3">
                                                <div class="col-lg-4 col-md-4 label fw-bold">Terdaftar Sejak</div>
                                                <div class="col-lg-8 col-md-8">{{ auth()->user()->created_at->format('d F Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Profile Tab -->
                        <div class="tab-pane fade profile-edit pt-3" id="profile-edit" role="tabpanel">
                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Foto Profil</label>
                                    <div class="col-md-8 col-lg-9">
                                        <div class="text-center mb-3">
                                            @if(auth()->user()->foto_profil)
                                            <img src="{{ asset('storage/profile/foto/' . auth()->user()->foto_profil) }}" 
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
                                        <div class="form-text">Format: JPG, PNG, JPEG. Max: 2MB</div>
                                        @error('foto_profil')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="username" class="col-md-4 col-lg-3 col-form-label">Username <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-9">
                                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                                            id="username" name="username" 
                                            value="{{ old('username', auth()->user()->username) }}" required>
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
                                            value="{{ old('email', auth()->user()->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="gelar_depan" class="col-md-4 col-lg-3 col-form-label">Gelar Depan</label>
                                    <div class="col-md-8 col-lg-9">
                                        <input type="text" class="form-control @error('gelar_depan') is-invalid @enderror" 
                                            id="gelar_depan" name="gelar_depan" 
                                            value="{{ old('gelar_depan', auth()->user()->gelar_depan) }}"
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
                                            value="{{ old('nama_lengkap', auth()->user()->nama_lengkap) }}" required>
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
                                            value="{{ old('gelar_belakang', auth()->user()->gelar_belakang) }}"
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
                                            id="nip" name="nip" value="{{ old('nip', auth()->user()->nip) }}">
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
                                            value="{{ old('no_telepon', auth()->user()->no_telepon) }}">
                                        @error('no_telepon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="alamat" class="col-md-4 col-lg-3 col-form-label">Alamat</label>
                                    <div class="col-md-8 col-lg-9">
                                        <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                            id="alamat" name="alamat" rows="3">{{ old('alamat', auth()->user()->alamat) }}</textarea>
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
                                            placeholder="Contoh: Web Development, Machine Learning, Database Management">{{ old('bidang_keahlian', auth()->user()->bidang_keahlian) }}</textarea>
                                        @error('bidang_keahlian')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Change Password Tab -->
                        <div class="tab-pane fade pt-3" id="profile-change-password" role="tabpanel">
                            <form action="{{ route('profile.password') }}" method="POST" id="changePasswordForm">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label for="current_password" class="col-md-4 col-lg-3 col-form-label">Password Saat Ini <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-9">
                                        <div class="input-group">
                                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                                id="current_password" name="current_password" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                                <i class="bi bi-eye" id="current_password-icon"></i>
                                            </button>
                                        </div>
                                        @error('current_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label for="new_password" class="col-md-4 col-lg-3 col-form-label">Password Baru <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-9">
                                        <div class="input-group">
                                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                                id="new_password" name="new_password" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                                <i class="bi bi-eye" id="new_password-icon"></i>
                                            </button>
                                        </div>
                                        @error('new_password')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        
                                        <!-- Password Strength Indicator -->
                                        <div class="mt-2">
                                            <div class="progress" style="height: 5px;">
                                                <div id="password-strength-bar" class="progress-bar" role="progressbar" 
                                                    style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small id="password-strength-text" class="text-muted"></small>
                                        </div>

                                        <!-- Password Requirements -->
                                        <div class="mt-3">
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

                                <div class="row mb-3">
                                    <label for="new_password_confirmation" class="col-md-4 col-lg-3 col-form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <div class="col-md-8 col-lg-9">
                                        <div class="input-group">
                                            <input type="password" class="form-control" 
                                                id="new_password_confirmation" name="new_password_confirmation" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                                <i class="bi bi-eye" id="new_password_confirmation-icon"></i>
                                            </button>
                                        </div>
                                        <small id="password-match-text" class="text-muted"></small>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-danger" id="submitPasswordBtn" disabled>
                                        <i class="bi bi-shield-lock me-1"></i> Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
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
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    const matchText = document.getElementById('password-match-text');
    const submitBtn = document.getElementById('submitPasswordBtn');

    // Password requirements
    const requirements = {
        length: { regex: /.{8,}/, element: document.getElementById('req-length') },
        uppercase: { regex: /[A-Z]/, element: document.getElementById('req-uppercase') },
        lowercase: { regex: /[a-z]/, element: document.getElementById('req-lowercase') },
        number: { regex: /[0-9]/, element: document.getElementById('req-number') },
        special: { regex: /[!@#$%^&*(),.?":{}|<>]/, element: document.getElementById('req-special') }
    };

    newPassword.addEventListener('input', function() {
        const password = this.value;
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

        // Check if password matches confirmation
        checkPasswordMatch();
        
        // Enable/disable submit button
        updateSubmitButton(metRequirements === 5);
    });

    confirmPassword.addEventListener('input', checkPasswordMatch);

    function checkPasswordMatch() {
        const password = newPassword.value;
        const confirm = confirmPassword.value;

        if (confirm.length > 0) {
            if (password === confirm) {
                matchText.textContent = 'Password cocok';
                matchText.className = 'text-success small mt-1';
                confirmPassword.classList.remove('is-invalid');
                confirmPassword.classList.add('is-valid');
            } else {
                matchText.textContent = 'Password tidak cocok';
                matchText.className = 'text-danger small mt-1';
                confirmPassword.classList.remove('is-valid');
                confirmPassword.classList.add('is-invalid');
            }
        } else {
            matchText.textContent = '';
            confirmPassword.classList.remove('is-valid', 'is-invalid');
        }

        // Update submit button state
        updateSubmitButton();
    }

    function updateSubmitButton(requirementsMet = null) {
        const password = newPassword.value;
        const confirm = confirmPassword.value;
        
        // Check if requirements are met (if not provided, check manually)
        if (requirementsMet === null) {
            requirementsMet = Object.values(requirements).every(req => req.regex.test(password));
        }

        const passwordsMatch = password === confirm && confirm.length > 0;
        const currentPassword = document.getElementById('current_password').value.length > 0;

        if (requirementsMet && passwordsMatch && currentPassword) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

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

    // Switch to appropriate tab on validation errors
    @if($errors->has('current_password') || $errors->has('new_password'))
        document.querySelector('[data-bs-target="#profile-change-password"]').click();
    @elseif($errors->any())
        document.querySelector('[data-bs-target="#profile-edit"]').click();
    @endif
</script>
@endpush