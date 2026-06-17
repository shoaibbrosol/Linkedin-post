@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<section class="card">
    <h1>Reset Password</h1>
    <p class="muted">Enter your email to receive a reset link.</p>
    <form method="post" action="{{ route('password.email') }}" class="grid">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <button class="btn primary">Send Reset Link</button>
    </form>
    <p><a href="{{ route('login') }}">Back to login</a></p>
</section>
@endsection
