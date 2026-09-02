<script setup lang="ts">
import { ref } from 'vue';
import { Toaster } from '@/components/ui/sonner';
import { useTrans } from '@/composables/useTrans';
import StoreCategoryNav from '@/layouts/storefront/store-category-nav.vue';
import StoreFooter from '@/layouts/storefront/store-footer.vue';
import StoreHeader from '@/layouts/storefront/store-header.vue';
import StoreMobileDrawer from '@/layouts/storefront/store-mobile-drawer.vue';
import StoreUtilityBar from '@/layouts/storefront/store-utility-bar.vue';

const { t } = useTrans();

const drawerOpen = ref<boolean>(false);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-paper">
        <a
            href="#main"
            class="sr-only rounded-sm bg-ink px-4 py-2 text-paper focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-100"
        >
            {{ t('shop.nav.skip_to_content') }}
        </a>

        <StoreUtilityBar />
        <StoreHeader @open-menu="drawerOpen = true" />
        <StoreCategoryNav />
        <StoreMobileDrawer v-model:open="drawerOpen" />

        <main id="main" class="flex-1">
            <slot />
        </main>

        <StoreFooter />
        <Toaster />
    </div>
</template>
