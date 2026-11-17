import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Shared state for the one <ConfirmDialog> mounted in app.js.
 * Replaces window.confirm with a themed modal.
 */
export const dialog = reactive({
    open: false,
    busy: false,
    title: '',
    body: '',
    confirmLabel: 'Confirm',
    danger: false,
    resolve: null,
    action: null,
});

const DANGER = /^(delete|remove|revoke|void|cancel|reset|clear|disconnect)\b/i;

/** "Delete X? Their files go too." → title "Delete X?", body "Their files go too." */
function split(message) {
    const at = message.indexOf('? ');
    return at === -1 ? [message, ''] : [message.slice(0, at + 1), message.slice(at + 2)];
}

/**
 * Ask a yes/no question. Resolves true or false.
 *   confirm('Delete this share?')
 *   confirm({ title, body, confirmLabel, danger })
 */
export function confirm(options) {
    const o = typeof options === 'string' ? { message: options } : options;
    const [title, body] = o.message ? split(o.message) : [o.title, o.body ?? ''];
    const verb = title.match(/^[\w-]+/)?.[0] ?? 'Confirm';

    dialog.resolve?.(false);
    Object.assign(dialog, {
        open: true,
        busy: false,
        title,
        body,
        confirmLabel: o.confirmLabel ?? verb.charAt(0).toUpperCase() + verb.slice(1),
        danger: o.danger ?? DANGER.test(title),
        action: o.action ?? null,
    });
    return new Promise((resolve) => (dialog.resolve = resolve));
}

/** Confirm in the modal, then send a non-GET Inertia request (the modal waits for it). */
export function confirmVisit(message, url, method = 'delete', data = {}, options = {}) {
    const { confirmLabel, danger, ...visit } = options;
    return confirm({
        message,
        confirmLabel,
        danger: danger ?? (method === 'delete' ? true : undefined),
        action: () =>
            new Promise((done) =>
                router.visit(url, {
                    method,
                    data,
                    preserveScroll: true,
                    ...visit,
                    onFinish: (...args) => {
                        visit.onFinish?.(...args);
                        done();
                    },
                }),
            ),
    });
}
