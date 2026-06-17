<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkedinAccount;
use App\Services\LinkedInService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class LinkedinAccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('linkedin.account', [
            'account' => $request->user()->linkedinAccounts()->latest()->first(),
            'oauthReady' => filled(config('services.linkedin.client_id')) && filled(config('services.linkedin.client_secret')),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'linkedin_user_id' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'access_token' => ['required', 'string'],
            'refresh_token' => ['nullable', 'string'],
            'token_expires_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $request->user()->linkedinAccounts()->updateOrCreate(
            ['id' => $request->user()->linkedinAccounts()->latest()->value('id')],
            $data
        );

        return back()->with('status', 'LinkedIn account settings saved.');
    }

    public function redirect(Request $request, LinkedInService $linkedIn)
    {
        abort_unless(filled(config('services.linkedin.client_id')), 409, 'LinkedIn OAuth credentials are not configured.');

        $state = Str::random(40);
        $request->session()->put('linkedin_oauth_state', $state);

        return redirect()->away($linkedIn->authorizationUrl($state));
    }

    public function callback(Request $request, LinkedInService $linkedIn)
    {
        if ($request->filled('error')) {
            $message = html_entity_decode(
                $request->input('error_description', $request->input('error', 'LinkedIn authorization was cancelled.')),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );

            return redirect()->route('linkedin.account.edit')->with('error', 'LinkedIn connection failed: '.$message);
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $expectedState = (string) $request->session()->pull('linkedin_oauth_state');

        if (! hash_equals($expectedState, $request->state)) {
            return redirect()->route('linkedin.account.edit')->with('error', 'LinkedIn connection expired. Please try connecting again.');
        }

        try {
            $token = $linkedIn->exchangeCodeForToken($request->code);
            $profile = $linkedIn->fetchProfile($token['access_token']);
        } catch (RuntimeException $exception) {
            return redirect()->route('linkedin.account.edit')->with('error', 'LinkedIn connection failed: '.$exception->getMessage());
        }

        $request->user()->linkedinAccounts()->updateOrCreate(
            ['linkedin_user_id' => $profile['sub'] ?? $profile['id'] ?? null],
            [
                'name' => $profile['name'] ?? trim(($profile['given_name'] ?? '').' '.($profile['family_name'] ?? '')),
                'email' => $profile['email'] ?? null,
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
                'status' => 'active',
            ]
        );

        return redirect()->route('linkedin.account.edit')->with('status', 'LinkedIn account connected.');
    }

    public function disconnect(Request $request, LinkedinAccount $account)
    {
        abort_unless($account->user_id === $request->user()->id, 403);

        $account->update(['status' => 'inactive']);

        return back()->with('status', 'LinkedIn account disconnected.');
    }
}
