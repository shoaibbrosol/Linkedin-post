@extends('layouts.app')

@section('title', 'Logs')
@section('heading', 'Posting Logs')

@section('content')
@foreach ($logs as $post)
    <section class="card" style="margin-bottom:12px">
        <h2><a href="{{ route('posts.show', $post) }}">{{ $post->title ?: 'Untitled post' }}</a></h2>
        @forelse ($post->logs as $log)
            <p><span class="badge {{ $log->status }}">{{ $log->status }}</span> {{ $log->message }} <span class="muted">{{ $log->created_at->format('M d, Y H:i') }}</span></p>
        @empty
            <p class="muted">No logs recorded.</p>
        @endforelse
    </section>
@endforeach
{{ $logs->links() }}
@endsection
