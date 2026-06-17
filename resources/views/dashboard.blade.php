@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid stats">
    @foreach ($counts as $label => $count)
        <section class="card">
            <div class="muted">{{ ucfirst($label) }} posts</div>
            <div class="stat">{{ $count }}</div>
        </section>
    @endforeach
</div>

<div class="grid two" style="margin-top:16px">
    <section class="card">
        <h2>Today</h2>
        @forelse ($todayPosts as $post)
            <p><span class="badge {{ $post->status }}">{{ $post->status }}</span> {{ $post->scheduled_at?->timezone(auth()->user()->timezone)->format('H:i') }} · <a href="{{ route('posts.show', $post) }}">{{ $post->title ?: 'Untitled post' }}</a></p>
        @empty
            <p class="muted">No posts scheduled today.</p>
        @endforelse
    </section>
    <section class="card">
        <h2>Upcoming</h2>
        @forelse ($upcomingPosts as $post)
            <p>{{ $post->scheduled_at?->timezone(auth()->user()->timezone)->format('M d, H:i') }} · <a href="{{ route('posts.show', $post) }}">{{ $post->title ?: str($post->content)->limit(50) }}</a></p>
        @empty
            <p class="muted">No upcoming posts yet.</p>
        @endforelse
    </section>
</div>

<section class="card" style="margin-top:16px">
    <h2>Recent Activity</h2>
    @forelse ($recentLogs as $log)
        <p><span class="badge {{ $log->status }}">{{ $log->status }}</span> {{ $log->message }} <span class="muted">{{ $log->created_at->diffForHumans() }}</span></p>
    @empty
        <p class="muted">Publishing activity will appear here.</p>
    @endforelse
</section>
@endsection
