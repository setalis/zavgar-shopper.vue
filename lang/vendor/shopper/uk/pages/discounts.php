<?php

declare(strict_types=1);

return [

    'menu' => 'Знижки',
    'single' => 'знижка',
    'title' => 'Управління знижками та промоакціями',
    'description' => 'Створіть та управляйте кодами знижок та промоакцій, які застосовуються при оформленні або на замовлення клієнтів.',

    'empty_message' => 'Знижок не знайдено...',
    'search' => 'Пошук коду знижки',
    'name_helptext' => 'Клієнти введуть цей код знижки при оформленні.',
    'percentage' => 'Процент',
    'percentage_description' => 'Знижка у %',
    'fixed_amount' => 'Фіксована сума',
    'fixed_amount_description' => 'Знижка у цілих числах',
    'configuration_description' => 'Код знижки активується з моменту натискання кнопки публікації і залишається активним, якщо не змінено.',
    'condition_description' => 'Код знижки застосовується до всіх товарів, якщо не змінено.',
    'applies_to' => 'Застосовується до',
    'entire_order' => 'Всього замовлення',
    'specific_products' => 'Конкретних товарів',
    'select_products' => 'Виберіть товари',
    'min_requirement' => 'Мінімальні вимоги',
    'none' => 'Немає',
    'min_amount' => 'Мінімальна сума покупки (:currency)',
    'min_value' => 'Мінімальне необхідне значення',
    'applies_only_selected' => 'Застосовується лише до вибраних товарів.',
    'min_quantity' => 'Мінімальна кількість товарів',
    'customer_eligibility' => 'Доступність для клієнтів',
    'everyone' => 'Всі',
    'specific_customers' => 'Конкретні клієнти',
    'select_customers' => 'Виберіть клієнтів',
    'usage_limits' => 'Ліміти використання',
    'usage_label' => 'Обмежити загальну кількість використань цього знижки',
    'usage_label_description' => 'Цей ліміт застосовується до всіх клієнтів, а не індивідуально.',
    'usage_value' => 'Значення ліміту використання',
    'limit_one_per_user' => 'Обмежити до одного використання на клієнта',
    'active_dates' => 'Дати активності',
    'active_dates_description' => 'Дати, на які знижка буде доступна для користувачів.',
    'start_date' => 'Дата початку',
    'choose_start_date' => 'Виберіть період дати початку',
    'end_date' => 'Дата завершення',
    'choose_end_date' => 'Виберіть дату завершення',
    'empty_code' => 'Інформація ще не введена.',
    'count_items' => ':count товарів',
    'min_purchase' => 'Мінімальна покупка',

    'modals' => [
        'stock_available' => ':stock доступно',
        'add_products' => 'Додати товари',
        'add_selected_products' => 'Додати вибрані товари',
        'search_product' => 'Пошук товару по назві',

        'add_customers' => 'Додати клієнтів',
        'search_customer' => 'Пошук клієнта по імені',
        'add_selected_customers' => 'Додати вибрані клієнти',

        'remove' => [
            'title' => 'Видалити цей код',
            'description' => 'Ви впевнені, що хочете видалити цей код? Всі дані будуть видалені. Цю дію не можна скасувати.',
            'success_message' => 'Код знижки успішно видалено!',
        ],
    ],

    'active_today' => 'Активно сьогодні',
    'active_from_today' => 'Активно з сьогодні',
    'active_from' => 'Активно з :date',
    'active_date' => 'Активно :date',
    'active_from_to' => 'Активно з :start до :end',
    'one_per_customer' => 'один на клієнта',

    'save' => 'Код знижки :code успішно збережено!',
    'total_use' => 'Використання',

];
