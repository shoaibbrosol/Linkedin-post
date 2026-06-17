@extends('layouts.app')

@section('title', 'Posts')
@section('heading', 'Posts')

@section('content')
<form method="get" class="filters">
    <div><label>Status</label><select name="status"><option value="">All</option>@foreach ($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
    <div><label>Keyword</label><input name="keyword" value="{{ request('keyword') }}"></div>
    <div><label>From</label><input type="date" name="date_from" value="{{ request('date_from') }}"></div>
    <div><label>To</label><input type="date" name="date_to" value="{{ request('date_to') }}"></div>
    <div><label>Sort</label><select name="sort"><option value="latest">Latest</option><option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option><option value="scheduled_asc" @selected(request('sort') === 'scheduled_asc')>Scheduled asc</option><option value="scheduled_desc" @selected(request('sort') === 'scheduled_desc')>Scheduled desc</option></select></div>
    <button class="btn">Filter</button>
</form>

<p>
    <a class="btn primary" href="{{ route('posts.create') }}">Create Post</a>
    <a class="btn" href="{{ route('posts.generate') }}">Generate From Topics</a>
</p>

<table class="table">
    <thead><tr><th>Post</th><th>Status</th><th>Scheduled</th><th>Posted</th><th></th></tr></thead>
    <tbody>
        @forelse ($posts as $post)
            <tr>
                <td><strong>{{ $post->title ?: 'Untitled post' }}</strong><br><span class="muted">{{ str($post->content)->limit(110) }}</span></td>
                <td><span class="badge {{ $post->status }}">{{ $post->status }}</span></td>
                <td>{{ $post->scheduled_at?->timezone(auth()->user()->timezone)->format('M d, Y H:i') ?: '-' }}</td>
                <td>{{ $post->posted_at?->timezone(auth()->user()->timezone)->format('M d, Y H:i') ?: '-' }}</td>
                <td><a href="{{ route('posts.show', $post) }}">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">No posts found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $posts->links() }}
@endsection
