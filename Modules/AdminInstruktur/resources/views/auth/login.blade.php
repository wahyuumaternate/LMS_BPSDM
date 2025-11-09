<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LMS BPSDM</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #0061f2;
            --primary-dark: #0050c9;
            --primary-light: #e8efff;
            --secondary: #6c757d;
            --text-dark: #212832;
            --text-muted: #69707a;
            --border-color: #e5e9f2;
            --white: #ffffff;
            --shadow-sm: 0 .125rem .25rem rgba(33, 40, 50, .15);
            --shadow: 0 .5rem 1rem rgba(33, 40, 50, .15);
            --border-radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, sans-serif;
            background-color: #f8f9fa;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            line-height: 1.5;
            font-size: 16px;
        }

        .container {
            padding: 1rem;
            width: 100%;
        }

        .row {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 0;
        }

        .login-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .login-image {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: var(--white);
            height: 100%;
            padding: 3rem 2rem;
        }

        .login-image img {
            max-width: 100px;
            margin-bottom: 1.5rem;
        }

        .login-image h2 {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .login-image p {
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .login-features {
            padding-left: 0;
            list-style: none;
            margin-top: 1.5rem;
        }

        .login-features li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .login-features li i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .login-form-container {
            padding: 3rem 2rem;
            height: 100%;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            max-height: 60px;
            margin-bottom: 1.5rem;
        }

        .login-title {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .login-subtitle {
            color: var(--text-muted);
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--text-dark);
            background-color: var(--white);
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            height: calc(3rem + 2px);
        }

        .form-control:focus {
            color: var(--text-dark);
            background-color: var(--white);
            border-color: var(--primary);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 97, 242, 0.15);
        }

        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }

        .input-group .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--text-muted);
            text-align: center;
            white-space: nowrap;
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-right: 0;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .input-group .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }

        .input-group .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            z-index: 10;
        }

        .form-check {
            display: block;
            min-height: 1.5rem;
            padding-left: 1.5rem;
            margin-bottom: 0;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
            margin-top: 0.25em;
            margin-left: -1.5rem;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 97, 242, 0.15);
        }

        .btn {
            display: inline-block;
            font-weight: 500;
            line-height: 1.5;
            color: var(--text-dark);
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.75rem 1.25rem;
            font-size: 1rem;
            border-radius: var(--border-radius);
            transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }

        .btn-primary {
            color: var(--white);
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            color: var(--white);
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-primary:focus {
            color: var(--white);
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: 0 0 0 0.2rem rgba(0, 97, 242, 0.25);
        }

        .alert {
            position: relative;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: var(--border-radius);
        }

        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        .alert-success {
            color: #0f5132;
            background-color: #d1e7dd;
            border-color: #badbcc;
        }

        .alert-warning {
            color: #664d03;
            background-color: #fff3cd;
            border-color: #ffecb5;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-muted);
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .login-image {
                display: none;
            }

            .container {
                padding: 1.5rem;
            }

            .login-form-container {
                padding: 2rem 1.5rem;
            }
        }

        @media (min-width: 768px) and (max-width: 991.98px) {
            .login-image {
                padding: 2rem 1.5rem;
            }

            .login-form-container {
                padding: 2.5rem 2rem;
            }
        }

        @media (min-width: 992px) {
            .container {
                padding: 2rem;
            }

            .login-card {
                min-height: 600px;
            }

            .login-image {
                padding: 4rem 3rem;
            }

            .login-form-container {
                padding: 4rem 3rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="login-card">
                    <div class="row g-0">
                        <!-- Left side panel - Hidden on mobile -->
                        <div class="col-md-6 login-image">
                            <img src="{{ asset('logo.png') }}" alt="Logo BPSDM" class="img-fluid">
                            <h2>Selamat Datang</h2>
                            <p>Masuk ke portal Learning Management System untuk mengelola pembelajaran dan pengembangan
                                sumber daya manusia.</p>

                            <ul class="login-features">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Akses materi pembelajaran</span>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Kelola kursus dan peserta</span>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Pantau perkembangan pelatihan</span>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Dapatkan sertifikat digital</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Right side panel with login form -->
                        <div class="col-md-6">
                            <div class="login-form-container d-flex flex-column">
                                <div class="login-header">
                                    <!-- Logo - Only shown on mobile -->
                                    <div class="d-md-none">
                                        <img src="{{ asset('logo.png') }}" alt="Logo BPSDM" class="img-fluid">
                                    </div>
                                    <h1 class="login-title">Login</h1>
                                    <p class="login-subtitle">Masukkan kredensial untuk akses sistem</p>
                                </div>

                                <!-- Alert messages -->
                                @if ($errors->any())
                                    <div class="alert alert-danger" role="alert">
                                        <ul class="mb-0 ps-3">
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

                                <!-- Login Form -->
                                <form method="POST" action="{{ route('admin.login.submit') }}" id="login-form">
                                    @csrf

                                    <div class="form-group">
                                        <label for="email" class="form-label">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-envelope"></i>
                                            </span>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror" id="email"
                                                name="email" placeholder="nama@example.com"
                                                value="{{ old('email', app()->environment('local') ? 'superadmin@example.com' : '') }}"
                                                required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-lock"></i>
                                            </span>
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror"
                                                id="password" name="password" placeholder="Masukkan password"
                                                value="{{ app()->environment('local') ? 'password123' : '' }}" required>
                                            <span class="password-toggle" id="togglePassword">
                                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                            </span>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember"
                                                id="remember" {{ app()->environment('local') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remember">
                                                Ingat saya
                                            </label>
                                        </div>
                                        <a href="#" class="text-decoration-none text-primary">Lupa password?</a>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                        </button>
                                    </div>
                                </form>

                                <!-- Dev options - only in local environment -->
                                @if (app()->environment('local'))
                                    <div class="mt-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-gear-fill me-1"></i> Dev Login Options
                                            </button>
                                            <ul class="dropdown-menu w-100">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="loginAs('superadmin@example.com', 'password123')">
                                                        <i class="bi bi-person-fill-gear me-2"></i>Login as Super Admin
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="loginAs('instruktur@example.com', 'password123')">
                                                        <i class="bi bi-person-fill me-2"></i>Login as Instruktur
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="document.getElementById('login-form').submit()">
                                                        <i class="bi bi-send-fill me-2"></i>Submit Form Now
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning text-center mt-3" role="alert">
                                        <small><i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            <strong>Mode Development</strong> - Credentials diisi otomatis</small>
                                    </div>
                                @endif

                                <!-- Footer -->
                                <div class="login-footer mt-auto">
                                    <p class="mb-0">&copy; {{ date('Y') }} <strong>BPSDM</strong>. All Rights
                                        Reserved</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
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
</body>

</html>
