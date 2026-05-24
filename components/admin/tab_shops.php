        <section id="shops" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <h3>Shops Pending Verification</h3>
                <?php
// This component renders the shop verification requests list in the admin dashboard.

if (empty($pendingShops)): ?>
                    <div class="empty-state" style="padding: 3rem; text-align: center;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <p style="margin:0; font-size: 1.1rem; font-weight: 500;">All caught up!</p>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem;">No shops are currently pending verification.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Shop Name</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Trader Info</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingShops as $shop): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;"><?php echo e($shop['SHOP_NAME']); ?></td>
                                        <td style="padding: 1rem 0;">
                                            <?php echo e($shop['FIRST_NAME'] . ' ' . $shop['LAST_NAME']); ?><br>
                                            <small style="color: var(--color-muted);"><?php echo e($shop['EMAIL']); ?></small>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <span class="status-badge status-badge--pending">
                                                <?php echo e(ucwords(str_replace('_', ' ', strtolower($shop['SHOP_STATUS'])))); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <form method="post" style="display:inline-flex; gap:0.5rem; justify-content: center;">
                                                <input type="hidden" name="shop_id" value="<?php echo $shop['SHOP_ID']; ?>">
                                                <button type="submit" name="action" value="approve_shop" class="button button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px;">Approve</button>
                                                <button type="submit" name="action" value="reject_shop" class="button button--secondary button--small" style="padding: 0.4rem 0.8rem; border-radius: 4px; background: #fee2e2; color: #991b1b; border-color: #fca5a5;">Reject</button>
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
