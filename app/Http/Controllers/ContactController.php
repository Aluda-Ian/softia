<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        // Intro text stays editable in Admin > Content > Pages ("Contact Us").
        $page = Page::query()->published()->where('slug', 'contact-us')->first();

        return view('contact', [
            'page' => $page,
            'seo' => $page ? Seo::forModel($page) : Seo::forRoute('contact', 'Contact Us | Softia', 'Talk to the Softia team about orders, quotations and distribution.'),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $message = ContactMessage::create(Arr::except($request->validated(), ['website']) + [
            'ip_address' => $request->ip(),
        ]);

        try {
            Mail::to(config('shop.contact.email'))->send(new ContactMessageReceived($message));
        } catch (Throwable $e) {
            // The message is saved in the admin either way; don't fail the visitor.
            report($e);
        }

        return to_route('contact')->with('contact_sent', true);
    }
}
