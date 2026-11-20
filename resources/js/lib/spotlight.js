/** v-spotlight — feeds the pointer position to .spotlight's radial highlight. */
export const spotlight = {
    mounted(el) {
        el.classList.add('spotlight');
        el._spot = (e) => {
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${e.clientX - r.left}px`);
            el.style.setProperty('--my', `${e.clientY - r.top}px`);
        };
        el.addEventListener('pointermove', el._spot);
    },
    unmounted(el) {
        el.removeEventListener('pointermove', el._spot);
    },
};
