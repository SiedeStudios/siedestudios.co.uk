import { gsap } from 'gsap';

const fine = () => window.matchMedia('(pointer: fine)').matches;

/** v-magnetic — element drifts toward the pointer while hovered. */
export const magnetic = {
    mounted(el, binding) {
        if (!fine()) return;
        const strength = Number(binding.value ?? 0.35);
        const xTo = gsap.quickTo(el, 'x', { duration: 0.6, ease: 'power3.out' });
        const yTo = gsap.quickTo(el, 'y', { duration: 0.6, ease: 'power3.out' });

        el._magMove = (e) => {
            const r = el.getBoundingClientRect();
            xTo((e.clientX - (r.left + r.width / 2)) * strength);
            yTo((e.clientY - (r.top + r.height / 2)) * strength);
        };
        el._magLeave = () => {
            xTo(0);
            yTo(0);
        };
        el.addEventListener('pointermove', el._magMove);
        el.addEventListener('pointerleave', el._magLeave);
    },
    unmounted(el) {
        el.removeEventListener('pointermove', el._magMove);
        el.removeEventListener('pointerleave', el._magLeave);
    },
};
