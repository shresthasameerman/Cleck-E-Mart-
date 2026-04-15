<?php
// Reuses site-wide header/navigation to keep contact page in the same theme.
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="contact-page">

    <!-- Page intro: mirrors the auth-intro pattern used on auth.php -->
    <section class="contact-intro" aria-labelledby="contact-title">
        <div class="container contact-intro__inner">
            <p class="contact-intro__eyebrow">We'd love to hear from you</p>
            <h1 id="contact-title">Get in Touch</h1>
            <p class="contact-intro__sub">Have a question about an order, a product, or want to become a trader? Send us a message and we'll get back to you shortly.</p>
        </div>
    </section>

    <!-- Two-column layout: info left, form right -->
    <section class="contact" aria-label="Contact details and message form">
        <div class="container">
            <div class="contact-grid">

                <!-- LEFT: Contact information cards -->
                <aside class="contact-info" aria-label="Contact information">

                    <div class="contact-card">
                        <p class="contact-card__eyebrow">Contact Information</p>
                        <p class="contact-card__body">Our team is available during collection hours to assist customers and traders alike.</p>
                    </div>

                    <div class="contact-card">
                        <!--
                            Location icon: map-pin style SVG.
                            Update the address text when a real address is available.
                        -->
                        <div class="contact-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21c-4-4.5-7-8-7-11a7 7 0 0 1 14 0c0 3-3 6.5-7 11z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </div>
                        <div>
                            <strong class="contact-card__label">Location</strong>
                            <p class="contact-card__detail">123 Market Street, Kathmandu</p>
                            <p class="contact-card__detail contact-card__detail--muted">Bagmati Province, Nepal</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <!--
                            Email icon: envelope style SVG.
                            Replace the href and display text with the real support address.
                        -->
                        <div class="contact-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="3"/>
                                <path d="M2 8l10 6 10-6"/>
                            </svg>
                        </div>
                        <div>
                            <strong class="contact-card__label">Email</strong>
                            <p class="contact-card__detail">
                                <a href="mailto:support@cleckmart.com">support@cleckmart.com</a>
                            </p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <!--
                            Clock icon: opening hours.
                            Update day/time ranges to reflect real collection schedule.
                        -->
                        <div class="contact-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 3"/>
                            </svg>
                        </div>
                        <div>
                            <strong class="contact-card__label">Collection Hours</strong>
                            <p class="contact-card__detail">Mon – Fri: 9:00 AM – 6:00 PM</p>
                            <p class="contact-card__detail contact-card__detail--muted">Sat: 10:00 AM – 4:00 PM</p>
                        </div>
                    </div>

                </aside>

                <!-- RIGHT: Contact form -->
                <section class="contact-form-wrap" aria-labelledby="form-title">
                    <h2 id="form-title" class="contact-form-wrap__title">Send a Message</h2>

                    <!--
                        Contact form backend integration guide:
                        - Set action to your mail endpoint (example: contact-handler.php)
                        - Sanitize all inputs server-side before sending or storing
                        - Required fields: first_name, last_name, email, subject, message
                        - Consider a honeypot field or CSRF token for spam protection
                    -->
                    <form class="contact-form" action="#" method="post" novalidate>

                        <div class="contact-form__grid">
                            <label>
                                <span>First Name*</span>
                                <input type="text" name="first_name" required autocomplete="given-name" placeholder="Enter first name" />
                            </label>
                            <label>
                                <span>Last Name*</span>
                                <input type="text" name="last_name" required autocomplete="family-name" placeholder="Enter last name" />
                            </label>
                        </div>

                        <label>
                            <span>Email*</span>
                            <input type="email" name="email" required autocomplete="email" placeholder="name@example.com" />
                        </label>

                        <label>
                            <span>Subject*</span>
                            <input type="text" name="subject" required placeholder="What is your message about?" />
                        </label>

                        <label>
                            <span>Message*</span>
                            <textarea name="message" required rows="5" placeholder="Write your message here…"></textarea>
                        </label>

                        <button class="contact-submit" type="submit">
                            Send Message
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </button>

                    </form>
                </section>

            </div>
        </div>
    </section>

</main>
<?php
// Shared footer keeps legal/quick links unified across pages.
require __DIR__ . '/components/footer.php';
?>
