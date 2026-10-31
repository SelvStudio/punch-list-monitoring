<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | Punch List Monitoring System</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">
    <!-- Theme style (AdminLTE v3.2.0) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <style>
        body.login-page {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
        }
        .login-box {
            width: 440px;
        }
        .card {
            border-radius: 12px;
            overflow: hidden;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <!-- Login Logo -->
    <div class="login-logo text-center mb-3">
        <a href="{{ url('/') }}" class="text-white font-weight-bold">
            <i class="fas fa-hard-hat text-warning mr-2"></i>
            <span style="font-size: 1.6rem; letter-spacing: 0.5px;">PUNCH LIST</span>
            <div style="font-size: 0.95rem; font-weight: normal; opacity: 0.85;">Sistem Monitoring Masa Pemeliharaan</div>
        </a>
    </div>

    <!-- Login Card -->
    <div class="card card-outline card-primary shadow-lg">
        <div class="card-body login-card-body p-4">
            <p class="login-box-msg font-weight-bold pb-2 text-dark">
                Silakan masuk untuk mengakses sistem
            </p>

            @include('partials.alerts')

            <form action="{{ route('login.post') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Alamat Email" value="{{ old('email') }}" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kata Sandi" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-7">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember" class="font-weight-normal text-muted small">
                                Ingat Saya
                            </label>
                        </div>
                    </div>
                    <div class="col-5">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                            <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                        </button>
                    </div>
                </div>
            </form>

            <hr class="my-3">

            <div class="demo-accounts bg-light p-3 rounded small">
                <div class="font-weight-bold text-secondary mb-2">
                    <i class="fas fa-info-circle text-info mr-1"></i> Akun Demo Pengembangan:
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span><strong>Admin:</strong> admin@punchlist.test</span>
                    <button class="btn btn-xs btn-outline-secondary copy-btn" onclick="fillLogin('admin@punchlist.test')">Pilih</button>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span><strong>Manajemen:</strong> manajemen@punchlist.test</span>
                    <button class="btn btn-xs btn-outline-secondary copy-btn" onclick="fillLogin('manajemen@punchlist.test')">Pilih</button>
                </div>
                <div class="d-flex justify-content-between">
                    <span><strong>PIC (Anton):</strong> anton@punchlist.test</span>
                    <button class="btn btn-xs btn-outline-secondary copy-btn" onclick="fillLogin('anton@punchlist.test')">Pilih</button>
                </div>
                <div class="text-muted mt-2" style="font-size: 11px;">
                    *Kata sandi seluruh akun demo: <code>password</code>
                </div>
            </div>
        </div>
        <div class="card-footer text-center bg-white border-top text-muted small py-2">
            Proyek Capstone Design &copy; 2026 Kontraktor Utama
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function fillLogin(email) {
        document.querySelector('input[name="email"]').value = email;
        document.querySelector('input[name="password"]').value = 'password';
    }
</script>
</body>
</html>
