@extends('layouts.dashboard')

@section('title', 'Detail pengguna')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('coordinator.users.index') }}">← Semua pengguna</a>
            <span class="eyebrow"><span class="eyebrow-dot"></span>Profil akun</span>
            <h1>{{ $user->name }}</h1>
            <p>Informasi akun dan peran pengguna.</p>
        </div>
    </div>
    @if (session('status')) <div class="form-status" role="status">{{ session('status') }}</div> @endif
    <section class="detail-card user-detail-card">
        <div class="detail-section"><span class="detail-label">Nama</span><p>{{ $user->name }}</p></div>
        <div class="detail-section"><span class="detail-label">Email</span><p>{{ $user->email }}</p></div>
        <div class="detail-section"><span class="detail-label">Nomor telepon</span><p>{{ $user->phone_number ?: 'Tidak tersedia' }}</p></div>
        <div class="detail-section"><span class="detail-label">Role</span><p><span class="role-badge role-{{ $user->role }}">{{ $user->role }}</span></p></div>
        @if ($canUpdateRole)
            <div class="detail-section user-role-action"><a class="button" href="{{ route('coordinator.users.role.edit', $user) }}">Ubah role</a></div>
        @elseif ($user->is(auth()->user()))
            <div class="notice-card">Role akun Anda tidak dapat diubah melalui manajemen pengguna.</div>
        @elseif ($user->role === 'coordinator')
            <div class="notice-card">Akun coordinator dilindungi dan role-nya tidak dapat diubah.</div>
        @endif
    </section>
@endsection
