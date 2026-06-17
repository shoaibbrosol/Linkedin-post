@extends('layouts.app')

@section('title', 'Failed Posts')
@section('heading', 'Failed Posts')

@section('content')
<table class="table">
    <thead><tr><th>Post</th><th>Error</th><th>Retries</th><th></th></tr></thead>
    <tbody>
        @forelse ($posts as $post)
            <tr>
                <td><a href="{{ route('posts.show', $post) }}">{{ $post->title ?: 'Untitled post' }}</a></td>
                <td>{{ str($post->error_message)->limit(130) }}</td>
                <td>{{ $post->retry_count }} / {{ config('services.linkedin.max_retries') }}</td>
                <td>@if ($post->canRetry())<form method="post" action="{{ route('posts.retry', $post) }}">@csrf <button class="btn primary">Retry</button></form>@endif</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No failed posts.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $posts->links() }}
@endsection
