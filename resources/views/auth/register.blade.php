@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
    <div class="auth-heading">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Bergabung bersama kami</span>
        <h1>Buat akun reporter</h1>
        <p>Daftarkan diri untuk mulai menyampaikan laporan kondisi hutan.</p>
    </div>

    @if ($errors->any())
        <div class="form-alert" role="alert">
            <p>Periksa kembali data yang Anda masukkan.</p>
            <ul>
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="auth-form" method="POST" action="{{ route('register.store') }}">
        @csrf
        <div class="field">
            <label for="name">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" placeholder="Nama Anda" maxlength="100" required autofocus>
            @error('name')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="nama@email.com" maxlength="100" required>
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="field">
            <label for="phone_number">Nomor telepon</label>
            <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" placeholder="08xxxxxxxxxx" maxlength="20" required>
            @error('phone_number')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="field">
            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Ulangi kata sandi" required>
        </div>
        <button class="button form-button" type="submit">Buat akun reporter <span aria-hidden="true">→</span></button>
    </form>

    <p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection
