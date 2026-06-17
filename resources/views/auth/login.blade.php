@extends('layouts.app')

@section('title', 'Login')

@section('content')
<section class="card">
    <h1>LinkedIn Daily Posts</h1>
    <p class="muted">Sign in to manage scheduled LinkedIn publishing.</p>
    <form method="post" action="{{ route('login.store') }}" class="grid">
        @csrf
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <label><input type="checkbox" name="remember" style="width:auto"> Remember me</label>
        <button class="btn primary">Login</button>
    </form>
    <p><a href="{{ route('register') }}">Create account</a> · <a href="{{ route('password.request') }}">Forgot password</a></p>
</section>
@endsection
