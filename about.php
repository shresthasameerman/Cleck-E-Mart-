<?php
require_once __DIR__ . '/lib/bootstrap.php';
$pageTitle = 'About Us | Cleck E-Mart';
$metaDescription = 'Learn more about Cleck E-Mart, our locations, and how to get in touch with us.';
require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="about-page">

    <!-- Hero / Intro Section -->
    <section class="about-hero" aria-labelledby="about-title">
        <div class="container about-hero__inner">
            <p class="about-hero__eyebrow">Who We Are</p>
            <h1 id="about-title">About Cleck E-Mart</h1>
            <p class="about-hero__sub">We are your local hub for fresh, high-quality products sourced directly from independent traders. Cleck E-Mart is committed to bringing the community together through a shared love for great food and reliable service.</p>
        </div>
    </section>

    <!-- Information & Location Grid -->
    <section class="about-content" aria-label="Shop information and locations">
        <div class="container">
            <div class="about-grid">
                
                <!-- Left: Text content -->
                <div class="about-text">
                    <h2>Our Mission</h2>
                    <p>At Cleck E-Mart, we believe in supporting local businesses and providing our customers with a seamless, modern shopping experience. From fresh organic vegetables and artisan bakery goods to premium quality meats, we partner with the best traders to ensure you get nothing but excellence.</p>
                    
                    <h2>Contact Information</h2>
                    <ul class="about-contact-list">
                        <li>
                            <strong>Address:</strong> 123 Market Street, Kathmandu, Bagmati Province, Nepal
                        </li>
                        <li>
                            <strong>Email:</strong> <a href="mailto:support@cleckmart.com">support@cleckmart.com</a>
                        </li>
                        <li>
                            <strong>Phone:</strong> +977 1-2345678
                        </li>
                    </ul>

                    <h2>Collection Hours</h2>
                    <p>Orders can be collected at our main hub during the following time slots:</p>
                    <ul class="about-hours-list">
                        <li>Wednesday – Friday</li>
                        <li><strong>Morning:</strong> 10:00 AM – 1:00 PM</li>
                        <li><strong>Afternoon:</strong> 1:00 PM – 4:00 PM</li>
                        <li><strong>Evening:</strong> 4:00 PM – 7:00 PM</li>
                    </ul>
                    
                    <div style="margin-top: 2rem;">
                        <a href="contact.php" class="button button--primary">Send us a message</a>
                    </div>
                </div>

                <!-- Right: Map embed -->
                <div class="about-map">
                    <h2>Live Location</h2>
                    <p class="about-map-desc">Find our collection hub located in the heart of the city.</p>
                    <div class="map-responsive">
                        <!-- Google Maps iframe for Kathmandu -->
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d56516.2768919741!2d85.28493297686524!3d27.70831700000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb198a307baabf%3A0xb5137c1bf18db1ea!2sKathmandu%2044600%2C%20Nepal!5e0!3m2!1sen!2s!4v1700000000000!5m2!1sen!2s" 
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

            </div>
        </div>
    </section>

</main>

<style>
    .about-page {
        padding-bottom: 4rem;
    }

    .about-hero {
        background-color: rgba(106, 136, 97, 0.05);
        padding: 4rem 0;
        text-align: center;
        margin-bottom: 3rem;
    }

    .about-hero__eyebrow {
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.1em;
        color: #6a8861;
        margin-bottom: 0.5rem;
    }

    .about-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: #1a1a1a;
    }

    .about-hero__sub {
        max-width: 800px;
        margin: 0 auto;
        font-size: 1.1rem;
        line-height: 1.6;
        color: rgba(26, 26, 26, 0.7);
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
    }

    .about-text h2 {
        font-size: 1.75rem;
        margin-bottom: 1rem;
        color: #1a1a1a;
    }

    .about-text p {
        line-height: 1.7;
        margin-bottom: 2rem;
        color: rgba(26, 26, 26, 0.8);
    }

    .about-contact-list, .about-hours-list {
        list-style: none;
        padding: 0;
        margin: 0 0 2rem 0;
    }

    .about-contact-list li, .about-hours-list li {
        margin-bottom: 0.75rem;
        font-size: 1.05rem;
        color: rgba(26, 26, 26, 0.8);
    }

    .about-contact-list a {
        color: #6a8861;
        text-decoration: underline;
    }

    .about-map h2 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .about-map-desc {
        margin-bottom: 1.5rem;
        color: rgba(26, 26, 26, 0.7);
    }

    .map-responsive {
        overflow: hidden;
        padding-bottom: 56.25%;
        position: relative;
        height: 0;
    }

    .map-responsive iframe {
        left: 0;
        top: 0;
        height: 100%;
        width: 100%;
        position: absolute;
    }

    @media (max-width: 992px) {
        .about-grid {
            grid-template-columns: 1fr;
            gap: 3rem;
        }
        .map-responsive {
            padding-bottom: 75%; /* More square on mobile */
        }
    }
</style>

<?php
require __DIR__ . '/components/footer.php';
?>
