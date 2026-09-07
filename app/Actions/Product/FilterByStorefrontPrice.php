<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

final class FilterByStorefrontPrice
{
    /**
     * @param  Builder<Product>|Relation<*, Product, *>  $query
     * @return array{min: int, max: int}|null
     */
    public function bounds(Builder|Relation $query): ?array
    {
        $query = $this->toBuilder($query);
        ['sql' => $sql, 'bindings' => $bindings] = $this->amountExpression($query);

        $clone = $query->clone()->reorder();
        $clone->getQuery()->columns = [];

        $row = $clone
            ->toBase()
            ->selectRaw(
                "MIN({$sql}) as min_amount, MAX({$sql}) as max_amount",
                [...$bindings, ...$bindings],
            )
            ->first();

        if ($row === null || $row->min_amount === null || $row->max_amount === null) {
            return null;
        }

        return [
            'min' => (int) $row->min_amount,
            'max' => (int) $row->max_amount,
        ];
    }

    /**
     * @param  Builder<Product>|Relation<*, Product, *>  $query
     * @return Builder<Product>
     */
    public function apply(Builder|Relation $query, ?int $min, ?int $max): Builder
    {
        $query = $this->toBuilder($query);

        if ($min === null && $max === null) {
            return $query;
        }

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        ['sql' => $sql, 'bindings' => $bindings] = $this->amountExpression($query);

        if ($min !== null && $max !== null) {
            return $query->whereRaw("{$sql} BETWEEN ? AND ?", [...$bindings, $min, $max]);
        }

        if ($min !== null) {
            return $query->whereRaw("{$sql} >= ?", [...$bindings, $min]);
        }

        return $query->whereRaw("{$sql} <= ?", [...$bindings, $max]);
    }

    /**
     * @param  Builder<Product>  $query
     * @return array{sql: string, bindings: list<mixed>}
     */
    private function amountExpression(Builder $query): array
    {
        return $query->getModel()->storefrontAmountSql($query->getModel()->getTable());
    }

    /**
     * @param  Builder<Product>|Relation<*, Product, *>  $query
     * @return Builder<Product>
     */
    private function toBuilder(Builder|Relation $query): Builder
    {
        return $query instanceof Relation ? $query->getQuery() : $query;
    }
}
