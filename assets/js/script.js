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

// Scroll-to-top controller:
// Shows a floating button once the page has been scrolled and hides it near the top.
document.addEventListener('DOMContentLoaded', () => {
    const scrollTopButton = document.querySelector('[data-scroll-top]');

    if (!scrollTopButton) {
        return;
    }

    const visibleThreshold = 240;

    const updateVisibility = () => {
        scrollTopButton.classList.toggle('is-visible', window.scrollY > visibleThreshold);
    };

    scrollTopButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('scroll', updateVisibility, { passive: true });
    updateVisibility();
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
    // Profile tab controller:
// Switches between profile sections (Orders, Account, History, Reviews, Password).
document.addEventListener('DOMContentLoaded', () => {
    const navItems = document.querySelectorAll('[data-profile-tab]');
    const panels   = document.querySelectorAll('[data-profile-panel]');

    if (navItems.length === 0 || panels.length === 0) {
        // Safe no-op for pages without profile UI.
        return;
    }

    const activateTab = (tab) => {
        navItems.forEach((item) => {
            item.classList.toggle('is-active', item.getAttribute('data-profile-tab') === tab);
        });
        panels.forEach((panel) => {
            const isActive = panel.getAttribute('data-profile-panel') === tab;
            panel.classList.toggle('is-active', isActive);
            panel.hidden = !isActive;
        });
    };

    navItems.forEach((item) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            activateTab(item.getAttribute('data-profile-tab'));
        });
    });

    // Deep-link support: profile.php?tab=account
    const requestedTab = new URLSearchParams(window.location.search).get('tab');
    const validTabs = ['orders', 'account', 'history', 'reviews', 'password'];
    if (requestedTab && validTabs.includes(requestedTab)) {
        activateTab(requestedTab);
    }
    });
});

// Category page filter controller:
// Handles search input, category filter buttons, price filter buttons, and empty-state visibility.
document.addEventListener('DOMContentLoaded', () => {
    const categoryPage = document.querySelector('[data-category-page]');

    if (!categoryPage) {
        // Safe no-op when current page is not the category listing.
        return;
    }

    const searchInput = categoryPage.querySelector('[data-category-search]');
    const productCards = Array.from(categoryPage.querySelectorAll('[data-product-card]'));
    const filterButtons = Array.from(categoryPage.querySelectorAll('[data-filter-type]'));
    const emptyState = categoryPage.querySelector('[data-empty-state]');

    // Stores active filters so all controls can compose together.
    const activeFilters = {
        category: 'all',
        trader: 'all',
        price: 'all'
    };

    const setActiveButton = (type, value) => {
        filterButtons.forEach((button) => {
            if (button.getAttribute('data-filter-type') !== type) {
                return;
            }

            const isActive = button.getAttribute('data-filter-value') === value;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
    };

    const applyFilters = () => {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        productCards.forEach((card) => {
            const categoryType = card.getAttribute('data-category-type') || '';
            const traderType = card.getAttribute('data-trader-type') || '';
            const priceTier = card.getAttribute('data-price-tier') || '';
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const trader = (card.getAttribute('data-trader') || '').toLowerCase();

            const matchesCategory = activeFilters.category === 'all' || categoryType === activeFilters.category;
            const matchesTrader = activeFilters.trader === 'all' || traderType === activeFilters.trader;
            const matchesPrice = activeFilters.price === 'all' || priceTier === activeFilters.price;
            const matchesSearch = query.length === 0 || name.includes(query) || trader.includes(query);

            const isVisible = matchesCategory && matchesTrader && matchesPrice && matchesSearch;

            card.classList.toggle('is-hidden', !isVisible);
            card.setAttribute('aria-hidden', String(!isVisible));

            if (isVisible) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount > 0;
        }
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const type = button.getAttribute('data-filter-type');
            const value = button.getAttribute('data-filter-value');

            if (!type || !value) {
                return;
            }

            activeFilters[type] = value;
            setActiveButton(type, value);
            applyFilters();
        });
    });

    // Initialize active button state for both groups before first filter pass.
    setActiveButton('category', activeFilters.category);
    setActiveButton('trader', activeFilters.trader);
    setActiveButton('price', activeFilters.price);
    searchInput?.addEventListener('input', applyFilters);
    applyFilters();
});

// Cart page controller:
// Updates item quantities and keeps the summary totals in sync with the wireframe.
document.addEventListener('DOMContentLoaded', () => {
    const cartPage = document.querySelector('[data-cart-page]');

    if (!cartPage) {
        return;
    }

    const cartItems = Array.from(cartPage.querySelectorAll('[data-cart-item]'));
    const totalNode = cartPage.querySelector('[data-cart-total]');
    const summaryItemsNode = cartPage.querySelector('[data-cart-summary-items]');

    const formatCurrency = (value) => `$${value}`;

    const updateSummary = () => {
        let total = 0;

        if (summaryItemsNode) {
            summaryItemsNode.innerHTML = '';
        }

        cartItems.forEach((item) => {
            const quantityNode = item.querySelector('[data-cart-qty-value]');
            const lineTotalNode = item.querySelector('[data-cart-line-total]');
            const quantity = Number(item.getAttribute('data-cart-qty') || '0');
            const price = Number(item.getAttribute('data-cart-price') || '0');
            const name = item.getAttribute('data-cart-name') || 'Item';
            const lineTotal = quantity * price;

            total += lineTotal;

            if (quantityNode) {
                quantityNode.textContent = String(quantity);
            }

            if (lineTotalNode) {
                lineTotalNode.textContent = formatCurrency(lineTotal);
            }

            if (summaryItemsNode) {
                const line = document.createElement('p');
                line.className = 'cart-summary__line';

                const label = name.includes('Carrots') ? 'Carrots' : name.includes('Apples') ? 'Apples' : name;
                line.textContent = `${label} x${quantity} ${formatCurrency(lineTotal)}`;
                summaryItemsNode.appendChild(line);
            }
        });

        if (totalNode) {
            totalNode.textContent = formatCurrency(total);
        }
    };

    cartPage.addEventListener('click', (event) => {
        const button = event.target.closest('[data-cart-qty-action]');

        if (!button) {
            return;
        }

        const item = button.closest('[data-cart-item]');

        if (!item) {
            return;
        }

        const action = button.getAttribute('data-cart-qty-action');
        const currentQuantity = Number(item.getAttribute('data-cart-qty') || '1');
        const nextQuantity = action === 'increase' ? currentQuantity + 1 : Math.max(1, currentQuantity - 1);

        item.setAttribute('data-cart-qty', String(nextQuantity));
        updateSummary();
    });

    updateSummary();
});

// Collection page controller:
// Renders calendar months and enforces the collection-slot rules.
document.addEventListener('DOMContentLoaded', () => {
    const collectionPage = document.querySelector('[data-collection-page]');

    if (!collectionPage) {
        return;
    }

    const summaryNode = collectionPage.querySelector('[data-collection-summary]');
    const calendarNode = collectionPage.querySelector('[data-collection-calendar]');
    const slotPanelNode = collectionPage.querySelector('[data-collection-slot-panel]');
    const slotEmptyNode = collectionPage.querySelector('[data-collection-slot-empty]');
    const timeSlotsNode = collectionPage.querySelector('[data-collection-times]');
    const availabilityNode = collectionPage.querySelector('[data-collection-availability]');
    const confirmButton = collectionPage.querySelector('[data-collection-confirm]');
    const statusNode = collectionPage.querySelector('[data-collection-status]');
    const rangeNode = collectionPage.querySelector('[data-collection-range]');

    const SLOT_OPTIONS = [
        { label: '10:00-13:00', startHour: 10, endHour: 13 },
        { label: '13:00-16:00', startHour: 13, endHour: 16 },
        { label: '16:00-19:00', startHour: 16, endHour: 19 }
    ];
    const ALLOWED_DAYS = new Set([3, 4, 5]);
    const SLOT_CAPACITY = 20;
    const STORAGE_KEY = 'cleck-emart-collection-capacities';

    const earliestPickup = new Date(Date.now() + 24 * 60 * 60 * 1000);
    const selectedSlot = {
        dateKey: null,
        slotLabel: null,
        dateLabel: null,
        slotRange: null
    };

    const readCapacities = () => {
        try {
            return JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '{}');
        } catch {
            return {};
        }
    };

    const writeCapacities = (capacities) => {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(capacities));
    };

    const capacities = readCapacities();

    const pad = (value) => String(value).padStart(2, '0');

    const toDateKey = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;

    const formatMonthLabel = (date) => new Intl.DateTimeFormat('en-GB', { month: 'long', year: 'numeric' }).format(date);

    const formatDayLabel = (date) => new Intl.DateTimeFormat('en-GB', { weekday: 'short', day: 'numeric' }).format(date);

    const formatLongLabel = (date) => new Intl.DateTimeFormat('en-GB', { weekday: 'long', day: 'numeric', month: 'long' }).format(date);

    const getMonthDates = (year, month) => {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startOffset = (firstDay.getDay() + 6) % 7;
        const gridStart = new Date(year, month, 1 - startOffset);

        const dates = [];

        for (let index = 0; index < 42; index += 1) {
            const date = new Date(gridStart);
            date.setDate(gridStart.getDate() + index);
            dates.push(date);
        }

        return { firstDay, lastDay, dates };
    };

    const getSlotCapacity = (dateKey, slotLabel) => Number(capacities[`${dateKey}|${slotLabel}`] || 0);

    const setSlotCapacity = (dateKey, slotLabel, nextCount) => {
        capacities[`${dateKey}|${slotLabel}`] = nextCount;
        writeCapacities(capacities);
    };

    const canCollectOnDate = (date) => {
        const currentKey = toDateKey(date);
        const isAllowedWeekday = ALLOWED_DAYS.has(date.getDay());
        const hasAvailableSlot = SLOT_OPTIONS.some((slot) => {
            const slotStart = new Date(date.getFullYear(), date.getMonth(), date.getDate(), slot.startHour, 0, 0, 0);
            return slotStart.getTime() >= earliestPickup.getTime() && getSlotCapacity(currentKey, slot.label) < SLOT_CAPACITY;
        });

        return {
            available: isAllowedWeekday && hasAvailableSlot,
            reason: !isAllowedWeekday ? 'Only Wednesday, Thursday, and Friday are available.' : !hasAvailableSlot ? 'No slots on this day meet the 24-hour rule or capacity limit.' : '',
            dateKey: currentKey
        };
    };

    const updateSummary = () => {
        if (!summaryNode) {
            return;
        }

        if (selectedSlot.dateLabel && selectedSlot.slotLabel) {
            summaryNode.textContent = `${selectedSlot.dateLabel} • ${selectedSlot.slotLabel}`;
            return;
        }

        summaryNode.textContent = 'Choose a day and time';
    };

    const updateStatus = (message, isError = false) => {
        if (!statusNode) {
            return;
        }

        statusNode.textContent = message;
        statusNode.classList.toggle('is-error', isError);
    };

    const updateAvailability = (message, isVisible) => {
        if (availabilityNode) {
            availabilityNode.textContent = message;
            availabilityNode.hidden = !isVisible;
        }

        if (slotEmptyNode) {
            slotEmptyNode.hidden = isVisible;
        }

        if (timeSlotsNode) {
            timeSlotsNode.hidden = !isVisible;
        }
    };

    const clearSelectedSlots = () => {
        collectionPage.querySelectorAll('.is-active').forEach((button) => {
            button.classList.remove('is-active');
            button.setAttribute('aria-pressed', 'false');
        });
    };

    const renderTimeSlots = (date) => {
        if (!timeSlotsNode) {
            return;
        }

        timeSlotsNode.innerHTML = '';

        const currentDateKey = toDateKey(date);
        let availableSlotCount = 0;

        SLOT_OPTIONS.forEach((slot) => {
            const slotStart = new Date(date.getFullYear(), date.getMonth(), date.getDate(), slot.startHour, 0, 0, 0);
            const slotEnd = new Date(date.getFullYear(), date.getMonth(), date.getDate(), slot.endHour, 0, 0, 0);
            const currentCount = getSlotCapacity(currentDateKey, slot.label);
            const isTooSoon = slotStart.getTime() < earliestPickup.getTime();
            const isFull = currentCount >= SLOT_CAPACITY;
            const isDisabled = isTooSoon || isFull;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'collection-option collection-option--slot';
            button.dataset.collectionTime = '';
            button.dataset.slotLabel = slot.label;
            button.dataset.slotStart = slotStart.toISOString();
            button.dataset.slotEnd = slotEnd.toISOString();
            button.textContent = `${slot.label} (${currentCount}/${SLOT_CAPACITY})`;
            button.disabled = isDisabled;
            button.setAttribute('aria-pressed', 'false');
            button.setAttribute('aria-label', `${slot.label}, ${currentCount} of ${SLOT_CAPACITY} orders booked`);

            if (isDisabled) {
                button.classList.add('is-disabled');
                button.title = isFull ? 'This slot is full.' : 'This slot is not available yet.';
            } else {
                availableSlotCount += 1;
            }

            button.addEventListener('click', () => {
                clearSelectedSlots();
                button.classList.add('is-active');
                button.setAttribute('aria-pressed', 'true');
                selectedSlot.slotLabel = slot.label;
                selectedSlot.slotRange = `${slot.label}`;
                updateSummary();
                updateStatus(`Selected ${formatLongLabel(date)} at ${slot.label}.`);
                if (confirmButton) {
                    confirmButton.disabled = false;
                }
            });

            timeSlotsNode.appendChild(button);
        });

        if (availabilityNode) {
            availabilityNode.textContent = availableSlotCount > 0 ? `${availableSlotCount} slot${availableSlotCount === 1 ? '' : 's'} available for ${formatLongLabel(date)}.` : `No slots are available for ${formatLongLabel(date)}.`;
            availabilityNode.hidden = false;
        }

        if (slotEmptyNode) {
            slotEmptyNode.hidden = availableSlotCount > 0;
            if (!availableSlotCount) {
                slotEmptyNode.textContent = 'No collection slots are available for this day.';
            }
        }

        if (timeSlotsNode) {
            timeSlotsNode.hidden = availableSlotCount === 0;
        }
    };

    const renderMonth = (date) => {
        if (!calendarNode) {
            return;
        }

        const year = date.getFullYear();
        const month = date.getMonth();
        const { firstDay, lastDay, dates } = getMonthDates(year, month);
        const monthWrap = document.createElement('section');
        monthWrap.className = 'collection-calendar__month-card';

        const heading = document.createElement('h3');
        heading.className = 'collection-calendar__month-title';
        heading.textContent = formatMonthLabel(firstDay);

        const weekdayRow = document.createElement('div');
        weekdayRow.className = 'collection-calendar__weekdays';
        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach((label) => {
            const weekday = document.createElement('span');
            weekday.textContent = label;
            weekdayRow.appendChild(weekday);
        });

        const grid = document.createElement('div');
        grid.className = 'collection-calendar__grid';

        dates.forEach((day) => {
            const cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'collection-calendar__day';
            cell.dataset.collectionDate = '';
            cell.dataset.dateKey = toDateKey(day);
            cell.dataset.dateLabel = formatLongLabel(day);
            cell.dataset.weekday = new Intl.DateTimeFormat('en-GB', { weekday: 'long' }).format(day);
            cell.innerHTML = `<span class="collection-calendar__day-number">${day.getDate()}</span><span class="collection-calendar__day-name">${new Intl.DateTimeFormat('en-GB', { weekday: 'short' }).format(day)}</span>`;

            const inCurrentMonth = day.getMonth() === month;
            const availability = canCollectOnDate(day);
            const isAllowed = inCurrentMonth && availability.available;

            if (!inCurrentMonth) {
                cell.classList.add('is-outside');
                cell.disabled = true;
            } else if (!isAllowed) {
                cell.classList.add('is-disabled');
                cell.disabled = true;
                cell.title = availability.reason;
            }

            if (availability.available) {
                cell.classList.add('is-available');
                cell.addEventListener('click', () => {
                    collectionPage.querySelectorAll('[data-collection-date]').forEach((button) => {
                        button.classList.remove('is-active');
                        button.setAttribute('aria-pressed', 'false');
                    });

                    cell.classList.add('is-active');
                    cell.setAttribute('aria-pressed', 'true');
                    selectedSlot.dateKey = availability.dateKey;
                    selectedSlot.dateLabel = formatLongLabel(day);
                    selectedSlot.slotLabel = null;
                    selectedSlot.slotRange = null;
                    updateSummary();
                    updateStatus(`Selected ${formatLongLabel(day)}. Choose a time slot.`);
                    renderTimeSlots(day);
                    updateAvailability(`Loading slots for ${formatLongLabel(day)}.`, true);
                    if (confirmButton) {
                        confirmButton.disabled = true;
                    }
                });
            }

            grid.appendChild(cell);
        });

        monthWrap.appendChild(heading);
        monthWrap.appendChild(weekdayRow);
        monthWrap.appendChild(grid);
        calendarNode.appendChild(monthWrap);
    };

    const initialize = () => {
        if (rangeNode) {
            rangeNode.textContent = `Checkout now, collect from ${new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'long' }).format(earliestPickup)} onwards.`;
        }

        if (calendarNode) {
            calendarNode.innerHTML = '';
            renderMonth(new Date());
            const nextMonth = new Date();
            nextMonth.setMonth(nextMonth.getMonth() + 1);
            renderMonth(nextMonth);
        }

        updateAvailability('Select a Wednesday, Thursday, or Friday on the calendar to view its slots.', false);
        updateSummary();
        updateStatus('Select a valid slot to continue.');

        if (confirmButton) {
            confirmButton.disabled = true;
            confirmButton.addEventListener('click', () => {
                if (!selectedSlot.dateKey || !selectedSlot.slotLabel) {
                    updateStatus('Choose a date and time before continuing.', true);
                    return;
                }

                const currentCount = getSlotCapacity(selectedSlot.dateKey, selectedSlot.slotLabel);
                if (currentCount >= SLOT_CAPACITY) {
                    updateStatus('That slot is full. Please choose another one.', true);
                    return;
                }

                setSlotCapacity(selectedSlot.dateKey, selectedSlot.slotLabel, currentCount + 1);
                updateStatus(`Reserved ${selectedSlot.dateLabel} • ${selectedSlot.slotLabel}. Proceed to payment.`);
                confirmButton.disabled = true;

                const paymentParams = new URLSearchParams({
                    slot_date: selectedSlot.dateKey,
                    slot_time: selectedSlot.slotLabel
                });
                window.location.href = `payment.php?${paymentParams.toString()}`;
            });
        }
    };

    initialize();
});

// Product page controller:
// Handles quantity +/- controls on product.php.
document.addEventListener('DOMContentLoaded', () => {
    const productPage = document.querySelector('[data-product-page]');

    if (!productPage) {
        // Safe no-op for non-product pages.
        return;
    }

    const quantityNode = productPage.querySelector('[data-product-qty-value]');

    if (!quantityNode) {
        return;
    }

    const setQuantity = (nextQuantity) => {
        // Keep quantity bounded at 1 so users cannot set invalid zero/negative values.
        quantityNode.textContent = String(Math.max(1, nextQuantity));
    };

    productPage.addEventListener('click', (event) => {
        const button = event.target.closest('[data-product-qty-action]');

        if (!button) {
            return;
        }

        const action = button.getAttribute('data-product-qty-action');
        const currentQuantity = Number(quantityNode.textContent || '1');
        const nextQuantity = action === 'increase' ? currentQuantity + 1 : currentQuantity - 1;

        setQuantity(nextQuantity);
    });
});

// Password toggle controller:
// Toggles password visibility across the site.
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const wrapper = button.closest('.password-wrapper');
            if (!wrapper) return;
            const input = wrapper.querySelector('input');
            if (!input) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = 'Hide';
            } else {
                input.type = 'password';
                button.textContent = 'Show';
            }
        });
    });
});
