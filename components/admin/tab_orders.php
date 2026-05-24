        <section id="orders" class="tab-content" style="display: none;">
            <div class="admin-panel">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <h3 style="margin: 0;">All Orders</h3>
                    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="orderSearchInput" placeholder="Search orders (ID, Customer...)" style="padding: 0.5rem 1rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px; width: 250px; font-family: inherit;" onkeyup="filterOrders()">
                        <select id="orderSortSelect" style="padding: 0.5rem 1rem; border: 1px solid rgba(0,0,0,0.1); border-radius: 4px; font-family: inherit; background-color: white;" onchange="sortOrders()">
                            <option value="date_desc">Sort by Date (Newest)</option>
                            <option value="date_asc">Sort by Date (Oldest)</option>
                            <option value="total_desc">Sort by Total (High to Low)</option>
                            <option value="total_asc">Sort by Total (Low to High)</option>
                            <option value="status">Sort by Status</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(0,0,0,0.05);">
                                <th style="text-align: left; padding-bottom: 0.75rem;">Order ID</th>
                                <th style="text-align: left; padding-bottom: 0.75rem;">Customer</th>
                                <th style="text-align: left; padding-bottom: 0.75rem;">Date</th>
                                <th style="text-align: right; padding-bottom: 0.75rem;">Total</th>
                                <th style="text-align: center; padding-bottom: 0.75rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="allOrdersBody">
                            <?php
// This component renders the list of all platform orders in the admin dashboard.

if (empty($allOrders)): ?>
                                <tr><td colspan="5" style="text-align: center; padding: 2rem;">No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($allOrders as $o): 
                                    $dt = $o['ORDER_DATE'] ?? $o['order_date'] ?? null;
                                    $total = (float) ($o['ORDER_TOTAL'] ?? $o['order_total']);
                                    $status = strtoupper($o['ORDER_STATUS'] ?? $o['order_status']);
                                    $badgeClass = ($status === 'DELIVERED' || $status === 'COLLECTED' || $status === 'PAID') ? 'status-badge--delivered' : 'status-badge--pending'; 
                                ?>
                                    <tr class="order-row" 
                                        data-date="<?php echo $dt ? date('Y-m-d H:i:s', strtotime($dt)) : '1970-01-01'; ?>" 
                                        data-total="<?php echo $total; ?>" 
                                        data-status="<?php echo strtolower($status); ?>"
                                        style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                        <td style="padding: 1rem 0; font-weight: 500;">
                                            <a href="javascript:void(0)" onclick="openOrderDetailsModal(<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?>)" style="color: var(--color-primary-dark); text-decoration: underline;">#EM-<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?></a>
                                        </td>
                                        <td style="padding: 1rem 0;"><?php echo e($o['CUSTOMER_NAME'] ?? $o['customer_name']); ?></td>
                                        <td style="padding: 1rem 0; color: var(--color-muted);">
                                            <?php echo $dt ? date('M d, Y', strtotime($dt)) : 'N/A'; ?>
                                        </td>
                                        <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£<?php echo number_format($total, 2); ?></td>
                                        <td style="text-align: center; padding: 1rem 0;">
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
