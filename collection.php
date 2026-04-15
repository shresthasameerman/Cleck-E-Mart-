<?php
$pageTitle = 'Collection Slot | Cleck E-Mart';
$metaDescription = 'Choose a collection day and time slot before confirming your order.';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="collection-page" data-collection-page>
    <section class="collection-hero" aria-labelledby="collection-title">
        <div class="container">
            <div class="collection-progress" aria-label="Checkout progress">
                <div class="collection-progress__step is-complete">
                    <span class="collection-progress__number">1</span>
                    <span class="collection-progress__label">Basket</span>
                </div>
                <div class="collection-progress__connector" aria-hidden="true"></div>
                <div class="collection-progress__step is-active" aria-current="step">
                    <span class="collection-progress__number">2</span>
                    <span class="collection-progress__label">Collection</span>
                </div>
                <div class="collection-progress__connector" aria-hidden="true"></div>
                <div class="collection-progress__step">
                    <span class="collection-progress__number">3</span>
                    <span class="collection-progress__label">Payment</span>
                </div>
                <div class="collection-progress__connector" aria-hidden="true"></div>
                <div class="collection-progress__step">
                    <span class="collection-progress__number">4</span>
                    <span class="collection-progress__label">Confirm</span>
                </div>
            </div>

            <div class="collection-hero__panel">
                <h1 id="collection-title">Collection Slot</h1>
                <p class="collection-hero__note">Select your preferred day and time for collection. Collections are available only on Wednesday, Thursday, and Friday, at least 24 hours after checkout.</p>
            </div>
        </div>
    </section>

    <section class="collection-content" aria-label="Collection slot selection">
        <div class="container collection-layout">
            <section class="collection-group collection-calendar" aria-labelledby="calendar-title">
                <div class="collection-group__header">
                    <h2 class="collection-group__title" id="calendar-title">Pick a Collection Day</h2>
                    <p class="collection-group__meta" data-collection-range></p>
                </div>
                <div class="collection-calendar__legend" aria-label="Calendar legend">
                    <span><strong>Available</strong></span>
                    <span><strong>Unavailable</strong></span>
                    <span><strong>Selected</strong></span>
                </div>
                <div class="collection-calendar__month" data-collection-calendar></div>
            </section>

            <section class="collection-group" aria-labelledby="time-slot-title">
                <div class="collection-group__header">
                    <h2 class="collection-group__title" id="time-slot-title">Pick a Time Slot</h2>
                    <p class="collection-group__meta">10:00-13:00, 13:00-16:00, or 16:00-19:00</p>
                </div>
                <div class="collection-slot-panel" data-collection-slot-panel>
                    <p class="collection-slot-panel__empty" data-collection-slot-empty>Select a Wednesday, Thursday, or Friday on the calendar to view its slots.</p>
                    <div class="collection-grid collection-grid--times" data-collection-times hidden></div>
                    <p class="collection-slot-panel__availability" data-collection-availability hidden></p>
                </div>
            </section>

            <div class="collection-actions">
                <p class="collection-actions__hint">Selected slot: <strong data-collection-summary>Choose a day and time</strong></p>
                <p class="collection-actions__status" data-collection-status>Select a valid slot to continue.</p>
                <button class="collection-confirm button" type="button" data-collection-confirm disabled>Confirm Slot &amp; Pay</button>
            </div>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/components/footer.php';
?>
