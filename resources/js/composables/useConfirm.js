import { router } from '@inertiajs/vue3';

/** Confirm, then send a non-GET Inertia request. */
export function confirmVisit(message, url, method = 'delete', data = {}, options = {}) {
    if (!window.confirm(message)) return;
    router.visit(url, { method, data, preserveScroll: true, ...options });
}
