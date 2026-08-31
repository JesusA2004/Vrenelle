/**
 * Generates a wa.me link server-side (the store's WhatsApp number lives in
 * admin settings, never in the frontend bundle) and opens it in a new tab.
 */
type Reason = 'out_of_stock' | 'exceeded' | 'made_to_order' | 'inquiry';

export function useWhatsApp() {
    async function openRequest(payload: {
        productId: number;
        variantId?: number | null;
        quantity?: number;
        reason: Reason;
    }): Promise<void> {
        const response = await fetch('/whatsapp/enlace', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(
                    document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
                ),
            },
            body: JSON.stringify({
                product_id: payload.productId,
                product_variant_id: payload.variantId ?? null,
                quantity: payload.quantity ?? 1,
                reason: payload.reason,
            }),
        });

        if (!response.ok) {
            return;
        }

        const data = (await response.json()) as { url: string | null };

        if (data.url) {
            window.open(data.url, '_blank', 'noopener');
        }
    }

    return { openRequest };
}
