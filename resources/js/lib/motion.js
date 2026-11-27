import gsap from 'gsap';

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

/** True when the visitor asked the system to keep motion still. */
export function prefersReducedMotion() {
    return reduced.matches;
}

/**
 * Drive the welcome-screen background. CSS keeps the same motion when
 * JavaScript has not taken over, and both stay still under reduced motion.
 */
export function animateSplash(root = document) {
    const glows = [...root.querySelectorAll('.splash-glow')];
    const mosque = root.querySelector('.splash-scene img');
    const mist = root.querySelector('.splash-mist');
    if (!glows.length && !mosque && !mist) {
        return;
    }
    if (prefersReducedMotion()) {
        return;
    }

    document.documentElement.classList.add('gsap-ready');

    glows.forEach((el, index) => {
        const centered = el.classList.contains('-translate-x-1/2');
        const x = centered ? -50 : 0;
        gsap.fromTo(el, { xPercent: x, yPercent: 0, scale: 1 }, {
            xPercent: x - 2,
            yPercent: 1.5,
            scale: 1.06,
            duration: 11,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
            delay: index === 0 ? 0 : -4,
        });
    });

    if (mosque) {
        gsap.fromTo(mosque, { xPercent: 0, yPercent: 0, scale: 1 }, {
            xPercent: -2,
            yPercent: 1.5,
            scale: 1.06,
            duration: 13,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
        });
    }

    if (mist) {
        gsap.fromTo(mist, { opacity: 0.45 }, {
            opacity: 1,
            duration: 3.5,
            ease: 'sine.inOut',
            yoyo: true,
            repeat: -1,
        });
    }
}

reduced.addEventListener('change', () => {
    if (!reduced.matches) {
        return;
    }
    gsap.killTweensOf('.splash-glow, .splash-scene img, .splash-mist');
    document.documentElement.classList.remove('gsap-ready');
});

export { gsap };
