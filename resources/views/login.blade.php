<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Owner - Nisa Cake</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/login-tailwind.css', 'resources/js/login.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
        }
        .login-card {
            background: linear-gradient(145deg, #1e293b 0%, #334155 100%);
            border: 1px solid #475569;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .photo-wrapper {
            background: linear-gradient(145deg, #334155 0%, #1e293b 100%);
            border: 3px solid #06b6d4;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
            /* Logo shape - more flexible for different logo sizes */
            width: 110px;
            height: 110px;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px;
        }
        .owner-photo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 10px;
        }
        h2 {
            color: #f1f5f9;
        }
        p {
            color: #94a3b8;
        }
        .input-group label {
            color: #cbd5e1;
        }
        .form-control {
            background: rgba(51, 65, 85, 0.8);
            border: 1px solid #475569;
            color: #f1f5f9;
            position: relative;
            z-index: 10;
            pointer-events: auto;
        }
        .form-control::placeholder {
            color: #64748b;
        }
        .form-control:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2);
            outline: none;
            background: rgba(51, 65, 85, 1);
        }
        .password-input-wrapper {
            position: relative;
            z-index: 10;
        }
        .btn-login {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
            position: relative;
            z-index: 10;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #22d3ee 0%, #06b6d4 100%);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4);
        }
        .brand-logo {
            color: #64748b;
        }
        .toggle-password {
            color: #64748b;
            z-index: 20;
            position: relative;
        }
        .toggle-password:hover {
            color: #06b6d4;
        }
        .error-message {
            color: #f87171;
            z-index: 10;
            position: relative;
        }
        .loading-overlay {
            background: rgba(15, 23, 42, 0.95);
        }
        .loading-text {
            color: #06b6d4;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-card {
                padding: 1.5rem 1.25rem;
                margin: 0 0.75rem;
            }

            .photo-wrapper {
                width: 80px;
                height: 80px;
            }

            .btn-login {
                width: 100%;
                padding: 0.875rem 1rem;
                min-height: 48px;
            }

            .input-group {
                margin-bottom: 1rem;
            }

            .form-control {
                padding: 0.75rem 0.875rem;
                min-height: 48px;
            }

            .password-input-wrapper {
                min-height: 48px;
            }

            .toggle-password {
                min-height: 48px;
                min-width: 48px;
            }
        }

        @media (min-width: 481px) {
            .login-card {
                padding: 2rem 2.5rem;
            }
        }
    </style>
</head>
<body data-login-post="{{ route('login.post') }}">

    <div class="login-card">
        <div class="photo-wrapper">
            <img src="{{ asset('images/logo.png') }}" alt="Nisa Cake Logo" class="owner-photo" id="ownerPhoto">
        </div>
        <h2 class="text-xl sm:text-2xl">Halo, Ibu Nisa!</h2>
        <p class="text-xs sm:text-sm">Selamat datang di dapur digital Anda. Mari kita mulai hari dengan semangat baru!</p>
        <form id="loginForm">
            @csrf
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="owner" readonly>
            </div>
            <div class="input-group password-group">
                <label>PIN Rahasia</label>
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan 6 angka PIN" maxlength="6" required autocomplete="off">
                    <button type="button" tabindex="-1" class="toggle-password" id="togglePassword" aria-label="Tampilkan PIN">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <div class="error-message" id="errorMsg"></div>
            </div>
            <button type="submit" class="btn-login" id="btnLogin">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
                Buka Dapur
            </button>
        </form>
    </div>

    <div class="brand-logo">© Nisa Cake System v1.0</div>

    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Menyiapkan Dapur...</div>
    </div>

</body>
</html>
