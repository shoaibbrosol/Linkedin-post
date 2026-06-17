@if (session('status'))
    <div class="alert">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert errors">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
