<?php $footerAccountHref = current_role() === 'TRADER' ? 'trader-dashboard.php' : 'profile.php'; ?>
<!-- Site footer -->
<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__brand-block">
            <a class="brand brand--footer" href="index.php">Cleck E-Mart</a>
            <p class="site-footer__intro">A clean market experience with quick browsing, simple checkout, and clear support when you need it.</p>
            <a class="site-footer__contact" href="mailto:support@cleckmart.com">support@cleckmart.com</a>
        </div>

        <div class="site-footer__links" aria-label="Footer links">
            <div class="site-footer__group">
                <p class="site-footer__label">Shop</p>
                <a href="index.php#featured-title">Featured</a>
                <a href="category.php">Browse Category</a>
                <a href="cart.php">Your Basket</a>
            </div>

            <div class="site-footer__group">
                <p class="site-footer__label">Support</p>
                <a href="contact.php">Contact Us</a>
                <a href="<?php echo e($footerAccountHref); ?>">My Profile</a>
            </div>
        </div>

        <div class="site-footer__bottom">
            <p>&copy; 2026 Cleck E-Mart. Built for a focused shopping flow.</p>
        </div>
    </div>
</footer>

<button class="scroll-to-top" type="button" aria-label="Back to top" data-scroll-top>
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M12 17V7M7.5 11.5 12 7l4.5 4.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>
</body>
</html>
