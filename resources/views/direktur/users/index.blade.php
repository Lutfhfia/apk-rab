@extends('layouts.app')
@section('title', 'Kelola User')
@section('page-title', 'Kelola User & Role')
@section('page-subtitle', 'Manajemen akun pengguna sistem')

@section('sidebar-menu')
@include('direktur._sidebar')
@endsection

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <h2 class="text-lg font-bold text-gray-800">Daftar User</h2>
    <a href="{{ route('direktur.users.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-bold flex items-center transition shadow-sm">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        Tambah User
    </a>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
    <p class="text-sm text-emerald-700 font-semibold">{{ session('success') }}</p>
</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
    <p class="text-sm text-red-700 font-semibold">{{ session('error') }}</p>
</div>
@endif

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-6">
    <form method="GET" action="{{ route('direktur.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:flex gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-bold text-gray-500 mb-1">Cari</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-bold text-gray-500 mb-1">Role</label>
            <select name="role" class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                <option value="">Semua Role</option>
                @foreach(\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" {{ request('role') == $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[140px]">
            <label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
            <select name="status" class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none">
                <option value="">Semua</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold transition">Filter</button>
        <a href="{{ route('direktur.users.index') }}" class="text-gray-500 hover:text-gray-700 px-4 py-2 text-sm font-medium">Reset</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs text-gray-500 border-b border-gray-100 bg-gray-50/50">
                    <th class="py-3.5 px-5 font-bold">Nama</th>
                    <th class="py-3.5 px-5 font-bold">Email</th>
                    <th class="py-3.5 px-5 font-bold">Role</th>
                    <th class="py-3.5 px-5 font-bold">Status</th>
                    <th class="py-3.5 px-5 font-bold">Terdaftar</th>
                    <th class="py-3.5 px-5 font-bold">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-600">
                @forelse($users as $user)
                <tr class="border-b border-gray-50 hover:bg-emerald-50/30 transition-colors {{ $user->trashed() || !$user->is_active ? 'opacity-50' : '' }}">
                    <td class="py-4 px-5">
                        <div class="flex items-center">
                            @if($user->avatar)
                            <div class="avatar-clickable">
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover mr-3 border border-gray-200 shadow-sm">
                            </div>
                            @else
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-sm mr-3">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            @endif
                            <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="py-4 px-5">{{ $user->email }}</td>
                    <td class="py-4 px-5">
                        @php
                            $roleColors = [
                                'admin_keuangan' => 'bg-blue-100 text-blue-700',
                                'manajer_operasional' => 'bg-indigo-100 text-indigo-700',
                                'direktur' => 'bg-purple-100 text-purple-700',
                            ];
                        @endphp
                        <span class="{{ $roleColors[$user->role->value] ?? 'bg-gray-100 text-gray-700' }} text-[10px] font-bold px-3 py-1.5 rounded-lg">{{ $user->role->label() }}</span>
                    </td>
                    <td class="py-4 px-5">
                        @if($user->is_active && !$user->trashed())
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-3 py-1.5 rounded-lg">Aktif</span>
                        @else
                        <span class="bg-red-100 text-red-700 text-[10px] font-bold px-3 py-1.5 rounded-lg">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-5">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="py-4 px-5">
                        <div class="flex items-center space-x-1">
                            <a href="{{ route('direktur.users.edit', $user->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('direktur.users.toggle', $user->id) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $user->is_active && !$user->trashed() ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition" title="{{ $user->is_active && !$user->trashed() ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    @if($user->is_active && !$user->trashed())
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('direktur.users.destroy', $user->id) }}" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400">Belum ada data user.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="p-5 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>
@endsection
