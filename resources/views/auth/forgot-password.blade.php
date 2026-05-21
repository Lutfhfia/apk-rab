<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Sistem RAB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-100 flex min-h-screen items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 relative">
        <div class="mb-6">
            <a href="{{ route('login') }}" class="text-gray-500 hover:text-[#2D3047] transition flex items-center text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Kembali ke Login
            </a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-2">Lupa Kata Sandi?</h2>
        <p class="text-gray-500 text-sm mb-6">Masukkan email yang terdaftar, kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.</p>

        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-green-600 bg-green-100 p-3 rounded">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-500 mb-2">Alamat Email</label>
                <input type="email" id="email" name="email" required autofocus
                    class="w-full border border-gray-300 px-4 py-2.5 rounded-md text-gray-700 focus:outline-none focus:border-[#2D3047] focus:ring-1 focus:ring-[#2D3047] transition" />
                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="w-full bg-[#2D3047] hover:bg-[#1f2233] text-white font-medium py-3 rounded-md transition duration-200">
                Kirim Tautan Reset
            </button>
        </form>
    </div>

</body>
</html>
