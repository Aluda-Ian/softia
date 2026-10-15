<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'seo' => new Seo('Create an Account · Softia', 'Create your Softia account to manage and track orders.', null, url()->current(), 'noindex, nofollow'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:30'],
            'customer_type' => ['nullable', 'string', 'in:b2c,b2b'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'delivery_address' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'customer_type' => $validated['customer_type'] ?? 'b2c',
            'company_name' => $validated['company_name'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'password' => $validated['password'],
            'is_admin' => false,
            'is_super_admin' => false,
        ]);

        // Automatically link any past orders placed with this email address
        $claimedCount = $user->claimOrdersByEmail();

        Auth::login($user);

        $request->session()->regenerate();

        $message = $claimedCount > 0
            ? "Welcome to Softia, {$user->name}! We found and linked {$claimedCount} previous order(s) to your account."
            : "Welcome to Softia, {$user->name}! Your account is ready.";

        return redirect()->route('dashboard')->with('status', $message);
    }
}
