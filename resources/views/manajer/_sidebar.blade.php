{{-- Manajer Sidebar --}}
<li>
    <a href="{{ route('manajer.dashboard') }}" class="sidebar-link {{ request()->routeIs('manajer.dashboard') ? 'active' : '' }}">
        <svg class="w-5 h-5 mr-3 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
        <span class="sidebar-text">Dashboard</span>
    </a>
</li>
<li>
    <a href="{{ route('manajer.rab.index') }}" class="sidebar-link {{ request()->routeIs('manajer.rab.*') ? 'active' : '' }}">
        <svg class="w-5 h-5 mr-3 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span class="sidebar-text">Daftar RAB</span>
    </a>
</li>
<li>
    <a href="{{ route('manajer.cash-flow.index') }}" class="sidebar-link {{ request()->routeIs('manajer.cash-flow.*') ? 'active' : '' }}">
        <svg class="w-5 h-5 mr-3 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="sidebar-text">Arus Kas</span>
    </a>
</li>
<li>
    <a href="{{ route('report.index') }}" class="sidebar-link {{ request()->routeIs('report.*') ? 'active' : '' }}">
        <svg class="w-5 h-5 mr-3 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span class="sidebar-text">Laporan Arus Kas</span>
    </a>
</li>
