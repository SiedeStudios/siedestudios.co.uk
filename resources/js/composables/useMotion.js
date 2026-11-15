import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { gsap, ScrollTrigger, revealIn } from '@/lib/motion';

/**
 * Run GSAP code scoped to a component root and clean everything up on unmount.
 * The setup function receives the root element and a gsap.context.
 */
export function useMotion(rootRef, setup, { reveal = true } = {}) {
    let ctx;

    onMounted(async () => {
        await nextTick();
        const root = rootRef.value;
        if (!root) return;

        ctx = gsap.context(() => {
            setup?.(root, ctx);
            if (reveal) revealIn(root);
        }, root);

        // Images and fonts change layout after mount; re-measure triggers.
        requestAnimationFrame(() => ScrollTrigger.refresh());
        document.fonts?.ready.then(() => ScrollTrigger.refresh());
    });

    onBeforeUnmount(() => ctx?.revert());

    return { context: () => ctx };
}
