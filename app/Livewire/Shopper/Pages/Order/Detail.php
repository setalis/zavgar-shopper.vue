<?php

declare(strict_types=1);

namespace App\Livewire\Shopper\Pages\Order;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Shopper\Core\Enum\OrderStatus;
use Shopper\Core\Enum\PaymentStatus;
use Shopper\Core\Enum\ShippingStatus;
use Shopper\Core\Events\Orders\OrderCompleted;
use Shopper\Core\Events\Orders\OrderPaid;
use Shopper\Livewire\Pages\Order\Detail as BaseDetail;

class Detail extends BaseDetail
{
    public function markPaidAction(): Action
    {
        return Action::make('markPaid')
            ->label(__('shopper::forms.actions.mark_paid'))
            ->authorize('edit_orders')
            ->visible($this->order->isPaymentPending() || $this->order->isPaymentAuthorized())
            ->action(function (): void {
                $data = ['payment_status' => PaymentStatus::Paid];

                if ($this->order->isNew()) {
                    $data['status'] = OrderStatus::Processing;
                }

                $shouldComplete = $this->order->isProcessing()
                    && $this->order->shipping_status === ShippingStatus::Delivered;

                if ($shouldComplete) {
                    $data['status'] = OrderStatus::Completed;
                }

                $this->order->update($data);

                $this->order->refresh();
                $this->dispatch('order.updated');

                event(new OrderPaid($this->order));

                if ($shouldComplete) {
                    event(new OrderCompleted($this->order));
                }

                Notification::make()
                    ->title(__('shopper::pages/orders.notifications.paid'))
                    ->success()
                    ->send();
            });
    }
}
