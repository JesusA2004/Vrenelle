<script setup lang="ts">
import { Minus, Plus } from '@lucide/vue';

const model = defineModel<number>({ default: 1 });

defineProps<{ max?: number }>();

function decrement(): void {
    if (model.value > 1) {
        model.value -= 1;
    }
}

function increment(max?: number): void {
    if (!max || model.value < max) {
        model.value += 1;
    }
}
</script>

<template>
    <div class="inline-flex items-center border border-line">
        <button
            type="button"
            class="flex size-10 items-center justify-center text-ink transition hover:bg-ivory-deep disabled:opacity-30"
            :disabled="model <= 1"
            aria-label="Disminuir cantidad"
            @click="decrement"
        >
            <Minus class="size-3.5" />
        </button>
        <span class="w-10 text-center text-sm text-ink tabular-nums">{{
            model
        }}</span>
        <button
            type="button"
            class="flex size-10 items-center justify-center text-ink transition hover:bg-ivory-deep"
            aria-label="Aumentar cantidad"
            @click="increment(max)"
        >
            <Plus class="size-3.5" />
        </button>
    </div>
</template>
