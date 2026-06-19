<?php

namespace App\Services;

use App\Models\LinkedinAccount;
use App\Models\LinkedinPost;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LinkedInService
{
    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.linkedin.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
            'scope' => config('services.linkedin.scopes'),
        ]);

        return 'https://www.linkedin.com/oauth/v2/authorization?'.$query;
    }

    public function exchangeCodeForToken(string $code): array
    {
        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri(),
            'client_id' => config('services.linkedin.client_id'),
            'client_secret' => config('services.linkedin.client_secret'),
        ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('error_description') ?? 'LinkedIn token exchange failed.');
        }

        return $response->json();
    }

    public function fetchProfile(string $accessToken): array
    {
        $profile = $this->client($accessToken)->get('/v2/userinfo');

        if ($profile->failed()) {
            throw new RuntimeException('Unable to fetch LinkedIn profile.');
        }

        return $profile->json();
    }

    public function profileFromIdToken(?string $idToken): array
    {
        if (blank($idToken)) {
            return [];
        }

        $parts = explode('.', $idToken);

        if (count($parts) < 2) {
            return [];
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return [];
        }

        $profile = json_decode($decoded, true);

        return is_array($profile) ? $profile : [];
    }

    public function usesOpenIdConnect(): bool
    {
        return in_array('openid', preg_split('/\s+/', trim((string) config('services.linkedin.scopes'))), true);
    }

    public function publish(LinkedinPost $post): array
    {
        $account = $post->linkedinAccount;

        if (! $account instanceof LinkedinAccount || ! $account->isActive()) {
            throw new RuntimeException('Missing or inactive LinkedIn account.');
        }

        if (blank($account->linkedin_user_id)) {
            throw new RuntimeException('LinkedIn user ID is required before publishing.');
        }

        if ($post->linkedin_post_id) {
            return [
                'id' => $post->linkedin_post_id,
                'message' => 'Post was already published.',
                'duplicate_guard' => true,
            ];
        }

        $payload = [
            'author' => 'urn:li:person:'.$account->linkedin_user_id,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $post->content],
                    'shareMediaCategory' => 'NONE',
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ];

        $response = $this->client($account->access_token)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->post('/v2/ugcPosts', $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->body() ?: 'LinkedIn publishing failed.');
        }

        return [
            'id' => $response->header('x-restli-id') ?? $response->json('id'),
            'payload' => $payload,
            'response' => $response->json() ?: ['status' => $response->status()],
        ];
    }

    private function client(string $accessToken): PendingRequest
    {
        return Http::baseUrl('https://api.linkedin.com')
            ->acceptJson()
            ->withToken($accessToken);
    }

    private function redirectUri(): string
    {
        return config('services.linkedin.redirect_uri') ?: route('linkedin.callback');
    }
}
