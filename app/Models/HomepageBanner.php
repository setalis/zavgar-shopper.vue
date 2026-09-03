<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\TailwindTintCast;
use App\Enums\HomepageBannerBackgroundType;
use App\Enums\HomepageBannerCtaType;
use App\Enums\HomepageBannerSize;
use App\Support\TailwindTint;
use Database\Factories\HomepageBannerFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shopper\Models\Traits\HasMedia;
use Spatie\MediaLibrary\HasMedia as SpatieHasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class HomepageBanner extends Model implements SpatieHasMedia
{
    /** @use HasFactory<HomepageBannerFactory> */
    use HasFactory;

    use HasMedia;

    public const string MEDIA_BACKGROUND = 'background';

    public const string MEDIA_ACCENT = 'accent';

    protected $fillable = [
        'eyebrow',
        'title',
        'description',
        'button_text',
        'size',
        'background_type',
        'gradient',
        'overlay_gradient',
        'cta_type',
        'cta_url',
        'category_id',
        'product_id',
        'collection_id',
        'brand_id',
        'is_enabled',
        'position',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'size' => 'medium',
        'background_type' => 'gradient',
        'cta_type' => 'url',
        'is_enabled' => true,
        'position' => 0,
    ];

    public function registerMediaCollections(): void
    {
        $disk = (string) config('shopper.media.storage.disk_name', 'public');
        $mimeTypes = config('shopper.media.accepts_mime_types', []);

        $this->addMediaCollection(self::MEDIA_BACKGROUND)
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes($mimeTypes);

        $this->addMediaCollection(self::MEDIA_ACCENT)
            ->useDisk($disk)
            ->singleFile()
            ->acceptsMimeTypes($mimeTypes);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Collection, $this>
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function href(): ?string
    {
        return match ($this->cta_type) {
            HomepageBannerCtaType::Url => filled($this->cta_url) ? $this->cta_url : null,
            HomepageBannerCtaType::Category => $this->categoryIsAvailable()
                ? route('shop.category', $this->category)
                : null,
            HomepageBannerCtaType::Product => $this->product?->isPublished()
                ? route('shop.product', $this->product)
                : null,
            HomepageBannerCtaType::Collection => $this->collectionIsPublished()
                ? route('shop.collection', $this->collection)
                : null,
            HomepageBannerCtaType::Brand => $this->brandIsAvailable()
                ? route('shop.brand', $this->brand)
                : null,
        };
    }

    /**
     * @return array{
     *     id: int,
     *     size: string,
     *     eyebrow: string|null,
     *     title: string,
     *     description: string|null,
     *     button_text: string|null,
     *     href: string|null,
     *     background_type: string,
     *     gradient: array{token: string, from: string, to: string}|null,
     *     overlay_gradient: array{token: string, from: string, to: string}|null,
     *     background_image: string|null,
     *     accent_image: string|null
     * }
     */
    public function toStorefrontArray(): array
    {
        $href = $this->href();
        $buttonText = filled($this->button_text) ? $this->button_text : null;

        return [
            'id' => $this->id,
            'size' => $this->size->value,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'description' => $this->description,
            'button_text' => $href !== null ? $buttonText : null,
            'href' => $buttonText !== null ? $href : null,
            'background_type' => $this->background_type->value,
            'gradient' => $this->tintPayload($this->gradient),
            'overlay_gradient' => $this->tintPayload($this->overlay_gradient),
            'background_image' => $this->mediaUrl(self::MEDIA_BACKGROUND),
            'accent_image' => $this->mediaUrl(self::MEDIA_ACCENT),
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => HomepageBannerSize::class,
            'background_type' => HomepageBannerBackgroundType::class,
            'gradient' => TailwindTintCast::class,
            'overlay_gradient' => TailwindTintCast::class,
            'cta_type' => HomepageBannerCtaType::class,
            'is_enabled' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * @return array{token: string, from: string, to: string}|null
     */
    private function tintPayload(?TailwindTint $tint): ?array
    {
        if (! $tint instanceof TailwindTint) {
            return null;
        }

        return [
            'token' => $tint->value(),
            'from' => $tint->oklch(),
            'to' => $tint->darker()->oklch(),
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

    private function mediaUrl(string $collection): ?string
    {
        $media = $this->getFirstMedia($collection);

        return $media instanceof Media ? $media->getUrl() : null;
    }
}
