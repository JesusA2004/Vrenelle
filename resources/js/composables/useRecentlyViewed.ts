import { ref } from 'vue';

export type RecentlyViewedProduct = {
    slug: string;
    name: string;
    image: string | null;
    price: string | null;
};

const STORAGE_KEY = 'vrenelle:recently-viewed';
const MAX_ITEMS = 8;

function read(): RecentlyViewedProduct[] {
    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);

        return raw ? (JSON.parse(raw) as RecentlyViewedProduct[]) : [];
    } catch {
        return [];
    }
}

function write(items: RecentlyViewedProduct[]): void {
    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    } catch {
        // Storage unavailable (private mode, quota) — recently viewed is a
        // convenience feature, so we fail silently.
    }
}

export function useRecentlyViewed() {
    const items = ref<RecentlyViewedProduct[]>(read());

    function record(product: RecentlyViewedProduct): void {
        const filtered = items.value.filter((p) => p.slug !== product.slug);
        filtered.unshift(product);
        items.value = filtered.slice(0, MAX_ITEMS);
        write(items.value);
    }

    function others(excludeSlug: string): RecentlyViewedProduct[] {
        return items.value.filter((p) => p.slug !== excludeSlug);
    }

    return { items, record, others };
}
