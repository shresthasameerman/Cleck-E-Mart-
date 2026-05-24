<?php
// This is the main admin dashboard where administrators can view platform statistics, manage users, and verify shops.

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/auth_helpers.php';

// Only ADMIN can access
require_login(['ADMIN']);

require_once __DIR__ . '/lib/admin_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (isset($_POST['product_id'])) {
        handle_admin_product_action($action, (int) $_POST['product_id']);
        redirect('admin-dashboard.php');
    }
    
    if (isset($_POST['shop_id'])) {
        handle_admin_shop_action($action, (int) $_POST['shop_id']);
        redirect('admin-dashboard.php');
    }
}

$pendingProducts = get_admin_pending_products();
$pendingShops = get_admin_pending_shops();
$dashboardData = get_admin_overview_data();

$totalRevenue = $dashboardData['totalRevenue'];
$totalOrders = $dashboardData['totalOrders'];
$activeTraders = $dashboardData['activeTraders'];
$totalCustomers = $dashboardData['totalCustomers'];
$recentOrders = $dashboardData['recentOrders'];
$revenueByTrader = $dashboardData['revenueByTrader'];
$chartData = $dashboardData['chartData'];
$allOrders = $dashboardData['allOrders'];
$itemsByOrder = $dashboardData['itemsByOrder'];
$allTraders = $dashboardData['allTraders'];

$pageTitle = 'Admin Dashboard - Cleck E-Mart';
require __DIR__ . '/components/header.php';
?>
<style>
/* Clean professional tab styles */
.admin-tab-nav {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    padding-bottom: 0;
}
.admin-tab-btn {
    background: none;
    border: none;
    padding: 1rem 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    color: var(--color-muted);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.admin-tab-btn:hover {
    color: var(--color-foreground);
}
.admin-tab-btn.active {
    color: var(--color-primary-dark);
    border-bottom-color: var(--color-primary-dark);
    font-weight: 600;
}
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}
.metric-card {
    background: white; /* Clean white card */
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
}
.metric-card h3 {
    font-size: 0.85rem;
    color: var(--color-muted);
    margin: 0 0 0.5rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.metric-card p {
    font-size: 2rem;
    font-weight: 700;
    color: var(--color-foreground);
    margin: 0;
}
.admin-panel {
    background: white;
    padding: 2rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}
.admin-panel h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.25rem;
    color: var(--color-foreground);
}
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-content {
    background: white;
    padding: 2rem;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 900px;
    max-height: 85vh;
    overflow-y: auto;
    position: relative;
    box-shadow: var(--shadow-lg);
}
.modal-close {
    position: absolute;
    top: 1.5rem; right: 1.5rem;
    background: none; border: none;
    font-size: 1.5rem; cursor: pointer;
    color: var(--color-muted);
}
</style>
<main id="main-content" class="page-layout" style="padding-top: 2rem;">
    
    <div class="container">
        <!-- Clean Tab Navigation -->
        <nav class="admin-tab-nav" aria-label="Admin Sections">
            <button class="admin-tab-btn active" onclick="openAdminTab(event, 'dashboard')">Dashboard Overview</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'shops')">Shop Verification</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'products')">Product Verification</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'traders')">Trader Access</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'collection')">Collection Verification</button>
            <button class="admin-tab-btn" onclick="openAdminTab(event, 'orders')">Orders</button>
        </nav>

        <?php if ($flashSuccess = get_flash('success')): ?>
            <p class="page-message page-message--success"><?php echo e($flashSuccess); ?></p>
        <?php endif; ?>
        <?php if ($flashError = get_flash('error')): ?>
            <p class="page-message page-message--error"><?php echo e($flashError); ?></p>
        <?php endif; ?>

        <!-- Dashboard Content -->
        <?php require __DIR__ . '/components/admin/tab_dashboard.php'; ?>

        <!-- Product Verification Content -->
        <?php require __DIR__ . '/components/admin/tab_products.php'; ?>

        <!-- Shop Verification Content -->
        <?php require __DIR__ . '/components/admin/tab_shops.php'; ?>

        <?php require __DIR__ . '/components/admin/tab_traders.php'; ?>

        <?php require __DIR__ . '/components/admin/tab_collection.php'; ?>

        <?php require __DIR__ . '/components/admin/tab_orders.php'; ?>

    </div>
</main>

<!-- The Orders Modal -->
<div id="ordersModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeOrdersModal()">&times;</button>
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem;">All Orders</h3>
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
                <tbody>
                    <?php if (empty($allOrders)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allOrders as $o): ?>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <td style="padding: 1rem 0; font-weight: 500;">
                                    <a href="javascript:void(0)" onclick="openOrderDetailsModal(<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?>)" style="color: var(--color-primary-dark); text-decoration: underline;">#EM-<?php echo e($o['ORDER_ID'] ?? $o['order_id']); ?></a>
                                </td>
                                <td style="padding: 1rem 0;"><?php echo e($o['CUSTOMER_NAME'] ?? $o['customer_name']); ?></td>
                                <td style="padding: 1rem 0; color: var(--color-muted);">
                                    <?php 
                                        $dt = $o['ORDER_DATE'] ?? $o['order_date'] ?? null;
                                        echo $dt ? date('M d, Y', strtotime($dt)) : 'N/A';
                                    ?>
                                </td>
                                <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£<?php echo number_format((float) ($o['ORDER_TOTAL'] ?? $o['order_total']), 2); ?></td>
                                <td style="text-align: center; padding: 1rem 0;">
                                    <?php 
                                    $status = strtoupper($o['ORDER_STATUS'] ?? $o['order_status']);
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
</div>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="modal-overlay" style="z-index: 1100;">
    <div class="modal-content" style="max-width: 600px;">
        <button class="modal-close" onclick="closeOrderDetailsModal()">&times;</button>
        <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem;">Order Details</h3>
        <div id="orderDetailsBody" class="table-responsive">
            <!-- Details rendered via JS -->
        </div>
    </div>
</div>

<script>
const itemsByOrder = <?php echo json_encode($itemsByOrder); ?>;

// Handles the core logic and operations for openOrderDetailsModal
function openOrderDetailsModal(orderId) {
    const items = itemsByOrder[orderId] || [];
    let html = '<table class="data-table" style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="border-bottom: 2px solid rgba(0,0,0,0.05);"><th style="text-align: left; padding-bottom: 0.75rem;">Product</th><th style="text-align: left; padding-bottom: 0.75rem;">Shop</th><th style="text-align: center; padding-bottom: 0.75rem;">Qty</th><th style="text-align: right; padding-bottom: 0.75rem;">Price</th></tr></thead><tbody>';
    
    if (items.length === 0) {
        html += '<tr><td colspan="4" style="text-align: center; padding: 2rem;">No items found for this order.</td></tr>';
    } else {
        items.forEach(i => {
            const prodName = i.PRODUCT_NAME || i.product_name;
            const shopName = i.SHOP_NAME || i.shop_name;
            const qty = i.QUANTITY || i.quantity;
            const price = parseFloat(i.UNIT_PRICE || i.unit_price).toFixed(2);
            html += `<tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                <td style="padding: 1rem 0;">${prodName}</td>
                <td style="padding: 1rem 0; color: var(--color-muted);">${shopName}</td>
                <td style="text-align: center; padding: 1rem 0;">${qty}</td>
                <td style="text-align: right; padding: 1rem 0; font-weight: 600;">£${price}</td>
            </tr>`;
        });
    }
    html += '</tbody></table>';
    
    document.getElementById('orderDetailsBody').innerHTML = html;
    document.getElementById('orderDetailsModal').classList.add('active');
}

// Handles the core logic and operations for closeOrderDetailsModal
function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').classList.remove('active');
}

// Handles the core logic and operations for openAdminTab
function openAdminTab(event, tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(function(el) {
        el.style.display = 'none';
        el.classList.remove('active-tab');
    });
    // Remove active class from all tab buttons
    document.querySelectorAll('.admin-tab-btn').forEach(function(el) {
        el.classList.remove('active');
    });
    
    // Show the selected tab content
    var target = document.getElementById(tabId);
    if (target) {
        target.style.display = 'block';
        target.classList.add('active-tab');
    }
    // Add active class to the clicked button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    }
}

// Handles the core logic and operations for openOrdersModal
function openOrdersModal() {
    document.getElementById('ordersModal').classList.add('active');
}
// Handles the core logic and operations for closeOrdersModal
function closeOrdersModal() {
    document.getElementById('ordersModal').classList.remove('active');
}

// Handles the core logic and operations for filterOrders
function filterOrders() {
    const input = document.getElementById('orderSearchInput').value.toLowerCase();
    const trs = document.querySelectorAll('#allOrdersBody tr.order-row');
    trs.forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(input) ? '' : 'none';
    });
}

// Handles the core logic and operations for sortOrders
function sortOrders() {
    const sortBy = document.getElementById('orderSortSelect').value;
    const tbody = document.getElementById('allOrdersBody');
    const rows = Array.from(tbody.querySelectorAll('tr.order-row'));

    rows.sort((a, b) => {
        if (sortBy === 'date_desc' || sortBy === 'date_asc') {
            const da = new Date(a.dataset.date);
            const db = new Date(b.dataset.date);
            return sortBy === 'date_desc' ? db - da : da - db;
        } else if (sortBy === 'total_desc' || sortBy === 'total_asc') {
            const ta = parseFloat(a.dataset.total);
            const tb = parseFloat(b.dataset.total);
            return sortBy === 'total_desc' ? tb - ta : ta - tb;
        } else if (sortBy === 'status') {
            return a.dataset.status.localeCompare(b.dataset.status);
        }
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>

<style>
    @keyframes spin { 100% { transform: rotate(360deg); } }
    .order-card { background: white; border: 1px solid rgba(0,0,0,0.1); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1rem; }
    .order-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 0.75rem; margin-bottom: 0.75rem; }
    .order-item-row { display: flex; justify-content: space-between; font-size: 0.95rem; margin-bottom: 0.5rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ---- RFID Web Serial Logic ----
    const connectBtn = document.getElementById('rfidConnectBtn');
    const statusEl = document.getElementById('rfidStatus');
    const loadingEl = document.getElementById('rfidLoading');
    const resultsEl = document.getElementById('rfidResults');
    const custNameEl = document.getElementById('rfidCustName');
    const custEmailEl = document.getElementById('rfidCustEmail');
    const custPhoneEl = document.getElementById('rfidCustPhone');
    const ordersListEl = document.getElementById('rfidOrdersList');

    if (!connectBtn) return; // If we aren't rendering the collection tab for some reason

    let port;
    let reader;
    let isConnected = false;

    async function connectSerial() {
        if (!('serial' in navigator)) {
            alert("Your browser doesn't support the Web Serial API. Please use Google Chrome or Microsoft Edge.");
            return;
        }

        try {
            port = await navigator.serial.requestPort();
            await port.open({ baudRate: 9600 });
            isConnected = true;
            connectBtn.textContent = 'Disconnect';
            connectBtn.style.background = '#ef4444';
            setStatus('Connected to Arduino. Ready to scan.', '#10b981');
            readLoop();
        } catch (err) {
            console.error("Serial connection error:", err);
            setStatus('Connection failed or cancelled.', '#ef4444');
        }
    }

    async function disconnectSerial() {
        if (reader) {
            await reader.cancel();
        }
        if (port) {
            await port.close();
        }
        isConnected = false;
        connectBtn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg> Connect Scanner`;
        connectBtn.style.background = 'var(--color-primary-dark)';
        setStatus('Scanner disconnected.', '#9ca3af');
        resultsEl.style.display = 'none';
    }

    connectBtn.addEventListener('click', async () => {
        if (isConnected) {
            await disconnectSerial();
        } else {
            await connectSerial();
        }
    });

    // Handles the core logic and operations for setStatus
    function setStatus(text, color) {
        statusEl.innerHTML = `<div style="width: 8px; height: 8px; border-radius: 50%; background: ${color};"></div> ${text}`;
    }

    async function readLoop() {
        const textDecoder = new TextDecoderStream();
        const readableStreamClosed = port.readable.pipeTo(textDecoder.writable);
        reader = textDecoder.readable.getReader();
        
        let buffer = '';
        
        try {
            while (true) {
                const { value, done } = await reader.read();
                if (done) break;
                
                buffer += value;
                const lines = buffer.split('\n');
                
                // Keep the last incomplete line in the buffer
                buffer = lines.pop(); 
                
                for (const line of lines) {
                    const trimmedLine = line.trim();
                    if (trimmedLine.startsWith('Card UID:')) {
                        // Extract "F3 DA 84 1A" -> "F3DA841A"
                        let uidRaw = trimmedLine.replace('Card UID:', '').trim();
                        let uid = uidRaw.replace(/\s+/g, '');
                        
                        if (uid) {
                            handleCardScan(uid);
                        }
                    }
                }
            }
        } catch (error) {
            console.error("Read error:", error);
        } finally {
            reader.releaseLock();
        }
    }

    async function handleCardScan(uid) {
        setStatus(`Card scanned (UID: ${uid}). Fetching data...`, '#3b82f6');
        resultsEl.style.display = 'none';
        loadingEl.style.display = 'block';

        try {
            const response = await fetch(`lib/rfid_api.php?action=scan&uid=${encodeURIComponent(uid)}`);
            const data = await response.json();
            
            loadingEl.style.display = 'none';

            if (data.status === 'success') {
                setStatus('Data loaded successfully.', '#10b981');
                
                // Populate customer info
                custNameEl.textContent = data.customer.name;
                custEmailEl.textContent = data.customer.email;
                custPhoneEl.textContent = data.customer.phone || 'N/A';

                // Populate orders
                ordersListEl.innerHTML = '';
                if (data.orders.length === 0) {
                    ordersListEl.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--color-muted); border: 1px dashed rgba(0,0,0,0.2); border-radius: var(--radius-sm);">No active orders found for this customer.</div>';
                } else {
                    data.orders.forEach(order => {
                        let itemsHtml = '';
                        if (order.items && order.items.length > 0) {
                            order.items.forEach(item => {
                                itemsHtml += `
                                    <div class="order-item-row">
                                        <span>${item.QUANTITY ?? item.quantity}x ${item.PRODUCT_NAME ?? item.product_name}</span>
                                        <span>£${parseFloat(item.UNIT_PRICE ?? item.unit_price).toFixed(2)}</span>
                                    </div>
                                `;
                            });
                        }

                        const orderId = order.ORDER_ID ?? order.order_id;
                        const totalAmount = parseFloat(order.TOTAL_AMOUNT ?? order.total_amount).toFixed(2);
                        const orderDate = new Date(order.ORDER_DATE ?? order.order_date).toLocaleDateString();
                        const status = order.ORDER_STATUS ?? order.order_status;

                        ordersListEl.innerHTML += `
                            <div class="order-card" id="rfid-order-${orderId}">
                                <div class="order-card-header">
                                    <div>
                                        <div style="font-weight: 700;">Order #EM-${orderId}</div>
                                        <div style="font-size: 0.8rem; color: var(--color-muted);">${orderDate} • Status: ${status}</div>
                                    </div>
                                    <button onclick="markOrderCollected(${orderId})" class="button button--small" style="background: #10b981; color: white;">Mark Collected</button>
                                </div>
                                <div>
                                    ${itemsHtml}
                                    <div style="border-top: 1px dashed rgba(0,0,0,0.1); margin-top: 0.5rem; padding-top: 0.5rem; text-align: right; font-weight: 700;">
                                        Total: £${totalAmount}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                resultsEl.style.display = 'block';

            } else {
                setStatus(`Error: ${data.message}`, '#ef4444');
            }
        } catch (err) {
            console.error(err);
            loadingEl.style.display = 'none';
            setStatus('Network error fetching customer data.', '#ef4444');
        }
    }

    window.markOrderCollected = async function(orderId) {
        if (!confirm('Mark this order as collected and complete?')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'mark_collected');
            formData.append('order_id', orderId);

            const response = await fetch('lib/rfid_api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.status === 'success') {
                const card = document.getElementById(`rfid-order-${orderId}`);
                if (card) {
                    card.innerHTML = `<div style="padding: 1rem; text-align: center; color: #10b981; font-weight: 600;">✓ Order Marked as Collected</div>`;
                    setTimeout(() => card.remove(), 2000);
                }
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            alert('Network error updating order.');
        }
    };
});
</script>

<?php require __DIR__ . '/components/footer.php'; ?>
