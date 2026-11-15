import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import { Flip } from 'gsap/Flip';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger, SplitText, Flip);
gsap.defaults({ ease: 'expo.out', duration: 1 });

export const prefersReducedMotion = () =>
    typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

let lenis = null;

export function startSmoothScroll() {
    if (lenis || prefersReducedMotion()) return lenis;

    lenis = new Lenis({ lerp: 0.11, wheelMultiplier: 0.95, smoothWheel: true });
    lenis.on('scroll', ScrollTrigger.update);
    gsap.ticker.add((time) => lenis?.raf(time * 1000));
    gsap.ticker.lagSmoothing(0);

    return lenis;
}

export function stopSmoothScroll() {
    lenis?.destroy();
    lenis = null;
}

export const getLenis = () => lenis;

export function scrollToTop(immediate = true) {
    if (lenis) lenis.scrollTo(0, { immediate, force: true });
    else window.scrollTo({ top: 0, behavior: immediate ? 'instant' : 'smooth' });
}

export function scrollToEl(target, offset = -90) {
    if (lenis) lenis.scrollTo(target, { offset, duration: 1.4 });
    else document.querySelector(target)?.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Generic scroll-in reveal for [data-reveal] elements inside a scope.
 * data-reveal="up" | "fade" | "clip" | "left"
 */
export function revealIn(scope) {
    const els = gsap.utils.toArray('[data-reveal]', scope);
    if (!els.length) return;

    if (prefersReducedMotion()) {
        gsap.set(els, { opacity: 1, clearProps: 'transform,clipPath' });
        return;
    }

    els.forEach((el) => {
        const type = el.dataset.reveal || 'up';
        const from = {
            up: { y: 36, opacity: 0, filter: 'blur(8px)' },
            fade: { opacity: 0, filter: 'blur(6px)' },
            left: { x: -36, opacity: 0, filter: 'blur(8px)' },
            scale: { opacity: 0, scale: 0.94, filter: 'blur(10px)' },
            clip: { opacity: 1, clipPath: 'inset(8% 8% 8% 8% round 28px)' },
        }[type] ?? { y: 36, opacity: 0, filter: 'blur(8px)' };

        const to = {
            y: 0,
            x: 0,
            scale: 1,
            opacity: 1,
            filter: 'blur(0px)',
            ...(type === 'clip' ? { clipPath: 'inset(0% 0% 0% 0% round 28px)' } : {}),
            duration: type === 'clip' ? 1.6 : 1.2,
            ease: type === 'clip' ? 'expo.inOut' : 'expo.out',
            delay: Number(el.dataset.revealDelay || 0),
            clearProps: 'filter',
        };

        gsap.fromTo(el, from, {
            ...to,
            scrollTrigger: { trigger: el, start: 'top 88%', once: true },
        });
    });
}

/** Fluid word-by-word blur-in for headlines. */
export function blurWords(el, { delay = 0, stagger = 0.045, scroll = false } = {}) {
    if (!el) return;
    if (prefersReducedMotion()) {
        gsap.set(el, { opacity: 1 });
        return;
    }
    const run = () => {
        if (!el.isConnected) return;
        const split = SplitText.create(el, { type: 'words', wordsClass: 'inline-block' });
        gsap.set(el, { opacity: 1 });
        gsap.fromTo(split.words, { opacity: 0, y: '0.35em', filter: 'blur(12px)' }, {
            opacity: 1,
            y: 0,
            filter: 'blur(0px)',
            duration: 1.3,
            stagger,
            delay,
            ease: 'expo.out',
            onComplete: () => split.revert(),
            ...(scroll ? { scrollTrigger: { trigger: el, start: 'top 85%', once: true } } : {}),
        });
    };
    document.fonts?.status === 'loaded' ? run() : document.fonts.ready.then(run);
}

/** Split a heading into lines and slide them up out of masks. */
export function splitLines(el, { delay = 0, stagger = 0.09, duration = 1.25, scroll = false } = {}) {
    if (!el) return null;
    if (prefersReducedMotion()) {
        gsap.set(el, { opacity: 1 });
        return null;
    }

    // Wait for web fonts, otherwise lines are measured with the fallback font.
    const run = () => {
        if (!el.isConnected) return;
        const split = SplitText.create(el, { type: 'lines', mask: 'lines', linesClass: 'split-line' });
        gsap.set(el, { opacity: 1 });
        gsap.from(split.lines, {
            yPercent: 110,
            duration,
            stagger,
            delay,
            ease: 'expo.out',
            onComplete: () => split.revert(),
            ...(scroll ? { scrollTrigger: { trigger: el, start: 'top 85%', once: true } } : {}),
        });
    };

    document.fonts?.status === 'loaded' ? run() : document.fonts.ready.then(run);
    return null;
}

export { gsap, ScrollTrigger, SplitText, Flip };
