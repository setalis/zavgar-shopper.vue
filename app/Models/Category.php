<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\InteractsWithStorefrontMedia;
use App\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Shopper\Models\Category as Model;

#[ObservedBy([CategoryObserver::class])]
final class Category extends Model
{
    use InteractsWithStorefrontMedia;

    /**
     * @param  Collection<int, self>  $categories
     * @return Collection<int, self>
     */
    public static function hydrateBranchProductsCount(Collection $categories): Collection
    {
        if ($categories->isEmpty()) {
            return $categories;
        }

        $childrenByParentId = self::query()
            ->toBase()
            ->get(['id', 'parent_id'])
            ->groupBy(fn (object $row): string => (string) $row->parent_id);

        $branchIdsByCategoryId = $categories->mapWithKeys(
            fn (self $category): array => [
                $category->id => collect(self::idsInBranch((int) $category->id, $childrenByParentId)),
            ],
        );

        $productIdsByCategoryId = self::productIdsGroupedByCategoryId(
            $branchIdsByCategoryId->flatten()->unique()->values(),
        );

        return $categories->each(function (self $category) use ($branchIdsByCategoryId, $productIdsByCategoryId): void {
            $category->setAttribute(
                'products_count',
                $branchIdsByCategoryId
                    ->get($category->id, collect())
                    ->flatMap(
                        fn (int $categoryId): BaseCollection => $productIdsByCategoryId->get($categoryId, collect()),
                    )
                    ->unique()
                    ->count(),
            );
        });
    }

    /**
     * @param  BaseCollection<string, BaseCollection<int, object{id: int|string, parent_id: int|string|null}>>  $childrenByParentId
     * @return list<int>
     */
    private static function idsInBranch(int $categoryId, BaseCollection $childrenByParentId): array
    {
        $ids = [$categoryId];

        foreach ($childrenByParentId->get((string) $categoryId, collect()) as $child) {
            array_push($ids, ...self::idsInBranch((int) $child->id, $childrenByParentId));
        }

        return $ids;
    }

    /**
     * @param  BaseCollection<int, int>  $categoryIds
     * @return BaseCollection<int|string, BaseCollection<int, int|string>>
     */
    private static function productIdsGroupedByCategoryId(BaseCollection $categoryIds): BaseCollection
    {
        if ($categoryIds->isEmpty()) {
            return collect();
        }

        $productsTable = (new Product)->getTable();
        $pivotTable = shopper_table('product_has_relations');

        return Product::query()
            ->join($pivotTable, "{$pivotTable}.product_id", '=', "{$productsTable}.id")
            ->where("{$pivotTable}.productable_type", (new self)->getMorphClass())
            ->whereIn("{$pivotTable}.productable_id", $categoryIds)
            ->toBase()
            ->get([
                "{$pivotTable}.productable_id",
                "{$productsTable}.id as product_id",
            ])
            ->groupBy('productable_id')
            ->map(fn (BaseCollection $rows): BaseCollection => $rows->pluck('product_id'));
    }
}
