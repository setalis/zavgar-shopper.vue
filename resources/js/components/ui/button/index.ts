import type { VariantProps } from 'class-variance-authority';
import { cva } from 'class-variance-authority';

export { default as Button } from './Button.vue';

export const buttonVariants = cva(
    "inline-flex shrink-0 items-center justify-center gap-2 rounded-full border border-transparent leading-none font-semibold whitespace-nowrap transition-all duration-200 ease-brand outline-none select-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40 disabled:pointer-events-none disabled:opacity-50 aria-invalid:border-destructive aria-invalid:ring-[3px] aria-invalid:ring-destructive/20 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
    {
        variants: {
            variant: {
                default:
                    'bg-primary text-primary-foreground shadow-brand hover:-translate-y-px hover:bg-brand-deep',
                ink: 'bg-ink text-paper hover:bg-primary',
                paper: 'bg-paper text-ink hover:bg-ink hover:text-paper',
                outline:
                    'border-rule-strong bg-transparent text-ink hover:border-ink hover:bg-ink hover:text-paper',
                secondary:
                    'bg-secondary text-secondary-foreground hover:bg-rule-strong/60',
                ghost: 'text-ink-soft hover:bg-brand-soft hover:text-brand-deep',
                destructive:
                    'bg-destructive text-white hover:bg-destructive/90 focus-visible:ring-destructive/30',
                onDark: 'border-white/30 bg-transparent text-paper hover:border-paper hover:bg-paper hover:text-ink',
                link: 'text-primary underline-offset-4 hover:underline',
            },
            size: {
                default: 'px-[22px] py-3 text-sm',
                sm: 'px-3.5 py-2 text-xs',
                lg: 'px-7 py-4 text-base',
                icon: 'size-11 rounded-full',
                'icon-sm': 'size-9 rounded-full',
                'icon-lg': 'size-12 rounded-full',
            },
            block: {
                true: 'w-full',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>;
