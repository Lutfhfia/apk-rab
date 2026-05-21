@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', $user->name)

@section('sidebar-menu')
@include('direktur._sidebar')
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Data User
            </h3>
            @if($user->is_active && !$user->trashed())
            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-lg">Aktif</span>
            @else
            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1.5 rounded-lg">Nonaktif</span>
            @endif
        </div>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5">
            <ul class="text-sm text-red-600 list-disc list-inside">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('direktur.users.update', $user->id) }}">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Role *</label>
                    <select name="role" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            @if($role !== \App\Enums\UserRole::DIREKTUR || $user->role === \App\Enums\UserRole::DIREKTUR)
                            <option value="{{ $role->value }}" {{ old('role', $user->role->value) == $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1.5">Status *</label>
                    <select name="is_active" required class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                        <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-100">
                    <p class="text-xs font-bold text-amber-600 mb-3">Ganti Password (kosongkan jika tidak ingin mengubah)</p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1.5">Password Baru</label>
                            <input type="password" name="password" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none" placeholder="Minimal 6 karakter">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6 pt-5 border-t border-gray-100">
                <a href="{{ route('direktur.users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl text-sm font-bold transition">Batal</a>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
