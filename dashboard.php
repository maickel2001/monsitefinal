<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Orders.php';
require_once 'includes/Services.php';
require_once 'includes/Tickets.php';

$user = new User();
$orders = new Orders();
$services = new Services();
$tickets = new Tickets();

// Redirect if not logged in
if (!$user->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $user->getCurrentUser();
$userId = $currentUser['id'];

// Get user data
$userOrders = $orders->getUserOrders($userId, 1, 5);
$orderStats = $orders->getOrderStats($userId);
$userTickets = $tickets->getUserTickets($userId, 1, 5);
$servicesData = $services->getServicesForOrderForm();

// Handle tab switching
$activeTab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        .header { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 1rem 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #007AFF; text-decoration: none; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .btn { padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 500; }
        .btn-primary { background: #007AFF; color: white; }
        .btn-secondary { background: transparent; color: #007AFF; border: 1px solid #007AFF; }
        
        .dashboard { padding: 2rem 0; }
        .tabs { display: flex; background: white; border-radius: 12px; padding: 0.5rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .tab { padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .tab.active { background: #007AFF; color: white; }
        .tab:hover:not(.active) { background: #f8f9fa; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .card-title { font-size: 1.25rem; font-weight: 600; color: #1d1d1f; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 12px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2rem; font-weight: 700; color: #007AFF; }
        .stat-label { color: #6c757d; margin-top: 0.5rem; }
        
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-input, .form-select { width: 100%; padding: 0.75rem; border: 2px solid #e9ecef; border-radius: 8px; font-size: 1rem; }
        .form-input:focus, .form-select:focus { outline: none; border-color: #007AFF; }
        
        .order-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .order-status { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .ticket-item { border: 1px solid #e9ecef; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .priority-high { border-left: 4px solid #dc3545; }
        .priority-medium { border-left: 4px solid #ffc107; }
        .priority-low { border-left: 4px solid #28a745; }
        
        @media (max-width: 768px) {
            .tabs { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">
                    <i class="fas fa-rocket"></i> <?php echo APP_NAME; ?>
                </a>
                <div class="user-menu">
                    <span>Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="dashboard">
        <div class="container">
            <div class="tabs">
                <div class="tab <?php echo $activeTab === 'overview' ? 'active' : ''; ?>" data-tab="overview">
                    <i class="fas fa-chart-line"></i> Overview
                </div>
                <div class="tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>" data-tab="orders">
                    <i class="fas fa-shopping-cart"></i> Orders History
                </div>
                <div class="tab <?php echo $activeTab === 'place-order' ? 'active' : ''; ?>" data-tab="place-order">
                    <i class="fas fa-plus"></i> Place Order
                </div>
                <div class="tab <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>" data-tab="tickets">
                    <i class="fas fa-ticket-alt"></i> Support Tickets
                </div>
                <div class="tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" data-tab="profile">
                    <i class="fas fa-user"></i> Profile
                </div>
            </div>

            <!-- Overview Tab -->
            <div id="overview" class="tab-content <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $orderStats['pending']['count'] ?? 0; ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $orderStats['completed']['count'] ?? 0; ?></div>
                        <div class="stat-label">Completed Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$<?php echo number_format($orderStats['completed']['total'] ?? 0, 2); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $userTickets['total']; ?></div>
                        <div class="stat-label">Support Tickets</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Orders</h3>
                        <a href="?tab=orders" class="btn btn-secondary">View All</a>
                    </div>
                    <?php if (!empty($userOrders['orders'])): ?>
                        <?php foreach ($userOrders['orders'] as $order): ?>
                            <div class="order-item">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($order['service_name']); ?></strong>
                                        <div style="color: #6c757d; font-size: 0.875rem;">
                                            Quantity: <?php echo $order['quantity']; ?> | 
                                            Total: $<?php echo number_format($order['total_amount'], 2); ?>
                                        </div>
                                    </div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #6c757d; text-align: center;">No orders yet. <a href="?tab=place-order">Place your first order</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Orders History Tab -->
            <div id="orders" class="tab-content <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                <div class="card">
                    <h3 class="card-title">Orders History</h3>
                    <?php if (!empty($userOrders['orders'])): ?>
                        <?php foreach ($userOrders['orders'] as $order): ?>
                            <div class="order-item">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($order['service_name']); ?></strong>
                                        <div style="color: #6c757d; font-size: 0.875rem; margin-top: 0.25rem;">
                                            Category: <?php echo htmlspecialchars($order['category_name']); ?>
                                        </div>
                                        <div style="color: #6c757d; font-size: 0.875rem;">
                                            Link: <?php echo htmlspecialchars($order['link']); ?>
                                        </div>
                                        <div style="color: #6c757d; font-size: 0.875rem;">
                                            Quantity: <?php echo $order['quantity']; ?> | 
                                            Total: $<?php echo number_format($order['total_amount'], 2); ?>
                                        </div>
                                        <div style="color: #6c757d; font-size: 0.875rem;">
                                            Date: <?php echo date('M j, Y', strtotime($order['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </div>
                                <?php if (!empty($order['admin_notes'])): ?>
                                    <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 6px; margin-top: 0.5rem;">
                                        <strong>Admin Notes:</strong> <?php echo htmlspecialchars($order['admin_notes']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #6c757d; text-align: center;">No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Place Order Tab -->
            <div id="place-order" class="tab-content <?php echo $activeTab === 'place-order' ? 'active' : ''; ?>">
                <div class="card">
                    <h3 class="card-title">Place New Order</h3>
                    <form id="orderForm">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($servicesData as $categoryId => $categoryData): ?>
                                    <option value="<?php echo $categoryId; ?>">
                                        <?php echo htmlspecialchars($categoryData['category']['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Service</label>
                            <select class="form-select" id="service" required disabled>
                                <option value="">Select Service</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Link/Content</label>
                            <input type="url" class="form-input" id="link" placeholder="Enter your social media link" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-input" id="quantity" placeholder="Enter quantity" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Total Amount</label>
                            <input type="text" class="form-input" id="total" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </form>
                </div>
            </div>

            <!-- Support Tickets Tab -->
            <div id="tickets" class="tab-content <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Support Tickets</h3>
                        <button class="btn btn-primary" onclick="openNewTicket()">New Ticket</button>
                    </div>
                    
                    <?php if (!empty($userTickets['tickets'])): ?>
                        <?php foreach ($userTickets['tickets'] as $ticket): ?>
                            <div class="ticket-item priority-<?php echo $ticket['priority']; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                    <div>
                                        <strong><?php echo htmlspecialchars($ticket['subject']); ?></strong>
                                        <div style="color: #6c757d; font-size: 0.875rem; margin-top: 0.25rem;">
                                            <?php echo htmlspecialchars($ticket['message']); ?>
                                        </div>
                                        <div style="color: #6c757d; font-size: 0.875rem; margin-top: 0.5rem;">
                                            Priority: <?php echo ucfirst($ticket['priority']); ?> | 
                                            Status: <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?> |
                                            Date: <?php echo date('M j, Y', strtotime($ticket['created_at'])); ?>
                                        </div>
                                    </div>
                                    <span class="order-status status-<?php echo $ticket['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #6c757d; text-align: center;">No support tickets found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profile" class="tab-content <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                <div class="card">
                    <h3 class="card-title">Profile Information</h3>
                    <form id="profileForm">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-input" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Update active tab
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Update active content
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.getElementById(tabName).classList.add('active');
                
                // Update URL
                const url = new URL(window.location);
                url.searchParams.set('tab', tabName);
                window.history.pushState({}, '', url);
            });
        });

        // Category change handler
        document.getElementById('category').addEventListener('change', function() {
            const serviceSelect = document.getElementById('service');
            const categoryId = this.value;
            
            serviceSelect.innerHTML = '<option value="">Select Service</option>';
            serviceSelect.disabled = !categoryId;
            
            if (categoryId) {
                const services = <?php echo json_encode($servicesData); ?>;
                const categoryServices = services[categoryId].services;
                
                categoryServices.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} - $${service.unit_price}`;
                    option.dataset.price = service.unit_price;
                    option.dataset.min = service.min_quantity;
                    option.dataset.max = service.max_quantity;
                    serviceSelect.appendChild(option);
                });
            }
        });

        // Service change handler
        document.getElementById('service').addEventListener('change', function() {
            const quantityInput = document.getElementById('quantity');
            const totalInput = document.getElementById('total');
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const price = parseFloat(selectedOption.dataset.price);
                const min = parseInt(selectedOption.dataset.min);
                const max = parseInt(selectedOption.dataset.max);
                
                quantityInput.min = min;
                quantityInput.max = max;
                quantityInput.placeholder = `Min: ${min}, Max: ${max}`;
                
                // Calculate total on quantity change
                quantityInput.addEventListener('input', function() {
                    const quantity = parseInt(this.value) || 0;
                    if (quantity >= min && quantity <= max) {
                        totalInput.value = `$${(price * quantity).toFixed(2)}`;
                    } else {
                        totalInput.value = '';
                    }
                });
            }
        });

        // Form submissions
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add order submission logic here
            alert('Order submission functionality will be implemented here');
        });

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add profile update logic here
            alert('Profile update functionality will be implemented here');
        });

        function openNewTicket() {
            // Add new ticket functionality here
            alert('New ticket functionality will be implemented here');
        }
    </script>
</body>
</html>