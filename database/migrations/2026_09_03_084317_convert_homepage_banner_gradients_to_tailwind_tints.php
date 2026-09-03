<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private const array LEGACY = [
        'blue' => 'blue-500',
        'purple' => 'purple-500',
        'teal' => 'teal-500',
        'orange' => 'orange-500',
        'green' => 'green-500',
        'black' => 'zinc-950',
    ];

    public function up(): void
    {
        foreach (['gradient', 'overlay_gradient'] as $column) {
            foreach (self::LEGACY as $from => $to) {
                DB::table('homepage_banners')->where($column, $from)->update([$column => $to]);
            }
        }
    }

    public function down(): void
    {
        foreach (['gradient', 'overlay_gradient'] as $column) {
            foreach (self::LEGACY as $from => $to) {
                DB::table('homepage_banners')->where($column, $to)->update([$column => $from]);
            }
        }
    }
};
