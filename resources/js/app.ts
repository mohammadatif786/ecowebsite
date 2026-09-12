import '../css/app.css';
import './bootstrap'
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import 'remixicon/fonts/remixicon.css';
import VueDatePicker from '@vuepic/vue-datepicker';
import { useAppearance } from '@/composables/useAppearance';
const { updateAppearance } = useAppearance();
import '@vuepic/vue-datepicker/dist/main.css'

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

// Windows preserves historical directory casing in glob keys even though Git
// tracks these admin directories in lowercase. Expose the canonical Linux/Git
// paths as aliases so Inertia component names resolve consistently everywhere.
for (const [path, resolver] of Object.entries(pages)) {
    const canonicalPath = path
        .replace(/^\.\/pages\/Admin\//, './pages/admin/')
        .replace(/^\.\/pages\/admin\/Auth\//, './pages/admin/auth/')
        .replace(/^\.\/pages\/admin\/Events\//, './pages/admin/events/')
        .replace(/^\.\/pages\/admin\/Taxes\//, './pages/admin/taxes/');

    pages[canonicalPath] = resolver;
}

Object.assign(pages, {
    './pages/new_front/live/Index.vue': () => import('./pages/new_front/live/Index.vue'),
    './pages/new_front/live/Watch.vue': () => import('./pages/new_front/live/Watch.vue'),
    './pages/admin/taxes/TaxManagement.vue': () => import('./pages/admin/taxes/TaxManagement.vue'),
    './pages/admin/taxes/TaxDashboard.vue': () => import('./pages/admin/taxes/TaxDashboard.vue'),
    './pages/admin/taxes/TaxAuthorityApis.vue': () => import('./pages/admin/taxes/TaxAuthorityApis.vue'),
    './pages/admin/taxes/LinkUpCorporateTax.vue': () => import('./pages/admin/taxes/LinkUpCorporateTax.vue'),
    './pages/admin/taxes/TaxSettings.vue': () => import('./pages/admin/taxes/TaxSettings.vue'),
    './pages/admin/taxes/TaxRemittanceSettings.vue': () => import('./pages/admin/taxes/TaxRemittanceSettings.vue'),
    './pages/admin/taxes/TaxRemittanceCenter.vue': () => import('./pages/admin/taxes/TaxRemittanceCenter.vue'),
    './pages/admin/RemittanceDashboard.vue': () => import('./pages/admin/RemittanceDashboard.vue'),
    './pages/admin/SettlementsDashboard.vue': () => import('./pages/admin/SettlementsDashboard.vue'),
    './pages/admin/PayoutOpsDashboard.vue': () => import('./pages/admin/PayoutOpsDashboard.vue'),
    './pages/admin/PayrollDashboard.vue': () => import('./pages/admin/PayrollDashboard.vue'),
    './pages/admin/PricingDashboard.vue': () => import('./pages/admin/PricingDashboard.vue'),
    './pages/admin/FoundationDashboard.vue': () => import('./pages/admin/FoundationDashboard.vue'),
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, pages),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('VueDatePicker', VueDatePicker)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
updateAppearance('light');
