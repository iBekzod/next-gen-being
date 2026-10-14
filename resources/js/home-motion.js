/**
 * home-motion.js  –  Tasteful motion for the NextGenBeing landing page (#171)
 *
 * Strategy
 * --------
 * • The hero already has CSS `ngb-reveal` / `ngbRise` for load animations and
 *   an IntersectionObserver-driven `ngb-io` system for scroll reveals.
 * • This module layers *additional* polish on top:
 *     1. Staggered per-card reveal for post article cards in the Featured section.
 *     2. Staggered reveal for category / grid cards.
 *     3. Smooth hover lift on article cards and feature cards (CSS injection).
 *     4. Trust block fade-up on scroll.
 *
 * Rules
 * -----
 * • animate transform / opacity ONLY  →  no layout shift, GPU-composited.
 * • `prefers-reduced-motion: reduce`  →  all animations skipped.
 * • Only runs on `#ngb-landing` (home page guard).
 * • Tree-shaken: only `animate` + `inView` imported from motion.
 */

import { animate, inView } from 'motion';

(function ngbHomeMotion() {
    // -----------------------------------------------------------------------
    // Guard: home page only
    // -----------------------------------------------------------------------
    const landing = document.getElementById('ngb-landing');
    if (!landing) return;

    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // -----------------------------------------------------------------------
    // 1. Staggered article card reveal (Featured Articles section)
    // -----------------------------------------------------------------------
    function initArticleCards() {
        const cards = landing.querySelectorAll('section article');
        if (!cards.length) return;

        if (reduced) {
            cards.forEach(function (el) { el.style.opacity = '1'; el.style.transform = 'none'; });
            return;
        }

        cards.forEach(function (card, i) {
            card.style.opacity = '0';
            card.style.transform = 'translateY(22px)';
            card.style.willChange = 'transform, opacity';

            inView(card, function () {
                animate(
                    card,
                    { opacity: [0, 1], transform: ['translateY(22px)', 'none'] },
                    { duration: 0.55, delay: (i % 4) * 0.1, easing: [0.16, 1, 0.3, 1] }
                );
                setTimeout(function () { card.style.willChange = 'auto'; }, 900);
                return function () {}; // one-shot
            }, { amount: 0.12 });
        });
    }

    // -----------------------------------------------------------------------
    // 2. Staggered category / grid card reveal
    // -----------------------------------------------------------------------
    function initGridCards() {
        if (reduced) return;

        // Category cards (.grid.md:grid-cols-3 > div) and feature cards (#features .group)
        var selectors = [
            'section .grid > div.group',
            '#features .group',
        ];
        var seen = new WeakSet();

        selectors.forEach(function (sel) {
            landing.querySelectorAll(sel).forEach(function (card, i) {
                if (seen.has(card)) return;
                seen.add(card);

                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.willChange = 'transform, opacity';

                inView(card, function () {
                    animate(
                        card,
                        { opacity: [0, 1], transform: ['translateY(20px)', 'none'] },
                        { duration: 0.5, delay: (i % 3) * 0.09, easing: [0.16, 1, 0.3, 1] }
                    );
                    setTimeout(function () { card.style.willChange = 'auto'; }, 800);
                    return function () {};
                }, { amount: 0.1 });
            });
        });
    }

    // -----------------------------------------------------------------------
    // 3. "Why readers stay" numbered feature items — stagger on scroll
    // -----------------------------------------------------------------------
    function initFeatureItems() {
        if (reduced) return;

        landing.querySelectorAll('section .group.relative.pb-1').forEach(function (item, i) {
            item.style.opacity = '0';
            item.style.transform = 'translateY(16px)';
            item.style.willChange = 'transform, opacity';

            inView(item, function () {
                animate(
                    item,
                    { opacity: [0, 1], transform: ['translateY(16px)', 'none'] },
                    { duration: 0.48, delay: (i % 3) * 0.08, easing: [0.16, 1, 0.3, 1] }
                );
                setTimeout(function () { item.style.willChange = 'auto'; }, 750);
                return function () {};
            }, { amount: 0.12 });
        });
    }

    // -----------------------------------------------------------------------
    // 4. Trust / authority icon blocks — fade up on scroll
    // -----------------------------------------------------------------------
    function initTrustBlocks() {
        if (reduced) return;

        landing.querySelectorAll('.mt-16.pt-12 .text-center').forEach(function (block, i) {
            block.style.opacity = '0';
            block.style.transform = 'translateY(14px)';
            block.style.willChange = 'transform, opacity';

            inView(block, function () {
                animate(
                    block,
                    { opacity: [0, 1], transform: ['translateY(14px)', 'none'] },
                    { duration: 0.5, delay: i * 0.1, easing: [0.16, 1, 0.3, 1] }
                );
                setTimeout(function () { block.style.willChange = 'auto'; }, 850);
                return function () {};
            }, { amount: 0.2 });
        });
    }

    // -----------------------------------------------------------------------
    // 5. Hover micro-states — injected once as a <style> tag (zero runtime cost)
    // -----------------------------------------------------------------------
    function injectHoverStyles() {
        if (document.getElementById('ngb-motion-hover-styles')) return;
        var style = document.createElement('style');
        style.id = 'ngb-motion-hover-styles';
        style.textContent = [
            /* Post/article card lift */
            '#ngb-landing section article {',
            '  transition: transform 0.28s cubic-bezier(.2,.9,.2,1), box-shadow 0.28s;',
            '}',
            '#ngb-landing section article:hover {',
            '  transform: translateY(-3px);',
            '  box-shadow: 0 10px 28px rgba(0,0,0,0.10);',
            '}',
            /* Dark-bg feature cards glow on hover */
            '#ngb-landing #features .group {',
            '  transition: transform 0.28s cubic-bezier(.2,.9,.2,1), border-color 0.28s, box-shadow 0.28s;',
            '}',
            '#ngb-landing #features .group:hover {',
            '  transform: translateY(-2px);',
            '  box-shadow: 0 8px 24px rgba(96,130,255,0.18);',
            '}',
            /* Policy/legal link cards (reinforce Tailwind hover) */
            '#ngb-landing a.group.block.rounded-2xl {',
            '  transition: transform 0.28s cubic-bezier(.2,.9,.2,1), box-shadow 0.28s, border-color 0.28s;',
            '}',
            /* Reduced-motion override */
            '@media (prefers-reduced-motion: reduce) {',
            '  #ngb-landing section article,',
            '  #ngb-landing #features .group,',
            '  #ngb-landing a.group.block.rounded-2xl {',
            '    transition: none !important;',
            '    transform: none !important;',
            '  }',
            '}',
        ].join('\n');
        document.head.appendChild(style);
    }

    // -----------------------------------------------------------------------
    // Boot — wait for DOM, then a rAF so first paint is never blocked
    // -----------------------------------------------------------------------
    function boot() {
        injectHoverStyles();
        requestAnimationFrame(function () {
            initArticleCards();
            initGridCards();
            initFeatureItems();
            initTrustBlocks();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
}());
