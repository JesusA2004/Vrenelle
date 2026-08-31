import type { Directive } from 'vue';

/**
 * `v-reveal` — adds the `.reveal` fade/rise treatment (defined in app.css)
 * and flips it to visible the moment the element enters the viewport.
 * Pass a number (ms) to stagger multiple elements: `v-reveal="120"`.
 */
const observer = new IntersectionObserver(
    (entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                const el = entry.target as HTMLElement;
                const delay = Number(el.dataset.revealDelay ?? 0);
                window.setTimeout(() => el.classList.add('is-visible'), delay);
                observer.unobserve(el);
            }
        }
    },
    { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
);

export const vReveal: Directive<HTMLElement, number | undefined> = {
    mounted(el, binding) {
        el.classList.add('reveal');

        if (binding.value) {
            el.dataset.revealDelay = String(binding.value);
        }

        observer.observe(el);
    },
    unmounted(el) {
        observer.unobserve(el);
    },
};
