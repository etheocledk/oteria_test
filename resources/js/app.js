import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

createInertiaApp({
    title: (title) => (title ? `${title} - SecureDesk` : 'SecureDesk'),
    resolve: (name) => {
        const page = require(`./Pages/${name}.vue`).default;
        page.layout ??= name.startsWith('Auth/') ? GuestLayout : AppLayout;
        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4f46e5',
    },
});
