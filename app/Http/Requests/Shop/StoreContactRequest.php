<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Enums\ContactTopic;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'topic' => ['required', Rule::enum(ContactTopic::class)],
            'order_number' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }
}
