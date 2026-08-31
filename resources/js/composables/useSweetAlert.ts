import Swal from 'sweetalert2';
import type { SweetAlertIcon } from 'sweetalert2';

/**
 * Centralized SweetAlert2 wrapper, styled to match the Vrenelle editorial
 * identity. Never call the bare `Swal.fire` (or `window.alert`) from a
 * component — go through one of these helpers so every dialog in the app
 * looks and behaves consistently.
 */
// buttonsStyling:false disables Swal's own inline colors so Tailwind classes apply.
const styledSwal = Swal.mixin({
    background: '#faf7f1',
    color: '#14120f',
    buttonsStyling: false,
    customClass: {
        popup: 'rounded-none border border-[#e4ddce] font-sans',
        confirmButton:
            'inline-flex items-center justify-center rounded-none bg-[#14120f] px-6 py-2.5 text-xs tracking-[0.14em] uppercase text-[#faf7f1] transition hover:bg-[#2a2620] mx-1',
        cancelButton:
            'inline-flex items-center justify-center rounded-none border border-[#948c7e] px-6 py-2.5 text-xs tracking-[0.14em] uppercase text-[#14120f] transition hover:bg-[#e4ddce] mx-1',
        title: 'font-serif! text-2xl!',
    },
});

export function useSweetAlert() {
    function success(message: string, title = 'Listo') {
        return styledSwal.fire({ icon: 'success', title, text: message });
    }

    function error(
        message: string,
        title = 'No fue posible completar la acción',
    ) {
        return styledSwal.fire({ icon: 'error', title, text: message });
    }

    function info(message: string, title = 'Aviso') {
        return styledSwal.fire({ icon: 'info', title, text: message });
    }

    async function confirmDestructive(options: {
        title: string;
        text?: string;
        confirmText?: string;
        icon?: SweetAlertIcon;
    }): Promise<boolean> {
        const result = await styledSwal.fire({
            icon: options.icon ?? 'warning',
            title: options.title,
            text: options.text,
            showCancelButton: true,
            confirmButtonText: options.confirmText ?? 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        });

        return result.isConfirmed;
    }

    /**
     * The specific "quantity exceeds immediate availability" dialog used
     * across the catalog and product page — offers the WhatsApp request
     * path instead of a blunt "out of stock" message.
     */
    async function stockExceeded(
        message: string,
        whatsappUrl?: string | null,
    ): Promise<void> {
        await styledSwal
            .fire({
                icon: 'info',
                title: 'Disponibilidad limitada',
                text: message,
                showCancelButton: Boolean(whatsappUrl),
                confirmButtonText: 'Entendido',
                cancelButtonText: 'Solicitar más piezas',
            })
            .then((result) => {
                if (
                    result.dismiss === Swal.DismissReason.cancel &&
                    whatsappUrl
                ) {
                    window.open(whatsappUrl, '_blank', 'noopener');
                }
            });
    }

    return {
        success,
        error,
        info,
        confirmDestructive,
        stockExceeded,
        raw: styledSwal,
    };
}
