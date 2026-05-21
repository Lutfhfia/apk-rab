@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')
@section('page-subtitle', 'Buat akun pengguna baru')

@section('sidebar-menu')
@include('direktur._sidebar')
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-5 flex items-center">
            <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Form Tambah User
        </h3>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
            <ul class="text-sm text-red-600 list-disc list-inside">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('direktur.users.store') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Masukkan nama lengkap">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="email@contoh.com">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Role *</label>
                    <select name="role" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        <option value="">-- Pilih Role --</option>
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            @if($role !== \App\Enums\UserRole::DIREKTUR)
                            <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Password *</label>
                    <input type="password" name="password" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Minimal 6 karakter">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Konfirmasi Password *</label>
                    <input type="password" name="password_confirmation" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Ulangi password">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6 pt-5 border-t border-gray-100">
                <a href="{{ route('direktur.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold transition">Batal</a>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
