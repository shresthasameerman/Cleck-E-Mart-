// Navigation controller:
// Handles opening/closing the mobile menu, click-outside closing, and Escape key support.
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('[data-nav]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');

    if (!nav || !toggle || !panel) {
        // Safe no-op for pages that do not include the nav structure.
        return;
    }

    const setExpanded = (isOpen) => {
        // One helper keeps visual state and ARIA state in sync.
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

// Auth tabs controller:
// Switches between Sign Up and Login panels in auth.php.
document.addEventListener('DOMContentLoaded', () => {
    const authCard = document.querySelector('[data-auth-card]');
    const switches = document.querySelectorAll('[data-auth-switch]');
    const panels = document.querySelectorAll('[data-auth-panel]');

    if (!authCard || switches.length === 0 || panels.length === 0) {
        // Safe no-op for pages without auth UI.
        return;
    }

    // Keeps tab button states, ARIA attributes, and panel visibility in sync.
    const activateMode = (mode) => {
        switches.forEach((switchButton) => {
            const isActive = switchButton.getAttribute('data-auth-switch') === mode;

            switchButton.classList.toggle('is-active', isActive);
            if (switchButton.getAttribute('role') === 'tab') {
                switchButton.setAttribute('aria-selected', String(isActive));
                switchButton.setAttribute('tabindex', isActive ? '0' : '-1');
            }
        });

        panels.forEach((panel) => {
            const isActive = panel.getAttribute('data-auth-panel') === mode;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
    };

    switches.forEach((switchButton) => {
        switchButton.addEventListener('click', () => {
            activateMode(switchButton.getAttribute('data-auth-switch'));
        });
    });

    // Optional deep links: auth.php?mode=login or auth.php?mode=signup
    const requestedMode = new URLSearchParams(window.location.search).get('mode');
    if (requestedMode === 'login' || requestedMode === 'signup') {
        activateMode(requestedMode);
    }
});
