<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ShowCategoryRequest extends FormRequest
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
            'sort' => ['sometimes', 'string', 'in:latest,name'],
            'attrs' => ['sometimes', 'array'],
            'attrs.*' => ['array'],
            'attrs.*.*' => ['string', 'max:255'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function selectedAttrs(): array
    {
        /** @var array<string, mixed> $attrs */
        $attrs = $this->validated('attrs') ?? [];
        $selected = [];

        foreach ($attrs as $slug => $keys) {
            if (! is_array($keys)) {
                continue;
            }

            $normalized = array_values(array_unique(array_filter(
                $keys,
                fn (mixed $key): bool => is_string($key) && $key !== '',
            )));

            if ($normalized === []) {
                continue;
            }

            $selected[(string) $slug] = $normalized;
        }

        return $selected;
    }

    public function sort(): string
    {
        return $this->validated('sort') ?? 'latest';
    }
}
