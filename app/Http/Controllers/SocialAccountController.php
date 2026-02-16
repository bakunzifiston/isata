<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialAccountController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $accounts = $organization->socialAccounts()->orderBy('platform')->get();

        return view('social.accounts', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $validated = $request->validate([
            'platform' => ['required', 'in:facebook,linkedin,twitter,whatsapp'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $organization->socialAccounts()->create([
            'platform' => $validated['platform'],
            'name' => $validated['name'] ?: ucfirst($validated['platform']),
        ]);

        return redirect()->route('social.accounts')->with('status', 'Account added. Configure API keys in .env for live posting.');
    }

    public function destroy(SocialAccount $account): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $account->organization_id !== $organization->id) {
            abort(403);
        }

        $account->delete();

        return redirect()->route('social.accounts')->with('status', 'Account removed.');
    }
}
