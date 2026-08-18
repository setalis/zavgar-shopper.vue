<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PendingProductImportStatus;
use Database\Factories\PendingProductImportFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PendingProductImport extends Model
{
    /** @use HasFactory<PendingProductImportFactory> */
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'payload',
        'status',
        'product_id',
        'approved_by',
        'approved_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === PendingProductImportStatus::Pending;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', PendingProductImportStatus::Pending);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status' => PendingProductImportStatus::class,
            'approved_at' => 'datetime',
        ];
    }
}
