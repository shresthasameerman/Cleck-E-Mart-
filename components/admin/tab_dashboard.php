        <section id="dashboard" class="tab-content active-tab" style="display: block;">
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <h3>Total Revenue</h3>
                    <p>£<?php
// This component renders the main overview tab with statistics in the admin dashboard.

echo number_format($totalRevenue, 2); ?></p>
                </div>
                <div class="metric-card">
                    <h3>Total Orders</h3>
                    <p><?php echo number_format($totalOrders); ?></p>
                </div>
                <div class="metric-card">
                    <h3>Active Traders</h3>
                    <p><?php echo number_format($activeTraders); ?></p>
                </div>
                <div class="metric-card">
                    <h3>Total Customers</h3>
                    <p><?php echo number_format($totalCustomers); ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="admin-panel">
                    <h3>Platform Revenue (Last 7 Days)</h3>
                    <div style="height: 200px; display: flex; align-items: flex-end; justify-content: space-between; padding-top: 1rem;">
                        <?php if (empty($chartData)): ?>
                            <div style="width: 100%; text-align: center; color: var(--color-muted); align-self: center;">No revenue data for the last 7 days.</div>
                        <?php else: ?>
                            <?php foreach ($chartData as $c): ?>
                                <div style="width: <?php echo max(5, 80 / count($chartData)); ?>%; background: var(--color-accent); height: <?php echo max(2, $c['percent']); ?>%; border-radius: 4px 4px 0 0; opacity: 0.8; position: relative;" title="£<?php echo number_format($c['total'], 2); ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; color: var(--color-muted); font-size: 0.8rem; font-weight: 500;">
                        <?php foreach ($chartData as $c): ?>
                            <span style="flex: 1; text-align: center;"><?php echo e(ucwords(strtolower($c['day']))); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="admin-panel">
                    <h3>All Traders by Revenue</h3>
                    <?php if (empty($revenueByTrader)): ?>
                        <p style="color: var(--color-muted);">No revenue data available yet.</p>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($revenueByTrader as $tr): ?>
                                <li style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem;">
                                    <span style="font-weight: 500; color: var(--color-foreground);"><?php echo e($tr['TRADER_NAME'] ?? $tr['trader_name']); ?></span>
                                    <span style="color: var(--color-primary-dark); font-weight: 700; background: rgba(0,0,0,0.03); padding: 0.25rem 0.75rem; border-radius: 20px;">£<?php echo number_format((float) ($tr['TOTAL_REVENUE'] ?? $tr['total_revenue']), 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="margin: 0;">Recent Orders</h3>
                    <a href="javascript:void(0)" onclick="openOrdersModal()" style="color: var(--color-primary-dark); text-decoration: none; font-size: 0.9rem; font-weight: 600;">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                <th style="text-align: left; padding-bottom: 0.75rem;">Order ID</th>
                                <th style="text-align: left; padding-bottom: 0.75rem;">Customer</th>
                                <th style="text-align: right; padding-bottom: 0.75rem;">Total</th>
                                <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem;">No recent orders.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $ro): ?>
                                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;">#EM-<?php echo e($ro['ORDER_ID'] ?? $ro['order_id']); ?></td>
                                        <td style="padding: 1rem 0;"><?php echo e($ro['CUSTOMER_NAME'] ?? $ro['customer_name']); ?></td>
                                        <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£<?php echo number_format((float) ($ro['ORDER_TOTAL'] ?? $ro['order_total']), 2); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
                                            <?php 
                                            $status = strtoupper($ro['ORDER_STATUS'] ?? $ro['order_status']);
                                            $badgeClass = ($status === 'DELIVERED' || $status === 'COLLECTED' || $status === 'PAID') ? 'status-badge--delivered' : 'status-badge--pending'; 
                                            ?>
                                            <span class="status-badge <?php echo $badgeClass; ?>"><?php echo e(ucwords(strtolower($status))); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
