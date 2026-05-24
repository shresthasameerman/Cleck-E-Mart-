        <section id="traders" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">Trader Access</h3>
                    <span style="font-size: 0.9rem; color: var(--color-muted);">Global Account Impersonation</span>
                </div>
                
                <?php
// This component renders the list of registered traders in the admin dashboard.

if (empty($allTraders)): ?>
                    <div class="empty-state" style="padding: 3rem; text-align: center;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 1rem; opacity: 0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <p style="margin:0; font-size: 1.1rem; font-weight: 500;">No traders found</p>
                        <p style="margin-top: 0.25rem; font-size: 0.9rem;">There are no registered traders in the system yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Trader ID</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Trader Name</th>
                                    <th style="text-align: left; padding-bottom: 0.75rem;">Contact Email</th>
                                    <th style="text-align: center; padding-bottom: 0.75rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allTraders as $t): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;">#<?php echo e($t['USER_ID'] ?? $t['user_id']); ?></td>
                                        <td style="padding: 1rem 0; font-weight: 500;"><?php echo e(($t['FIRST_NAME'] ?? '') . ' ' . ($t['LAST_NAME'] ?? '')); ?></td>
                                        <td style="padding: 1rem 0; color: var(--color-muted);"><?php echo e($t['EMAIL'] ?? $t['email']); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <a href="auth.php?action=impersonate&trader_id=<?php echo e($t['USER_ID'] ?? $t['user_id']); ?>" class="button button--small" style="text-decoration: none; display: inline-block;">Access Account</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>
