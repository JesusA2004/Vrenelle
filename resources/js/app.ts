import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AccountLayout from '@/layouts/AccountLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import StoreLayout from '@/layouts/StoreLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Vrenelle';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('admin/'):
                return AdminLayout;
            case name.startsWith('account/'):
                return AccountLayout;
            case name.startsWith('settings/'):
                return AccountLayout;
            case name.startsWith('store/'):
            case name.startsWith('errors/'):
                return StoreLayout;
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#9c8654',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
