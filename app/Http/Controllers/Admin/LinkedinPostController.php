<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\PublishLinkedInPostJob;
use App\Models\LinkedinPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LinkedinPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $posts = $request->user()->linkedinPosts()
            ->with('linkedinAccount')
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status))
            ->when($request->filled('keyword'), function (Builder $query) use ($request): void {
                $query->where(function (Builder $query) use ($request): void {
                    $query->where('title', 'like', '%'.$request->keyword.'%')
                        ->orWhere('content', 'like', '%'.$request->keyword.'%');
                });
            })
            ->when($request->filled('date_from'), fn (Builder $query) => $query->whereDate('scheduled_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn (Builder $query) => $query->whereDate('scheduled_at', '<=', $request->date_to));

        match ($request->input('sort', 'latest')) {
            'oldest' => $posts->oldest(),
            'scheduled_asc' => $posts->orderBy('scheduled_at'),
            'scheduled_desc' => $posts->orderByDesc('scheduled_at'),
            default => $posts->latest(),
        };

        return view('linkedin.posts.index', [
            'posts' => $posts->paginate(15)->withQueryString(),
            'statuses' => LinkedinPost::STATUSES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return view('linkedin.posts.form', [
            'post' => new LinkedinPost([
                'status' => 'draft',
                'scheduled_at' => $request->filled('date') ? $request->date.' 09:00:00' : null,
            ]),
            'accounts' => $request->user()->linkedinAccounts()->where('status', 'active')->get(),
            'statuses' => LinkedinPost::STATUSES,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validatedPostData($request);
        $data['user_id'] = $request->user()->id;
        $data['media_path'] = $this->storeMedia($request);

        $post = LinkedinPost::create($data);

        return redirect()->route('posts.show', $post)->with('status', 'Post saved.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, LinkedinPost $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        return view('linkedin.posts.show', [
            'post' => $post->load(['linkedinAccount', 'logs' => fn ($query) => $query->latest()]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, LinkedinPost $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);
        abort_unless($post->canBeEdited(), 409, 'Posted or cancelled posts cannot be edited.');

        return view('linkedin.posts.form', [
            'post' => $post,
            'accounts' => $request->user()->linkedinAccounts()->where('status', 'active')->get(),
            'statuses' => LinkedinPost::STATUSES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LinkedinPost $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);
        abort_unless($post->canBeEdited(), 409, 'Posted or cancelled posts cannot be edited.');

        $data = $this->validatedPostData($request, $post);

        if ($request->hasFile('media')) {
            $data['media_path'] = $this->storeMedia($request);
        }

        $post->update($data);

        return redirect()->route('posts.show', $post)->with('status', 'Post updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, LinkedinPost $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        if ($post->status === 'pending') {
            $post->update(['status' => 'cancelled']);
        } else {
            $post->delete();
        }

        return redirect()->route('posts.index')->with('status', 'Post removed from schedule.');
    }

    public function calendar(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::parse($request->month)->startOfMonth()
            : now()->startOfMonth();

        return view('linkedin.posts.calendar', [
            'month' => $month,
            'posts' => $request->user()->linkedinPosts()
                ->whereBetween('scheduled_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->orderBy('scheduled_at')
                ->get()
                ->groupBy(fn (LinkedinPost $post) => $post->scheduled_at?->toDateString()),
        ]);
    }

    public function failed(Request $request)
    {
        return view('linkedin.posts.failed', [
            'posts' => $request->user()->linkedinPosts()
                ->where('status', 'failed')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function retry(Request $request, LinkedinPost $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);
        abort_unless($post->canRetry(), 409, 'This post cannot be retried.');

        $post->update([
            'status' => 'pending',
            'scheduled_at' => now(),
            'error_message' => null,
        ]);

        PublishLinkedInPostJob::dispatch($post->id);

        return back()->with('status', 'Retry queued.');
    }

    private function validatedPostData(Request $request, ?LinkedinPost $post = null): array
    {
        $data = $request->validate([
            'linkedin_account_id' => [
                'nullable',
                Rule::exists('linkedin_accounts', 'id')->where('user_id', $request->user()->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:3000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'status' => ['required', Rule::in(['draft', 'pending'])],
            'media' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($data['status'] === 'pending') {
            validator($data, [
                'linkedin_account_id' => ['required'],
                'scheduled_at' => ['required'],
            ])->validate();

            $data['scheduled_at'] = Carbon::parse($data['scheduled_at'], $request->user()->timezone)->utc();

            $duplicate = LinkedinPost::query()
                ->where('user_id', $request->user()->id)
                ->where('status', 'pending')
                ->where('scheduled_at', $data['scheduled_at'])
                ->when($post, fn (Builder $query) => $query->whereKeyNot($post->id))
                ->exists();

            if ($duplicate) {
                back()->withErrors(['scheduled_at' => 'You already have a pending post scheduled at this time.'])->throwResponse();
            }
        } else {
            $data['scheduled_at'] = null;
        }

        return $data;
    }

    private function storeMedia(Request $request): ?string
    {
        if (! $request->hasFile('media')) {
            return null;
        }

        return $request->file('media')->store('linkedin-media', 'public');
    }
}
