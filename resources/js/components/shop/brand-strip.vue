<script setup lang="ts">
import Container from '@/components/shop/container.vue';

/**
 * Sprylo's wordmark strip. Static by design: the template ships plain
 * typographic brand names rather than logo assets.
 */
const brands = ['Apple', 'Samsung', 'Sony', 'Bose', 'Canon', 'Logitech'];
</script>

<template>
    <section class="border-y border-rule bg-paper py-10">
        <Container>
            <div class="flex flex-wrap items-center justify-around gap-10">
                <span
                    v-for="brand in brands"
                    :key="brand"
                    class="font-heading text-lg font-extrabold tracking-[-0.02em] text-ink-faint uppercase transition hover:text-ink md:text-xl"
                >
                    {{ brand }}
                </span>
            </div>
        </Container>
    </section>
</template>
