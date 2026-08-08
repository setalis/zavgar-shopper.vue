<?php

declare(strict_types=1);

return [
    'tax' => [
        'ttc' => 'TTC',
        'ht' => 'HT',
    ],
    'profile' => [
        'updated' => 'Profile updated.',
    ],
    'password' => [
        'updated' => 'Password updated.',
    ],
    'cart' => [
        'insufficient_stock' => 'Insufficient stock available.',
        'added' => 'Product added to cart!',
    ],
    'checkout' => [
        'option_unavailable' => 'Selected option is no longer available.',
        'payment_prepare_failed' => 'Unable to prepare payment. Please try again.',
        'payment_preparation_failed' => 'Payment preparation failed.',
        'use_stripe_form' => 'Use the Stripe payment form to complete this order.',
        'payment_initiation_failed' => 'Payment initiation failed.',
        'order_error' => 'An error occurred while placing your order. Please try again.',
        'invalid_session' => 'Invalid payment session.',
        'payment_processing' => 'Your payment is still being processed. Please wait a moment.',
        'payment_not_completed' => 'Payment was not completed. Please try again.',
        'order_creation_failed' => 'Order creation failed after payment.',
        'payment_method_unavailable' => 'Selected payment method is no longer available.',
    ],
    'order' => [
        'session_incomplete' => 'Checkout session is incomplete or expired.',
        'checkout_in_progress' => 'A checkout is already in progress.',
    ],
];
