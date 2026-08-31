<script setup lang="ts">
import { Expand, X } from '@lucide/vue';
import { ref } from 'vue';

type GalleryImage = {
    id: number;
    url: string;
    alt: string | null;
    type: string;
};

const props = defineProps<{ images: GalleryImage[] }>();

const activeIndex = ref(0);
const fullscreen = ref(false);

function select(index: number): void {
    activeIndex.value = index;
}

function openFullscreen(): void {
    fullscreen.value = true;
}
</script>

<template>
    <div>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-[80px_1fr]">
            <div
                class="order-2 flex gap-3 overflow-x-auto md:order-1 md:flex-col md:overflow-visible"
            >
                <button
                    v-for="(image, i) in images"
                    :key="image.id"
                    type="button"
                    class="aspect-square w-16 shrink-0 overflow-hidden border md:w-full"
                    :class="
                        i === activeIndex
                            ? 'border-ink'
                            : 'border-transparent opacity-70 hover:opacity-100'
                    "
                    @click="select(i)"
                >
                    <img
                        :src="image.url"
                        :alt="image.alt ?? ''"
                        class="h-full w-full object-cover"
                    />
                </button>
            </div>

            <div
                class="image-zoom group relative order-1 aspect-[4/5] overflow-hidden bg-ivory-deep md:order-2"
            >
                <img
                    v-if="props.images[activeIndex]"
                    :src="props.images[activeIndex].url"
                    :alt="props.images[activeIndex].alt ?? ''"
                    class="h-full w-full object-cover"
                />
                <button
                    type="button"
                    aria-label="Ver en pantalla completa"
                    class="absolute right-4 bottom-4 flex size-10 items-center justify-center bg-ivory/90 text-ink opacity-0 backdrop-blur transition-opacity group-hover:opacity-100"
                    @click="openFullscreen"
                >
                    <Expand class="size-4" />
                </button>
            </div>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="fullscreen"
                class="fixed inset-0 z-[90] flex items-center justify-center bg-ink/95 p-6"
            >
                <button
                    type="button"
                    aria-label="Cerrar"
                    class="absolute top-6 right-6 text-ivory"
                    @click="fullscreen = false"
                >
                    <X class="size-7" />
                </button>
                <img
                    v-if="props.images[activeIndex]"
                    :src="props.images[activeIndex].url"
                    :alt="props.images[activeIndex].alt ?? ''"
                    class="max-h-full max-w-full object-contain"
                />
                <div class="absolute bottom-8 flex gap-2">
                    <button
                        v-for="(image, i) in images"
                        :key="image.id"
                        type="button"
                        class="size-2 rounded-full"
                        :class="i === activeIndex ? 'bg-ivory' : 'bg-ivory/40'"
                        @click="select(i)"
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>
