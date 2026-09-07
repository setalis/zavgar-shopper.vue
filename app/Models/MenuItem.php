<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MenuItemTargetType;
use App\Observers\MenuItemObserver;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([MenuItemObserver::class])]
final class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    public const int MAX_DEPTH = 2;

    protected $fillable = [
        'parent_id',
        'title',
        'target_type',
        'url',
        'brand_id',
        'category_id',
        'collection_id',
        'product_id',
        'is_enabled',
        'position',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'target_type' => 'url',
        'is_enabled' => true,
        'position' => 0,
    ];

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Collection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function href(): ?string
    {
        return match ($this->target_type) {
            MenuItemTargetType::Url => filled($this->url) ? $this->url : null,
            MenuItemTargetType::Brand => $this->brandIsAvailable()
                ? route('shop.brand', $this->brand)
                : null,
            MenuItemTargetType::Category => $this->categoryIsAvailable()
                ? route('shop.category', $this->category)
                : null,
            MenuItemTargetType::Collection => $this->collectionIsPublished()
                ? route('shop.collection', $this->collection)
                : null,
            MenuItemTargetType::Product => $this->product?->isPublished()
                ? route('shop.product', $this->product)
                : null,
        };
    }

    public function depth(): int
    {
        $depth = 0;
        $parentId = $this->parent_id;

        while ($parentId !== null && $depth <= self::MAX_DEPTH) {
            $depth++;
            $parentId = self::query()->where('id', $parentId)->value('parent_id');
        }

        return $depth;
    }

    public function canHaveChildren(): bool
    {
        return $this->depth() < self::MAX_DEPTH;
    }

    /**
     * @return array{id: int, title: string, href: string, children: list<array{id: int, title: string, href: string, children: list<mixed>}>}|null
     */
    public function toNavArray(): ?array
    {
        $href = $this->href();

        if ($href === null) {
            return null;
        }

        $children = $this->relationLoaded('children')
            ? $this->children
            : collect();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'href' => $href,
            'children' => $children
                ->map(fn (self $child): ?array => $child->toNavArray())
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function enabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function roots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_type' => MenuItemTargetType::class,
            'is_enabled' => 'boolean',
            'position' => 'integer',
        ];
    }

    private function categoryIsAvailable(): bool
    {
        return $this->category !== null && $this->category->is_enabled;
    }

    private function collectionIsPublished(): bool
    {
        return $this->collection !== null
            && $this->collection->published_at !== null
            && $this->collection->published_at->lte(now());
    }

    private function brandIsAvailable(): bool
    {
        return $this->brand !== null && $this->brand->is_enabled;
    }
}
