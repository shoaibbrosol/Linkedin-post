@extends('layouts.app')

@section('title', 'Calendar')
@section('heading', 'Calendar')

@section('content')
<div class="topbar">
    <a class="btn" href="{{ route('posts.calendar', ['month' => $month->copy()->subMonth()->toDateString()]) }}">Previous</a>
    <h2>{{ $month->format('F Y') }}</h2>
    <a class="btn" href="{{ route('posts.calendar', ['month' => $month->copy()->addMonth()->toDateString()]) }}">Next</a>
</div>

<div class="calendar">
    @for ($day = $month->copy()->startOfMonth(); $day <= $month->copy()->endOfMonth(); $day->addDay())
        <div class="day">
            <strong><a href="{{ route('posts.create', ['date' => $day->toDateString()]) }}">{{ $day->format('d') }}</a></strong>
            @foreach ($posts->get($day->toDateString(), collect()) as $post)
                <p><span class="badge {{ $post->status }}">{{ $post->status }}</span><br><a href="{{ route('posts.show', $post) }}">{{ str($post->title ?: $post->content)->limit(28) }}</a></p>
            @endforeach
        </div>
    @endfor
</div>
@endsection
