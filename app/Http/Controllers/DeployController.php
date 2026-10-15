<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\MaintenanceTasks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class DeployController extends Controller
{
    public function show(string $token): View
    {
        $this->authorizeToken($token);

        return view('deploy', [
            'token' => $token,
            'tasks' => MaintenanceTasks::all(),
            'needsAdmin' => $this->needsFirstAdmin(),
        ]);
    }

    public function run(Request $request, string $token): RedirectResponse
    {
        $this->authorizeToken($token);

        $validated = $request->validate([
            'task' => ['required', 'string', Rule::in(array_keys(MaintenanceTasks::all()))],
        ]);

        $result = MaintenanceTasks::run($validated['task']);

        return back()->with('result', $result + ['task' => $validated['task']]);
    }

    public function createAdmin(Request $request, string $token): RedirectResponse
    {
        $this->authorizeToken($token);
        abort_unless($this->needsFirstAdmin(), 403, 'An admin account already exists.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        User::forceCreate($validated + ['is_admin' => true, 'is_super_admin' => true]);

        return back()->with('result', [
            'task' => 'admin',
            'ok' => true,
            'output' => "Admin account created for {$validated['email']}. Sign in at /admin.",
        ]);
    }

    private function authorizeToken(string $token): void
    {
        $expected = (string) config('deploy.token');

        abort_if(strlen($expected) < 32 || ! hash_equals($expected, $token), 404);
    }

    private function needsFirstAdmin(): bool
    {
        return Schema::hasTable('users') && User::query()->doesntExist();
    }
}
