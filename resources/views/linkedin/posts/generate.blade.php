@extends('layouts.app')

@section('title', 'Generate Posts')
@section('heading', 'Generate Posts')

@section('content')
@unless ($openAiReady)
    <div class="alert errors">OpenAI is not configured. Add OPENAI_API_KEY to .env before generating posts.</div>
@endunless

<section class="card">
    <form method="post" action="{{ route('posts.generate.store') }}" enctype="multipart/form-data" class="form-grid">
        @csrf
        <div>
            <label>LinkedIn Account</label>
            <select name="linkedin_account_id" required>
                <option value="">Select account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}" @selected((int) old('linkedin_account_id') === $account->id)>{{ $account->name ?: $account->linkedin_user_id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Tone</label>
            <input name="tone" value="{{ old('tone', 'professional and practical') }}" placeholder="professional and practical">
        </div>
        <div>
            <label>First Scheduled At</label>
            <input type="datetime-local" name="scheduled_start" value="{{ old('scheduled_start', now(auth()->user()->timezone)->addHour()->format('Y-m-d\TH:i')) }}" required>
        </div>
        <div>
            <label>Interval Minutes</label>
            <input type="number" name="interval_minutes" min="5" max="10080" value="{{ old('interval_minutes', 1440) }}" required>
        </div>
        <div class="full">
            <label>Topics</label>
            <textarea name="topics" placeholder="One topic per line">{{ old('topics') }}</textarea>
            <p class="muted">Paste one topic per line. Each topic becomes one generated pending LinkedIn post.</p>
        </div>
        <div class="full">
            <label>Topic File</label>
            <input type="file" name="topics_file" accept=".txt,.csv">
            <p class="muted">Optional .txt or .csv file. For CSV files, the first non-empty column in each row is used as the topic.</p>
        </div>
        <div class="full">
            <button class="btn primary" @disabled(! $openAiReady)>Generate Pending Posts</button>
            <a class="btn" href="{{ route('posts.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection
