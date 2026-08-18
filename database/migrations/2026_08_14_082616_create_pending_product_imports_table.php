<?php

declare(strict_types=1);

use App\Enums\PendingProductImportStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_product_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->json('payload');
            $table->string('status')->default(PendingProductImportStatus::Pending->value)->index();
            $table->foreignId('product_id')->nullable()->constrained(shopper_table('products'))->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_product_imports');
    }
};
