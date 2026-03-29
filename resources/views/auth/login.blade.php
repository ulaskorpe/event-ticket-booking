@extends('layouts.app')

@section('title', __('user.login.page_title') . ' — ' . config('app.name'))

@section('content')
<div class="flex-grow-1 d-flex align-items-center justify-content-center p-4">
    <div class="card shadow-sm w-100" style="max-width: 28rem;">
        <div class="card-body p-4">
            <h1 class="h4 mb-2">{{ __('user.login.title') }}</h1>
            <p class="text-muted small mb-4">{{ __('user.login.lead') }}</p>

            <form method="post" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('user.login.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="username"
                        class="form-control @error('email') is-invalid @enderror">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('user.login.password') }}</label>
                    <input type="password" name="password" id="password" required autocomplete="current-password"
                        class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-4 form-check">
                    <input type="checkbox" name="remember" value="1" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('user.login.remember') }}</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">{{ __('user.login.submit') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
