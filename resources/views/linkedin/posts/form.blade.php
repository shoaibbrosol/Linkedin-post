@extends('layouts.app')

@section('title', $post->exists ? 'Edit Post' : 'Create Post')
@section('heading', $post->exists ? 'Edit Post' : 'Create Post')

@section('content')
<section class="card">
    <form method="post" action="{{ $post->exists ? route('posts.update', $post) : route('posts.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        @if ($post->exists) @method('put') @endif
        <div>
            <label>LinkedIn Account</label>
            <select name="linkedin_account_id">
                <option value="">Select account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected((int) old('linkedin_account_id', $post->linkedin_account_id) === $account->id)>{{ $account->name ?: $account->linkedin_user_id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="draft" @selected(old('status', $post->status) === 'draft')>Draft</option>
                <option value="pending" @selected(old('status', $post->status) === 'pending')>Pending</option>
            </select>
        </div>
        <div class="full">
            <label>Title / Internal Note</label>
            <input name="title" value="{{ old('title', $post->title) }}">
        </div>
        <div class="full">
            <label>Content</label>
            <textarea name="content" maxlength="3000" required>{{ old('content', $post->content) }}</textarea>
            <p class="muted">LinkedIn text posts are validated up to 3,000 characters here.</p>
        </div>
        <div>
            <label>Scheduled At</label>
            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $post->scheduled_at?->timezone(auth()->user()->timezone)->format('Y-m-d\TH:i')) }}">
        </div>
        <div>
            <label>Media</label>
            <input type="file" name="media" accept=".jpg,.jpeg,.png,.webp">
            @if ($post->media_path)<p><a href="{{ asset('storage/'.$post->media_path) }}" target="_blank">View current image</a></p>@endif
        </div>
        <div class="full">
            <h2>Preview</h2>
            <div class="card" style="box-shadow:none">
                <strong>{{ old('title', $post->title) ?: 'LinkedIn post' }}</strong>
                <p style="white-space:pre-wrap">{{ old('content', $post->content) ?: 'Your post content preview appears here after saving.' }}</p>
            </div>
        </div>
        <div class="full">
            <button class="btn primary">Save Post</button>
            <a class="btn" href="{{ route('posts.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
