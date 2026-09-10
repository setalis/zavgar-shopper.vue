<?php

declare(strict_types=1);

use App\Enums\MenuItemTargetType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('title');
            $table->string('target_type')->default(MenuItemTargetType::Url->value);
            $table->string('url')->nullable();
            $table->foreignId('brand_id')->nullable()->index()->constrained(shopper_table('brands'))->nullOnDelete();
            $table->foreignId('category_id')->nullable()->index()->constrained(shopper_table('categories'))->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->index()->constrained(shopper_table('collections'))->nullOnDelete();
            $table->foreignId('product_id')->nullable()->index()->constrained(shopper_table('products'))->nullOnDelete();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
