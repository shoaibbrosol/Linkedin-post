<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedinPost;
use App\Services\OpenAIService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use SplFileObject;
use Throwable;

class GeneratedLinkedinPostController extends Controller
{
    public function create(Request $request)
    {
        return view('linkedin.posts.generate', [
            'accounts' => $request->user()->linkedinAccounts()->where('status', 'active')->get(),
            'openAiReady' => filled(config('services.openai.api_key')),
        ]);
    }

    public function store(Request $request, OpenAIService $openAI)
    {
        $data = $request->validate([
            'linkedin_account_id' => [
                'required',
                Rule::exists('linkedin_accounts', 'id')->where('user_id', $request->user()->id),
            ],
            'topics' => ['nullable', 'string', 'max:20000'],
            'topics_file' => ['nullable', 'file', 'mimes:txt,csv', 'max:1024'],
            'scheduled_start' => ['required', 'date', 'after:now'],
            'interval_minutes' => ['required', 'integer', 'min:5', 'max:10080'],
            'tone' => ['nullable', 'string', 'max:255'],
        ]);

        $topics = $this->topicsFromRequest($request);

        if ($topics === []) {
            return back()->withErrors(['topics' => 'Add at least one topic or upload a topic file.'])->withInput();
        }

        $start = Carbon::parse($data['scheduled_start'], $request->user()->timezone)->utc();
        $scheduledTimes = collect($topics)
            ->keys()
            ->map(fn (int $index) => $start->copy()->addMinutes($index * (int) $data['interval_minutes']));

        $duplicate = LinkedinPost::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->whereIn('scheduled_at', $scheduledTimes)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['scheduled_start' => 'One or more generated posts would overlap an existing pending post.'])->withInput();
        }

        $created = 0;
        $errors = [];

        foreach ($topics as $index => $topic) {
            try {
                $content = $openAI->generateLinkedInPost($topic, $data['tone'] ?? null);

                $request->user()->linkedinPosts()->create([
                    'linkedin_account_id' => $data['linkedin_account_id'],
                    'title' => $topic,
                    'content' => $content,
                    'scheduled_at' => $start->copy()->addMinutes($index * (int) $data['interval_minutes']),
                    'status' => 'pending',
                ]);

                $created++;
            } catch (Throwable $exception) {
                $errors[] = $topic.': '.$exception->getMessage();
            }
        }

        if ($created === 0) {
            return back()->withErrors($errors ?: ['topics' => 'No posts were generated.'])->withInput();
        }

        $message = "Generated {$created} pending LinkedIn post(s).";

        if ($errors !== []) {
            $message .= ' Some topics failed: '.implode(' | ', array_slice($errors, 0, 3));
        }

        return redirect()->route('posts.index', ['status' => 'pending'])->with('status', $message);
    }

    private function topicsFromRequest(Request $request): array
    {
        $topics = $this->splitTopics((string) $request->input('topics', ''));

        if ($request->hasFile('topics_file')) {
            $topics = array_merge($topics, $this->topicsFromFile($request->file('topics_file')->getRealPath()));
        }

        return array_values(array_unique(array_filter($topics)));
    }

    private function splitTopics(string $content): array
    {
        return collect(preg_split('/\R/', $content) ?: [])
            ->map(fn (string $topic) => trim($topic))
            ->filter()
            ->values()
            ->all();
    }

    private function topicsFromFile(string $path): array
    {
        $file = new SplFileObject($path);
        $topics = [];

        while (! $file->eof()) {
            $row = $file->fgetcsv();

            if ($row === false || $row === [null]) {
                continue;
            }

            foreach ($row as $value) {
                $topic = trim((string) $value);

                if ($topic !== '') {
                    $topics[] = $topic;
                    break;
                }
            }
        }

        return $topics;
    }
}
