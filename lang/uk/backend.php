<?php

declare(strict_types=1);

return [
    'tax' => [
        'ttc' => 'з ПДВ',
        'ht' => 'без ПДВ',
    ],
    'profile' => [
        'updated' => 'Профіль оновлено.',
    ],
    'password' => [
        'updated' => 'Пароль оновлено.',
    ],
    'cart' => [
        'insufficient_stock' => 'Недостатньо товару на складі.',
        'added' => 'Товар додано до кошика!',
    ],
    'checkout' => [
        'option_unavailable' => 'Обраний варіант більше недоступний.',
        'payment_prepare_failed' => 'Не вдалося підготувати оплату. Спробуйте ще раз.',
        'payment_preparation_failed' => 'Помилка підготовки оплати.',
        'use_stripe_form' => 'Використайте форму Stripe для завершення замовлення.',
        'payment_initiation_failed' => 'Помилка ініціалізації оплати.',
        'order_error' => 'Сталася помилка при оформленні замовлення. Спробуйте ще раз.',
        'invalid_session' => 'Недійсна сесія оплати.',
        'payment_processing' => 'Ваш платіж ще обробляється. Зачекайте, будь ласка.',
        'payment_not_completed' => 'Оплату не завершено. Спробуйте ще раз.',
        'order_creation_failed' => 'Не вдалося створити замовлення після оплати.',
        'payment_method_unavailable' => 'Обраний спосіб оплати більше недоступний.',
    ],
    'order' => [
        'session_incomplete' => 'Сесія оформлення неповна або закінчилась.',
        'checkout_in_progress' => 'Оформлення вже виконується.',
    ],
];
