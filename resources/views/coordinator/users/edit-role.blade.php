@extends('layouts.dashboard')

@section('title', 'Ubah role pengguna')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('coordinator.users.show', $user) }}">← Detail pengguna</a>
            <span class="eyebrow"><span class="eyebrow-dot"></span>Administrasi</span>
            <h1>Ubah role</h1>
            <p>Pilih akses yang sesuai untuk {{ $user->name }}.</p>
        </div>
    </div>
    <form class="report-form" method="POST" action="{{ route('coordinator.users.role.update', $user) }}">
        @csrf
        @method('PUT')
        @if ($errors->any()) <div class="form-alert" role="alert"><p>Periksa kembali pilihan role.</p></div> @endif
        <section class="form-section">
            <div class="field">
                <label for="role">Role pengguna</label>
                <select id="role" name="role" required>
                    <option value="reporter" @selected(old('role', $user->role) === 'reporter')>Reporter</option>
                    <option value="volunteer" @selected(old('role', $user->role) === 'volunteer')>Volunteer</option>
                </select>
                @error('role') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="notice-card">Role coordinator dilindungi dan tidak dapat diberikan melalui halaman ini.</div>
        </section>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ route('coordinator.users.show', $user) }}">Batal</a>
            <button class="button" type="submit">Simpan role</button>
        </div>
    </form>
@endsection
