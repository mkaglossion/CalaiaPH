@once
<style>
    [x-cloak]{display:none!important;}
    .cards-grid-responsive {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
        width: 100%;
    }
    @media (max-width: 767px) {
        .mobile-layout-mode-1 .cards-grid-responsive {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .mobile-layout-mode-2 .cards-grid-responsive {
            grid-template-columns: minmax(0, 1fr);
        }
        .mobile-layout-mode-3 .cards-grid-responsive {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 0;
            padding-bottom: 0;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .mobile-layout-mode-3 .cards-grid-responsive::-webkit-scrollbar {
            display: none;
        }
        .mobile-layout-mode-3 .cards-grid-responsive > * {
            flex: 0 0 100%;
            max-width: 100%;
            scroll-snap-align: start;
        }
        .mobile-layout-mode-3 .mobile-carousel-dots {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.75rem;
        }
        .mobile-layout-mode-3 .mobile-carousel-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background: #cbd5e1;
            border: 0;
            padding: 0;
            transition: all 0.2s ease;
        }
        .mobile-layout-mode-3 .mobile-carousel-dot.is-active {
            width: 1.125rem;
            background: #8f5d54;
        }
    }
    @media (min-width: 640px) {
        .cards-grid-responsive { gap: 1rem; }
    }
    @media (min-width: 768px) {
        .cards-grid-responsive { gap: 1.5rem; }
    }
</style>

<script>
    function initMobileCardCarousels() {
        if (!window.matchMedia('(max-width: 767px)').matches) return;
        const root = document.querySelector('.mobile-layout-mode-3');
        if (!root) return;

        const removeDots = (track) => {
            const next = track.nextElementSibling;
            if (next && next.classList.contains('mobile-carousel-dots')) {
                next.remove();
            }
            delete track.dataset.mobileCarouselReady;
        };

        const getSlideWidth = (track) => {
            const firstCard = track.querySelector(':scope > *');
            if (!firstCard) return track.clientWidth || 1;
            const trackStyles = getComputedStyle(track);
            const gapValue = parseFloat(trackStyles.columnGap || trackStyles.gap || '0') || 0;
            return firstCard.getBoundingClientRect().width + gapValue;
        };

        const syncActiveDot = (track) => {
            const dots = track.nextElementSibling;
            if (!dots || !dots.classList.contains('mobile-carousel-dots')) return;
            const slideWidth = getSlideWidth(track);
            if (!slideWidth) return;

            const maxIndex = dots.children.length - 1;
            const activeIndex = Math.min(maxIndex, Math.max(0, Math.round(track.scrollLeft / slideWidth)));

            Array.from(dots.children).forEach((dot, index) => {
                dot.classList.toggle('is-active', index === activeIndex);
            });
        };

        root.querySelectorAll('.cards-grid-responsive').forEach((track) => {
            const cards = Array.from(track.children).filter((node) => node.nodeType === 1);
            if (cards.length <= 1) {
                removeDots(track);
                return;
            }

            const existingDots = track.nextElementSibling;
            const needsFreshDots = !existingDots
                || !existingDots.classList.contains('mobile-carousel-dots')
                || Number(track.dataset.mobileCarouselCardCount || 0) !== cards.length;

            if (needsFreshDots) {
                removeDots(track);

                const dots = document.createElement('div');
                dots.className = 'mobile-carousel-dots';
                dots.setAttribute('aria-label', 'Carousel pagination');

                cards.forEach((_, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `mobile-carousel-dot${index === 0 ? ' is-active' : ''}`;
                    button.setAttribute('aria-label', `Go to card ${index + 1}`);
                    button.addEventListener('click', () => {
                        track.scrollTo({
                            left: index * getSlideWidth(track),
                            behavior: 'smooth'
                        });
                    });
                    dots.appendChild(button);
                });

                track.insertAdjacentElement('afterend', dots);

                if (track.dataset.mobileCarouselReady !== '1') {
                    track.addEventListener('scroll', () => syncActiveDot(track), { passive: true });
                    window.addEventListener('resize', () => syncActiveDot(track));
                }
            }

            track.scrollLeft = 0;
            requestAnimationFrame(() => {
                track.scrollLeft = 0;
                syncActiveDot(track);
            });
            track.dataset.mobileCarouselReady = '1';
            track.dataset.mobileCarouselCardCount = String(cards.length);
        });
    }

    document.addEventListener('DOMContentLoaded', initMobileCardCarousels);
    document.addEventListener('alpine:initialized', initMobileCardCarousels);
    window.addEventListener('load', initMobileCardCarousels);
    window.addEventListener('pageshow', initMobileCardCarousels);
</script>
@endonce
