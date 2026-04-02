document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('[data-nav]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (!nav || !toggle || !panel) {
        return;
    }

    const setExpanded = (isOpen) => {
        nav.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
    };

    toggle.addEventListener('click', () => {
        const isOpen = nav.classList.contains('is-open');
        setExpanded(!isOpen);
    });

    panel.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            setExpanded(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (!nav.contains(event.target)) {
            setExpanded(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setExpanded(false);
        }
    });
});
