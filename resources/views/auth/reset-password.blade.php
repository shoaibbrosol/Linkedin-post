@extends('layouts.app')

@section('title', 'Choose New Password')

@section('content')
<section class="card">
    <h1>Choose New Password</h1>
    <form method="post" action="{{ route('password.update') }}" class="grid">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button class="btn primary">Update Password</button>
    </form>
</section>
@endsection
