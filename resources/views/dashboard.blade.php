@extends('layouts.app')

@section('title', __('user.dashboard.page_title') . ' — ' . config('app.name'))

@section('content')
<div class="flex-grow-1 p-4 p-lg-5">
    <header class="container py-3 mb-4 border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1">{{ __('user.dashboard.title') }}</h1>
                <p class="text-muted small mb-0">
                    {{ __('user.dashboard.welcome', ['name' => auth()->user()->name]) }}
                    <span class="opacity-75">{{ __('user.dashboard.role_label', ['role' => auth()->user()->role->value]) }}</span>
                </p>
            </div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">{{ __('user.dashboard.logout') }}</button>
            </form>
        </div>
    </header>
    <main class="container">
        <div class="card shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-0">{{ __('user.dashboard.placeholder') }}</p>
            </div>
        </div>
    </main>
</div>
@endsection
