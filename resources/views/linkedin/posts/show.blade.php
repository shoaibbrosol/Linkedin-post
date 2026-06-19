@extends('layouts.app')

@section('title', 'Post Detail')
@section('heading', 'Post Detail')

@section('content')
<section class="card">
    <p><span class="badge {{ $post->status }}">{{ $post->status }}</span></p>
    <h2>{{ $post->title ?: 'Untitled post' }}</h2>
    <p class="muted">Scheduled: {{ $post->scheduled_at?->timezone(auth()->user()->timezone)->format('M d, Y H:i') ?: '-' }} · Posted: {{ $post->posted_at?->timezone(auth()->user()->timezone)->format('M d, Y H:i') ?: '-' }}</p>
    <p style="white-space:pre-wrap">{{ $post->content }}</p>
    @if ($post->media_path)<p><img src="{{ asset('storage/'.$post->media_path) }}" alt="" style="max-width:360px;width:100%;border-radius:8px"></p>@endif
    @if ($post->linkedin_post_id)<p>LinkedIn Post ID: <code>{{ $post->linkedin_post_id }}</code></p>@endif
    @if ($post->error_message)
        <div class="errors alert">
            {{ $post->error_message }}
            @if (str_contains($post->error_message, 'LinkedIn user ID'))
                <p><a class="btn" href="{{ route('linkedin.account.edit') }}">Add LinkedIn User ID</a></p>
            @endif
        </div>
    @endif
    <p>
        @if ($post->canBeEdited())<a class="btn" href="{{ route('posts.edit', $post) }}">Edit</a>@endif
        @if ($post->canRetry())<form method="post" action="{{ route('posts.retry', $post) }}" style="display:inline">@csrf <button class="btn primary">Retry</button></form>@endif
        <form method="post" action="{{ route('posts.destroy', $post) }}" style="display:inline">@csrf @method('delete') <button class="btn danger">Delete / Cancel</button></form>
    </p>
</section>

<section class="card" style="margin-top:16px">
    <h2>Logs</h2>
    @forelse ($post->logs as $log)
        <p><span class="badge {{ $log->status }}">{{ $log->status }}</span> {{ $log->message }} <span class="muted">{{ $log->created_at->format('M d, Y H:i') }}</span></p>
    @empty
        <p class="muted">No logs for this post yet.</p>
    @endforelse
</section>
@endsection
