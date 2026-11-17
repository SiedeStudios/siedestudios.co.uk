import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import BareLayout from '@/layouts/BareLayout.vue';
import PortalLayout from '@/layouts/PortalLayout.vue';
import { magnetic } from '@/lib/magnetic';
import { spotlight } from '@/lib/spotlight';

const pages = import.meta.glob('./pages/**/*.vue');
const BARE = ['Admin/Login', 'Portal/Login', 'Portal/Invite', 'Share/Show', 'Share/Locked', 'Testimonial/Submit'];

createInertiaApp({
    title: (title, page) => {
        const name = page?.props?.site?.site_name ?? 'SiedeStudios';
        return title ? `${title} — ${name}` : `${name} — ${page?.props?.site?.site_tagline ?? ''}`.replace(/ — $/, '');
    },
    resolve: (name) => {
        const loader = pages[`./pages/${name}.vue`];
        if (!loader) throw new Error(`Page not found: ${name}`);
        return loader();
    },
    layout: (name) => {
        if (BARE.includes(name)) return BareLayout;
        if (name.startsWith('Admin/')) return AdminLayout;
        if (name.startsWith('Portal/')) return PortalLayout;
        return PublicLayout;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .directive('magnetic', magnetic)
            .directive('spotlight', spotlight)
            .mount(el);
    },
    progress: {
        color: 'var(--accent)',
        delay: 250,
        showSpinner: false,
    },
});
