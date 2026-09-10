<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 8);
            $table->string('name')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->text('value')->nullable();
            $table->string('eyebrow')->nullable();
            $table->string('highlight')->nullable();
            $table->string('button_text')->nullable();
            $table->timestamps();

            $table->unique(
                ['translatable_type', 'translatable_id', 'locale'],
                'catalog_translations_unique',
            );
            $table->index(['locale', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_translations');
    }
};
