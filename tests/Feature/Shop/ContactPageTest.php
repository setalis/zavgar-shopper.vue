<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('contact page renders the shop contact component', function (): void {
    $this->get(route('shop.contact'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('shop/contact'));
});
