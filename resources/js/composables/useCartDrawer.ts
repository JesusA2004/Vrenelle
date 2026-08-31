import { ref } from 'vue';

/**
 * Shared singleton so any page can open the cart drawer that lives in
 * StoreLayout after adding an item, without prop-drilling or an event bus.
 */
const isOpen = ref(false);

export function useCartDrawer() {
    return {
        isOpen,
        open: () => {
            isOpen.value = true;
        },
        close: () => {
            isOpen.value = false;
        },
    };
}
