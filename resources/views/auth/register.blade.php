@extends('layouts.app')

@section('title', 'Register')

@section('content')
<section class="card">
    <h1>Create Account</h1>
    <p class="muted">Set your timezone so scheduled posts publish at the right local time.</p>
    <form method="post" action="{{ route('register.store') }}" class="grid">
        @csrf
        <div>
            <label>Name</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div>
            <label>Timezone</label>
            <select name="timezone" required>
                @foreach (timezone_identifiers_list() as $timezone)
                    <option value="{{ $timezone }}" @selected(old('timezone', 'UTC') === $timezone)>{{ $timezone }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>
        <button class="btn primary">Register</button>
    </form>
    <p><a href="{{ route('login') }}">Back to login</a></p>
</section>
@endsection
