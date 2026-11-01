@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="auth-heading">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Selamat datang kembali</span>
        <h1>Masuk ke akun</h1>
        <p>Gunakan email dan kata sandi Anda untuk melanjutkan.</p>
    </div>

    @if (session('status'))
        <p class="form-status" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->has('email'))
        <p class="form-alert" role="alert">{{ $errors->first('email') }}</p>
    @endif

    <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="nama@email.com" required autofocus>
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="field">
            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <button class="button form-button" type="submit">Masuk ke dashboard <span aria-hidden="true">→</span></button>
    </form>

    <p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai reporter</a></p>
@endsection
