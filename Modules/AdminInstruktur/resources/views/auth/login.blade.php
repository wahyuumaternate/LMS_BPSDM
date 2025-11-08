@extends('admininstruktur::layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="container">
        <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

                        <div class="d-flex justify-content-center py-4">
                            <a href="/" class="logo d-flex align-items-center w-auto">
                                <img src="{{ asset('logo.png') }}" alt="Logo BPSDM">
                                <span class="d-none d-lg-block">LMS BPSDM</span>
                            </a>
                        </div><!-- End Logo -->

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="pt-4 pb-2">
                                    <h5 class="card-title text-center pb-0 fs-4">Login ke Akun Anda</h5>
                                    <p class="text-center small">Masukkan email & password untuk login</p>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                @endif

                                <form class="row g-3 needs-validation" method="POST"
                                    action="{{ route('admin.login.submit') }}" novalidate id="login-form">
                                    @csrf
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text" id="inputGroupPrepend"><i
                                                    class="bi bi-envelope"></i></span>
                                            <input type="email" name="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                value="{{ old('email', app()->environment('local') ? 'superadmin@example.com' : '') }}"
                                                required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text" id="inputGroupPrepend"><i
                                                    class="bi bi-lock"></i></span>
                                            <input type="password" name="password"
                                                class="form-control @error('password') is-invalid @enderror" id="password"
                                                value="{{ app()->environment('local') ? 'password123' : '' }}" required>
                                            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                            </span>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" value="true"
                                                id="remember" {{ app()->environment('local') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">Ingat saya</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button class="btn btn-primary w-100" type="submit">Login</button>
                                    </div>

                                    <div class="col-12 text-center">
                                        <p class="small mb-0">Lupa password? Hubungi administrator</p>
                                    </div>
                                </form>

                                @if (app()->environment('local'))
                                    <div class="mt-3">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle w-100" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                Dev Login Options
                                            </button>
                                            <ul class="dropdown-menu w-100">
                                                <li><a class="dropdown-item" href="#"
                                                        onclick="loginAs('superadmin@example.com', 'password123')">Login as
                                                        Super Admin</a></li>

                                                <li><a class="dropdown-item" href="#"
                                                        onclick="loginAs('instruktur@example.com', 'password123')">Login as
                                                        Instruktur</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><a class="dropdown-item" href="#"
                                                        onclick="document.getElementById('login-form').submit()">Submit Form
                                                        Now</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if (app()->environment('local'))
                            <div class="alert alert-warning text-center" role="alert">
                                <strong>Mode Development</strong> - Credentials diisi otomatis
                            </div>
                        @endif

                        <div class="credits">
                            &copy; {{ date('Y') }} <strong>BPSDM</strong>. All Rights Reserved
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        });

        @if (app()->environment('local'))
            // Function to quickly login with different user types
            function loginAs(email, password) {
                document.getElementById('email').value = email;
                document.getElementById('password').value = password;
                document.getElementById('login-form').submit();
            }
        @endif
    </script>
@endpush
