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
    'reviews' => [
        'submitted' => 'Your review has been submitted and is awaiting moderation.',
        'already_submitted' => 'You have already reviewed this product.',
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
    'product_imports' => [
        'menu' => 'Product import queue',
        'description' => 'Products whose SKU was not found during import. Review and approve them to add them to the catalog.',
        'back_to_products' => 'Back to products',
        'approve' => 'Approve',
        'approve_heading' => 'Add this product?',
        'approve_description' => 'The product will be created in the catalog with the imported data.',
        'reject' => 'Reject',
        'approved' => 'Product :name has been added to the catalog.',
        'approved_bulk' => 'Selected products have been added to the catalog.',
        'rejected' => 'Product import rejected.',
        'empty' => 'No products waiting for approval.',
        'already_processed' => 'This import has already been processed.',
        'sku_already_exists' => 'A product with this SKU already exists.',
        'parent_sku' => 'Parent SKU',
        'parent_sku_help' => 'Leave empty for a product. For a variant, set the parent product SKU.',
        'attributes' => 'Attributes',
        'attributes_help' => 'Variant options, e.g. Color=Red | Size=M',
        'parent_not_found' => 'The parent product SKU was not found. Approve the parent product first.',
        'parent_not_variant' => 'The parent SKU must belong to a variant product.',
        'variant_sku_belongs_to_other_product' => 'This variant SKU already belongs to a different product.',
        'unknown_attribute' => 'Unknown product attribute :name.',
        'price_help' => 'Price in cents for the store default currency.',
        'export_completed' => 'Product export completed. :count rows exported.',
        'export_failed' => ':count rows failed to export.',
        'import_completed' => 'Product import completed. :count existing products were updated.',
        'import_queued' => ':count new SKUs were queued for approval.',
        'import_failed' => ':count rows failed to import.',
        'import_file_placeholder' => 'CSV or Excel (.xlsx)',
        'status' => [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ],
    ],
];
