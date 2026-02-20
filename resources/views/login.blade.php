<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Owner - Nisa Cake</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    @vite(['resources/css/login-tailwind.css', 'resources/js/login.js'])
</head>
<body data-login-post="{{ route('login.post') }}" class="body-bg">

    <div class="login-card">
        <div class="photo-wrapper">
            <img src="{{ asset('images/logo.png') }}" alt="Owner" class="owner-photo" id="ownerPhoto">
        </div>
        <h2>Halo, Ibu Nisa!</h2>
        <p>Selamat datang di dapur digital Anda. Mari kita mulai hari dengan semangat baru!</p>
        <form id="loginForm">
            @csrf
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="owner" readonly>
            </div>
            <div class="input-group password-group">
                <label>PIN Rahasia</label>
                <div class="password-input-wrapper">
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan 6 angka PIN" maxlength="6" required>
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