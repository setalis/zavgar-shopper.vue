<?php

declare(strict_types=1);

use App\Enums\ContactTopic;
use App\Mail\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function contactPayload(array $overrides = []): array
{
    return [
        'first_name' => 'Olena',
        'last_name' => 'Koval',
        'email' => 'olena@example.com',
        'phone' => '095 580 77 07',
        'topic' => ContactTopic::Order->value,
        'order_number' => 'ZG-1001',
        'message' => 'Need a viscosity recommendation.',
        ...$overrides,
    ];
}

test('contact page renders the shop contact component', function (): void {
    $this->get(route('shop.contact'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('shop/contact'));
});

test('contact form queues a message to the store inbox', function (): void {
    Mail::fake();

    $this->from(route('shop.contact'))
        ->post(route('shop.contact.store'), contactPayload())
        ->assertRedirect(route('shop.contact'))
        ->assertSessionHasNoErrors();

    Mail::assertQueued(ContactMessage::class, function (ContactMessage $mail): bool {
        return $mail->hasTo(config('mail.contact.address'))
            && $mail->hasReplyTo('olena@example.com');
    });
});

test('contact form validates required fields and preserves old input', function (): void {
    Mail::fake();

    $response = $this->from(route('shop.contact'))
        ->post(route('shop.contact.store'), [
            'first_name' => 'Olena',
            'last_name' => '',
            'email' => 'not-an-email',
            'topic' => ContactTopic::Order->value,
            'message' => '',
        ]);

    $response
        ->assertRedirect(route('shop.contact'))
        ->assertSessionHasErrors(['last_name', 'email', 'message']);

    expect(session()->getOldInput('first_name'))->toBe('Olena');

    Mail::assertNothingOutgoing();
});

test('contact mailable includes the enquiry details', function (): void {
    $mailable = new ContactMessage(
        firstName: 'Olena',
        lastName: 'Koval',
        email: 'olena@example.com',
        phone: '095 580 77 07',
        topic: ContactTopic::Product,
        orderNumber: 'ZG-1001',
        body: 'Need a viscosity recommendation.',
    );

    $mailable->assertHasReplyTo('olena@example.com');
    $mailable->assertSeeInHtml('Olena Koval');
    $mailable->assertSeeInHtml('olena@example.com');
    $mailable->assertSeeInHtml('Need a viscosity recommendation.');
    $mailable->assertSeeInHtml('ZG-1001');
    $mailable->assertSeeInHtml('095 580 77 07');
});
