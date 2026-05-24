<?php
// This script processes form submissions when a customer leaves a rating and review for a purchased product.

$pageTitle = 'Write a Review | Cleck E-Mart';
$metaDescription = 'Review a product you purchased on Cleck E-Mart.';
require_once __DIR__ . '/lib/auth_helpers.php';
require_once __DIR__ . '/lib/oci_db.php';
require_once __DIR__ . '/lib/offline_store.php';

require_login(['CUSTOMER']);

$customerId = (int) current_customer_id();
$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$error = null;
$success = get_flash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
    $comment = trim($_POST['comment'] ?? '');

    if ($productId === false || $productId === null || $rating === false || $rating === null || $rating < 1 || $rating > 5) {
        $error = 'Please provide a valid rating between 1 and 5 stars.';
    } else {
        try {
            if (db_is_offline()) {
                offline_submit_review($customerId, $productId, $rating, $comment);
            } else {
                $reviewId = db_next_id('REVIEW', 'review_id');
                db_execute(
                    'INSERT INTO REVIEW (review_id, customer_id, product_id, rating, "COMMENT", review_date) 
                     VALUES (:review_id, :customer_id, :product_id, :rating, :comment, SYSDATE)',
                    [
                        'review_id' => $reviewId,
                        'customer_id' => $customerId,
                        'product_id' => $productId,
                        'rating' => $rating,
                        'comment' => $comment === '' ? null : $comment
                    ]
                );
            }
            
            set_flash('success', 'Your review has been submitted successfully.');
            redirect("product.php?product_id={$productId}");
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'unique constraint')) {
                $error = 'You have already reviewed this product.';
            } elseif (str_contains($msg, 'ORA-20009') || str_contains($msg, 'collected/paid for')) {
                $error = 'You can only review products you have purchased and collected/paid for.';
            } else {
                $error = 'Failed to submit review: ' . $msg;
            }
        }
    }
}

$product = null;
if ($productId !== false && $productId !== null) {
    if (db_is_offline()) {
        $product = offline_get_product_detail((int) $productId);
    } else {
        $product = db_fetch_one(
            'SELECT product_id, product_name, product_image FROM PRODUCT WHERE product_id = :product_id',
            ['product_id' => $productId]
        );
    }
}

if ($product === null) {
    set_flash('error', 'Product not found.');
    redirect('order-history.php');
}

require __DIR__ . '/components/header.php';
?>
<main id="main-content" class="auth-page">
    <div class="container auth-container" style="max-width: 600px;">
        <section class="auth-section">
            <h1 class="auth-title">Write a Review</h1>
            <p class="auth-description">Tell us what you thought about <strong><?php echo e($product['PRODUCT_NAME']); ?></strong>.</p>

            <?php if ($error !== null): ?>
                <div class="page-message page-message--error" role="alert"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="product-review.php?product_id=<?php echo $productId; ?>">
                <input type="hidden" name="product_id" value="<?php echo $productId; ?>" />

                <div class="form-group">
                    <label>Rating</label>
                    <div style="display: flex; gap: 1rem; align-items: center; margin-top: 0.5rem; font-size: 1.5rem; color: #fbbf24;">
                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                            <input type="radio" name="rating" value="1" required style="margin-bottom: 0.25rem;" />
                            <span>1&#9733;</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                            <input type="radio" name="rating" value="2" required style="margin-bottom: 0.25rem;" />
                            <span>2&#9733;</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                            <input type="radio" name="rating" value="3" required style="margin-bottom: 0.25rem;" />
                            <span>3&#9733;</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                            <input type="radio" name="rating" value="4" required style="margin-bottom: 0.25rem;" />
                            <span>4&#9733;</span>
                        </label>
                        <label style="display: flex; flex-direction: column; align-items: center; cursor: pointer;">
                            <input type="radio" name="rating" value="5" required style="margin-bottom: 0.25rem;" />
                            <span>5&#9733;</span>
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="review-comment">Review (Optional)</label>
                    <textarea id="review-comment" name="comment" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px;"></textarea>
                </div>

                <button class="button auth-submit" type="submit" style="margin-top: 1.5rem;">Submit Review</button>
            </form>
            
            <div class="auth-footer" style="text-align: center; margin-top: 2rem;">
                <p><a href="order-history.php" style="color: var(--primary); text-decoration: underline;">Back to Orders</a></p>
            </div>
        </section>
    </div>
</main>
<?php require __DIR__ . '/components/footer.php'; ?>
