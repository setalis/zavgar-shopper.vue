<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_uk(): void
    {
        config(['app.locale' => 'uk']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('locale', 'uk')
            ->has('locales')
            ->has('translations')
        );
    }

    public function test_english_url_sets_storefront_locale(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('default_locale', 'uk')
            ->where('locale_urls.uk', '/')
            ->where('locale_urls.en', '/en')
        );
    }

    public function test_storefront_locale_urls_keep_the_current_path(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'uk')
                ->where('locale_urls.uk', '/shop')
                ->where('locale_urls.en', '/en/shop')
            );

        $this->get('/en/shop')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'en')
                ->where('locale_urls.uk', '/shop')
                ->where('locale_urls.en', '/en/shop')
            );
    }

    public function test_ukrainian_home_prefix_redirects_without_locale(): void
    {
        $this->get('/uk')
            ->assertRedirect('/')
            ->assertStatus(301);
    }

    public function test_locale_session_can_still_be_updated(): void
    {
        $response = $this
            ->from(route('home'))
            ->patch(route('locale.update'), ['locale' => 'en']);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('locale', 'en');
        $response->assertSessionHas('shopper_locale', 'en');
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this
            ->from(route('home'))
            ->patch(route('locale.update'), ['locale' => 'fr']);

        $response->assertSessionHasErrors('locale');
    }

    public function test_backend_translation_returns_ukrainian_text(): void
    {
        app()->setLocale('uk');

        $this->assertSame('Товар додано до кошика!', __('backend.cart.added'));
    }

    public function test_frontend_translations_are_shared_for_current_locale(): void
    {
        config(['app.locale' => 'uk']);

        $response = $this->get(route('home'));

        $response->assertInertia(function ($page): void {
            $translations = $page->toArray()['props']['translations'] ?? [];

            if ($translations instanceof Collection) {
                $translations = $translations->all();
            }

            $this->assertSame('Головна', $translations['shop.nav.home'] ?? null);
        });
    }
}
