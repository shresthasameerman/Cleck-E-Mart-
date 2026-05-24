        <section id="products" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <h3>Products Pending Verification</h3>
                <?php
// This component renders the product moderation list in the admin dashboard.

if (empty($pendingProducts)): ?>
                    <div class="empty-state" style="padding: 3rem; text-align: center;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem;">No products are currently pending verification.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Product Name</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Shop Name</th>
                                    <th style="text-align: right; padding-bottom: 0.75rem;">Price</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingProducts as $product): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;"><?php echo e($product['PRODUCT_NAME']); ?></td>
                                        <td style="padding: 1rem 0;"><?php echo e($product['SHOP_NAME']); ?></td>
                                        <td style="text-align: right; padding: 1rem 0;">£<?php echo e(number_format((float)$product['PRICE'], 2)); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <span class="status-badge status-badge--pending">
                                                <?php echo e(ucwords(str_replace('_', ' ', strtolower($product['PRODUCT_VERIFICATION_STATUS'])))); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <form method="post" style="display:inline-flex; gap:0.5rem; justify-content: center;">
                                                <input type="hidden" name="product_id" value="<?php echo $product['PRODUCT_ID']; ?>">
                                                <button type="submit" name="action" value="approve" class="button button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px;">Approve</button>
                                                <button type="submit" name="action" value="reject" class="button button--secondary button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px; background: #fee2e2; color: #991b1b; border-color: #fca5a5;">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
