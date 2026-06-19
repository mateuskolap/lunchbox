import { createInertiaApp } from '@inertiajs/vue3';
import { i18nVue } from 'laravel-vue-i18n';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18nVue, {
                resolve: async (lang: string) => {
                    const langs = import.meta.glob('../../lang/*.json');

                    // Try exact match
                    if (langs[`../../lang/${lang}.json`]) {
                        return await langs[`../../lang/${lang}.json`]();
                    }

                    // Try normalizations
                    const cleanLang = lang.replace('-', '_');
                    if (langs[`../../lang/${cleanLang}.json`]) {
                        return await langs[`../../lang/${cleanLang}.json`]();
                    }

                    const cleanLangHyphen = lang.replace('_', '-');
                    if (langs[`../../lang/${cleanLangHyphen}.json`]) {
                        return await langs[`../../lang/${cleanLangHyphen}.json`]();
                    }

                    // Try base language
                    const baseLang = lang.split(/[-_]/)[0];
                    if (langs[`../../lang/${baseLang}.json`]) {
                        return await langs[`../../lang/${baseLang}.json`]();
                    }

                    // Fallback to English
                    if (langs['../../lang/en.json']) {
                        return await langs['../../lang/en.json']();
                    }

                    return {};
                },
            })
            .mount(el as Element);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
