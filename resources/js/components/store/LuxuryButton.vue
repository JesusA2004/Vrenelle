<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type Variant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'text';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        href?: string;
        as?: 'button' | 'a' | 'link';
        type?: 'button' | 'submit';
        disabled?: boolean;
        method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    }>(),
    {
        variant: 'primary',
        as: 'button',
        type: 'button',
        disabled: false,
        method: 'get',
    },
);

const base =
    'group relative inline-flex items-center justify-center gap-2 overflow-hidden whitespace-nowrap px-7 py-3.5 text-[0.72rem] font-medium tracking-[0.18em] uppercase transition-colors duration-300 disabled:cursor-not-allowed disabled:opacity-50';

const variants: Record<Variant, string> = {
    primary: 'bg-ink text-ivory hover:bg-ink-soft',
    secondary: 'bg-champagne text-ink hover:bg-champagne-soft',
    outline: 'border border-ink text-ink hover:bg-ink hover:text-ivory',
    ghost: 'border border-stone-light text-ink hover:border-ink',
    text: 'px-0 py-0 link-underline normal-case tracking-normal text-sm font-normal',
};

const classes = computed(() => [base, variants[props.variant]]);
</script>

<template>
    <Link
        v-if="as === 'link' && href"
        :href="href"
        :method="method"
        :class="classes"
    >
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </Link>
    <a v-else-if="as === 'a' && href" :href="href" :class="classes">
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </a>
    <button v-else :type="type" :disabled="disabled" :class="classes">
        <span class="relative z-10 flex items-center gap-2">
            <slot />
        </span>
    </button>
</template>
