<?php

declare(strict_types=1);

use App\Livewire\Shopper\Pages\Order\Detail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Shopper\Core\Enum\OrderStatus;
use Shopper\Core\Enum\PaymentStatus;
use Shopper\Core\Enum\ShippingStatus;
use Shopper\Core\Models\Order;
use Shopper\Database\Seeders\AuthTableSeeder;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AuthTableSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole(config('shopper.admin.roles.admin'));
});

test('mark paid is available for completed unpaid delivered orders', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Pending,
        'shipping_status' => ShippingStatus::Delivered,
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test(Detail::class, ['order' => $order])
        ->assertSuccessful();

    expect($component->instance()->markPaidAction()->isVisible())->toBeTrue();

    $component->instance()->markPaidAction()->call();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->status)->toBe(OrderStatus::Completed);
});

test('mark paid completes a processing delivered order', function (): void {
    $order = Order::factory()->create([
        'status' => OrderStatus::Processing,
        'payment_status' => PaymentStatus::Pending,
        'shipping_status' => ShippingStatus::Delivered,
    ]);

    $component = Livewire::actingAs($this->admin)
        ->test(Detail::class, ['order' => $order])
        ->assertSuccessful();

    $component->instance()->markPaidAction()->call();

    $order->refresh();

    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->status)->toBe(OrderStatus::Completed);
});
