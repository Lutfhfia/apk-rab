<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem RAB</title>
    <link rel="icon" type="image/png" href="{{ asset('foto/logo_sbk.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex min-h-screen">

    <div class="hidden lg:flex lg:w-1/2 bg-[#2D3047] items-center justify-center relative">
        <img src="{{ asset('foto/logo_sbk.png') }}" alt="Logo SBK Sertifikasi" class="w-64 md:w-80 rounded-3xl bg-white p-2">
    </div>

    <div class="w-full lg:w-1/2 bg-white flex flex-col relative px-8 sm:px-16 md:px-24 xl:px-32 py-12 justify-center">

        <div class="absolute top-12 left-8 sm:left-16 md:left-24">
            <a href="{{ url('/') }}" class="text-blue-400 hover:text-blue-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
        </div>

        <div class="max-w-sm w-full mx-auto mt-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Login Akun</h2>

            @if ($errors->any())
                <div class="mb-5 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label for="email" class="block text-sm font-medium text-gray-500 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" required autofocus
                        class="w-full border border-gray-300 px-4 py-2.5 rounded-md text-gray-700 focus:outline-none focus:border-[#2D3047] focus:ring-1 focus:ring-[#2D3047] transition" />
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-sm font-medium text-gray-500 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full border border-gray-300 px-4 py-2.5 pr-10 rounded-md text-gray-700 focus:outline-none focus:border-[#2D3047] focus:ring-1 focus:ring-[#2D3047] transition" />

                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeSlashIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}
                            class="h-4 w-4 text-[#2D3047] focus:ring-[#2D3047] border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm text-gray-500 cursor-pointer">
                            Ingatkan Saya
                        </label>
                    </div>

                    <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:text-blue-700 hover:underline transition">Lupa Kata Sandi?</a>
                </div>

                <button type="submit"
                    class="w-full bg-[#2D3047] hover:bg-[#1f2233] text-white font-medium py-3 rounded-md transition duration-200">
                    Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        togglePassword.addEventListener('click', function () {
            // Cek tipe input saat ini
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Ganti ikon
            eyeIcon.classList.toggle('hidden');
            eyeSlashIcon.classList.toggle('hidden');
        });

        // Fitur Mengingat Email (Autofill dari LocalStorage)
        const emailInput = document.getElementById('email');
        const rememberCheckbox = document.getElementById('remember');
        const loginForm = document.querySelector('form');

        // Load email dari localStorage jika ada
        const savedEmail = localStorage.getItem('remember_email');
        if (savedEmail) {
            emailInput.value = savedEmail;
            rememberCheckbox.checked = true;
        }

        loginForm.addEventListener('submit', function () {
            if (rememberCheckbox.checked) {
                localStorage.setItem('remember_email', emailInput.value);
            } else {
                localStorage.removeItem('remember_email');
            }
        });
    </script>
</body>
</html>
