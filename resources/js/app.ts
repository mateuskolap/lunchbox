import { createInertiaApp } from '@inertiajs/vue3';
import { i18nVue } from 'laravel-vue-i18n';
import { registerSW } from 'virtual:pwa-register';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = window.document.title || 'Laravel';

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
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18nVue, {
                resolve: async (lang: string) => {
                    const langs = import.meta.glob('../../lang/*.json');

                    if (langs[`../../lang/${lang}.json`]) {
                        return await langs[`../../lang/${lang}.json`]();
                    }

                    return {};
                },
            });

        if (el) {
            app.mount(el);
        }

        return app;
    },
    progress: {
        color: '#BD5A27',
    },
});

if (typeof window !== 'undefined') {
    initializeTheme();

    initializeFlashToast();

    registerSW({
        immediate: true,
        onNeedRefresh() {
            console.log('Nova versão do PWA disponível!');
        },
        onOfflineReady() {
            console.log('PWA pronto para uso offline.');
        }
    });
}
