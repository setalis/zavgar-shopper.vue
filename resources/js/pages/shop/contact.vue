<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Clock, Mail, MapPin, MessageCircle, Phone } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Container from '@/components/shop/container.vue';
import PageHead from '@/components/shop/page-head.vue';
import SectionHead from '@/components/shop/section-head.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useTrans } from '@/composables/useTrans';
import { home } from '@/routes';

const { t } = useTrans();

/**
 * The template's contact page has no backend counterpart yet, so the form
 * acknowledges the submission locally instead of posting anywhere.
 */
const sent = ref<boolean>(false);
const topic = ref<string>('order');

const infoBlocks = computed(() => [
    { icon: Phone, key: 'phone' },
    { icon: Mail, key: 'email' },
    { icon: MessageCircle, key: 'chat' },
    { icon: MapPin, key: 'address' },
    { icon: Clock, key: 'hours' },
]);

const topics = ['order', 'product', 'returns', 'trade', 'other'] as const;
const faqs = ['shipping', 'returns', 'warranty', 'trade'] as const;

const crumbs = computed(() => [
    { label: t('shop.nav.home'), href: home.url() },
    { label: t('shop.contact.title') },
]);
</script>

<template>
    <Head :title="t('shop.contact.title')" />

    <PageHead
        :title="t('shop.contact.heading')"
        :description="t('shop.contact.subtitle')"
        :crumbs="crumbs"
    />

    <Container>
        <div class="grid gap-14 py-10 lg:grid-cols-[1fr_1.2fr] lg:py-20">
            <div>
                <div
                    v-for="(block, index) in infoBlocks"
                    :key="block.key"
                    :class="[
                        'grid grid-cols-[48px_1fr] items-center gap-4 border-b border-rule py-4',
                        index === 0 && 'pt-0',
                    ]"
                >
                    <span
                        class="grid size-12 place-items-center rounded-full bg-brand-soft text-brand"
                    >
                        <component
                            :is="block.icon"
                            class="size-5"
                            aria-hidden="true"
                        />
                    </span>
                    <div>
                        <p
                            class="mb-0.5 font-mono text-xs tracking-[0.08em] text-ink-mute uppercase"
                        >
                            {{ t(`shop.contact.info.${block.key}.label`) }}
                        </p>
                        <p class="font-heading text-md font-semibold text-ink">
                            {{ t(`shop.contact.info.${block.key}.value`) }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-8 rounded-xl bg-linear-to-br from-card-blue to-card-blue-2 p-7 text-paper"
                >
                    <p
                        class="font-mono text-xs tracking-[0.08em] uppercase opacity-70"
                    >
                        {{ t('shop.contact.trade.eyebrow') }}
                    </p>
                    <h3 class="mt-2 max-w-[22ch] text-lg text-paper">
                        {{ t('shop.contact.trade.title') }}
                    </h3>
                    <p class="mt-2 max-w-[38ch] text-sm opacity-86">
                        {{ t('shop.contact.trade.description') }}
                    </p>
                </div>
            </div>

            <form
                class="rounded-lg border border-rule bg-paper p-7 md:p-10"
                @submit.prevent="sent = true"
            >
                <h2 class="text-xl">{{ t('shop.contact.form.title') }}</h2>
                <p class="mt-1.5 mb-7 text-sm text-ink-mute">
                    {{ t('shop.contact.form.description') }}
                </p>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label
                            for="contact-first-name"
                            class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                        >
                            {{ t('shop.contact.form.first_name') }}
                        </Label>
                        <Input id="contact-first-name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="contact-last-name"
                            class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                        >
                            {{ t('shop.contact.form.last_name') }}
                        </Label>
                        <Input id="contact-last-name" required />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="contact-email"
                            class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                        >
                            {{ t('shop.contact.form.email') }}
                        </Label>
                        <Input id="contact-email" type="email" required />
                    </div>
                    <div class="grid gap-2">
                        <Label
                            for="contact-phone"
                            class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                        >
                            {{ t('shop.contact.form.phone') }}
                        </Label>
                        <Input id="contact-phone" type="tel" />
                    </div>
                </div>

                <div class="mt-4 grid gap-2">
                    <Label
                        class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                    >
                        {{ t('shop.contact.form.topic') }}
                    </Label>
                    <Select v-model="topic">
                        <SelectTrigger class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in topics"
                                :key="option"
                                :value="option"
                            >
                                {{ t(`shop.contact.form.topics.${option}`) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="mt-4 grid gap-2">
                    <Label
                        for="contact-order"
                        class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                    >
                        {{ t('shop.contact.form.order_number') }}
                    </Label>
                    <Input id="contact-order" />
                </div>

                <div class="mt-4 grid gap-2">
                    <Label
                        for="contact-message"
                        class="font-mono text-xs tracking-[0.08em] text-ink-soft uppercase"
                    >
                        {{ t('shop.contact.form.message') }}
                    </Label>
                    <Textarea id="contact-message" rows="5" required />
                </div>

                <Button type="submit" block class="mt-7">
                    {{
                        sent
                            ? t('shop.contact.form.sent')
                            : t('shop.contact.form.submit')
                    }}
                </Button>

                <p class="mt-4 text-xs text-ink-mute">
                    {{ t('shop.contact.form.privacy') }}
                </p>
            </form>
        </div>
    </Container>

    <section class="bg-muted py-14 md:py-20">
        <Container>
            <SectionHead
                :title="t('shop.contact.faq.title')"
                :description="t('shop.contact.faq.description')"
            />

            <Accordion
                type="single"
                collapsible
                class="grid gap-3 md:grid-cols-2"
            >
                <AccordionItem
                    v-for="faq in faqs"
                    :key="faq"
                    :value="faq"
                    class="rounded-lg border border-rule bg-paper px-5"
                >
                    <AccordionTrigger class="font-heading text-sm font-bold">
                        {{ t(`shop.contact.faq.items.${faq}.question`) }}
                    </AccordionTrigger>
                    <AccordionContent class="text-sm text-ink-mute">
                        {{ t(`shop.contact.faq.items.${faq}.answer`) }}
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </Container>
    </section>
</template>
