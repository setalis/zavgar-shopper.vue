<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Enums\ContactTopic;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\StoreContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

final class ContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('shop/contact');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Mail::to(config('mail.contact.address'))->send(new ContactMessage(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            email: $data['email'],
            phone: filled($data['phone'] ?? null) ? $data['phone'] : null,
            topic: ContactTopic::from($data['topic']),
            orderNumber: filled($data['order_number'] ?? null) ? $data['order_number'] : null,
            body: $data['message'],
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('backend.contact.sent')]);

        return back();
    }
}
