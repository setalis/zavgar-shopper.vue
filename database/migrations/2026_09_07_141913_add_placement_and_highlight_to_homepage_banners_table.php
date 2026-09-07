<?php

declare(strict_types=1);

use App\Enums\HomepageBannerPlacement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_banners', function (Blueprint $table): void {
            $table->string('highlight')->nullable()->after('title');
            $table->string('placement')->default(HomepageBannerPlacement::Bento->value)->index()->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_banners', function (Blueprint $table): void {
            $table->dropColumn(['highlight', 'placement']);
        });
    }
};
