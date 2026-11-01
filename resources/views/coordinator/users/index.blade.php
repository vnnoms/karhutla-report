@extends('layouts.dashboard')

@section('title', 'Manajemen pengguna')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('dashboard.coordinator') }}">← Dashboard coordinator</a>
            <span class="eyebrow"><span class="eyebrow-dot"></span>Administrasi</span>
            <h1>Manajemen pengguna</h1>
            <p>Lihat akun dan atur peran reporter atau volunteer.</p>
        </div>
    </div>
    @if (session('status')) <div class="form-status" role="status">{{ session('status') }}</div> @endif
    <div class="user-list" aria-label="Daftar pengguna">
        @forelse ($users as $user)
            <a class="user-list-row" href="{{ route('coordinator.users.show', $user) }}">
                <span class="user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <span class="user-list-main"><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></span>
                <span class="role-badge role-{{ $user->role }}">{{ $user->role }}</span>
                <span class="report-arrow" aria-hidden="true">→</span>
            </a>
        @empty
            <div class="empty-state"><h2>Belum ada pengguna</h2></div>
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $users->links() }}</div>
@endsection
