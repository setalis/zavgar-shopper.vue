<?php

declare(strict_types=1);

use App\Enums\HomepageBannerBackgroundType;
use App\Enums\HomepageBannerCtaType;
use App\Enums\HomepageBannerSize;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_banners', function (Blueprint $table): void {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('button_text')->nullable();
            $table->string('size')->default(HomepageBannerSize::Medium->value);
            $table->string('background_type')->default(HomepageBannerBackgroundType::Gradient->value);
            $table->string('gradient')->nullable();
            $table->string('cta_type')->default(HomepageBannerCtaType::Url->value);
            $table->string('cta_url')->nullable();
            $table->foreignId('category_id')->nullable()->index()->constrained(shopper_table('categories'))->nullOnDelete();
            $table->foreignId('product_id')->nullable()->index()->constrained(shopper_table('products'))->nullOnDelete();
            $table->foreignId('collection_id')->nullable()->index()->constrained(shopper_table('collections'))->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->index()->constrained(shopper_table('brands'))->nullOnDelete();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('position')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_banners');
    }
};
