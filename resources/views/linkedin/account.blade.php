@extends('layouts.app')

@section('title', 'LinkedIn Account')
@section('heading', 'LinkedIn Account')

@section('content')
<section class="card">
    <h2>Connection</h2>
    <p>Status: <span class="badge {{ $account?->status === 'active' ? 'posted' : 'cancelled' }}">{{ $account?->status ?? 'not connected' }}</span></p>
    @if ($oauthReady)
        <p><a class="btn primary" href="{{ route('linkedin.redirect') }}">Connect with LinkedIn</a></p>
    @else
        <p class="muted">Add LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET to .env to enable OAuth.</p>
    @endif
</section>

<section class="card" style="margin-top:16px">
    <h2>Manual Token Settings</h2>
    <form method="post" action="{{ route('linkedin.account.update') }}" class="form-grid">
        @csrf
        @method('put')
        <div>
            <label>LinkedIn User ID</label>
            <input name="linkedin_user_id" value="{{ old('linkedin_user_id', $account?->linkedin_user_id) }}">
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="active" @selected(old('status', $account?->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $account?->status) === 'inactive')>Inactive</option>
            </select>
        </div>
        <div>
            <label>Name</label>
            <input name="name" value="{{ old('name', $account?->name) }}">
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $account?->email) }}">
        </div>
        <div class="full">
            <label>Access Token</label>
            <textarea name="access_token" required>{{ old('access_token', $account?->access_token) }}</textarea>
        </div>
        <div class="full">
            <label>Refresh Token</label>
            <textarea name="refresh_token">{{ old('refresh_token', $account?->refresh_token) }}</textarea>
        </div>
        <div>
            <label>Token Expires At</label>
            <input type="datetime-local" name="token_expires_at" value="{{ old('token_expires_at', $account?->token_expires_at?->format('Y-m-d\TH:i')) }}">
        </div>
        <div class="full">
            <button class="btn primary">Save Settings</button>
        </div>
    </form>
    @if ($account)
        <form method="post" action="{{ route('linkedin.account.disconnect', $account) }}" style="margin-top:12px">
            @csrf
            <button class="btn danger">Disconnect</button>
        </form>
    @endif
</section>
@endsection
