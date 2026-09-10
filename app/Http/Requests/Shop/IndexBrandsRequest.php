<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use App\Models\Brand;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexBrandsRequest extends FormRequest
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
            'q' => ['sometimes', 'nullable', 'string', 'max:100'],
            'letter' => ['sometimes', 'nullable', 'string', Rule::in($this->letterOptions())],
            'sort' => ['sometimes', 'string', 'in:name,latest,products'],
            'with_products' => ['sometimes', 'boolean'],
        ];
    }

    public function search(): string
    {
        return trim((string) ($this->validated('q') ?? ''));
    }

    public function letter(): ?string
    {
        $letter = $this->validated('letter');

        return filled($letter) ? (string) $letter : null;
    }

    public function sort(): string
    {
        return $this->validated('sort') ?? 'name';
    }

    public function withProducts(): bool
    {
        return $this->boolean('with_products');
    }

    /**
     * @param  Builder<Brand>  $query
     * @return Builder<Brand>
     */
    public function apply(Builder $query): Builder
    {
        $search = $this->search();

        if ($search !== '') {
            $query->where('name', 'like', '%'.addcslashes($search, '%_\\').'%');
        }

        $letter = $this->letter();

        if ($letter === '0-9') {
            $query->where(function (Builder $builder): void {
                foreach (range(0, 9) as $digit) {
                    $builder->orWhere('name', 'like', $digit.'%');
                }
            });
        } elseif ($letter === 'other') {
            $query->whereRaw('LOWER(SUBSTR(name, 1, 1)) NOT BETWEEN ? AND ?', ['a', 'z'])
                ->whereRaw('SUBSTR(name, 1, 1) NOT BETWEEN ? AND ?', ['0', '9']);
        } elseif ($letter !== null) {
            $query->where('name', 'like', $letter.'%');
        }

        if ($this->withProducts()) {
            $query->whereHas(
                'products',
                fn (Builder $products): Builder => $products->scopes('publish'),
            );
        }

        return $query;
    }

    public static function letterKey(string $name): string
    {
        $first = mb_strtoupper(mb_substr(trim($name), 0, 1));

        if (preg_match('/^[A-Z]$/', $first) === 1) {
            return $first;
        }

        if (preg_match('/^[0-9]$/', $first) === 1) {
            return '0-9';
        }

        return 'other';
    }

    /**
     * @return list<string>
     */
    private function letterOptions(): array
    {
        return ['0-9', 'other', ...range('A', 'Z')];
    }
}
