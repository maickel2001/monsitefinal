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
$userOrders = $orders->getUserOrders($currentUser['id']);
$orderStats = $orders->getOrderStats($currentUser['id']);
$userTickets = $tickets->getUserTickets($currentUser['id']);
$categories = $services->getAllCategories();

// Handle tab switching
$activeTab = $_GET['tab'] ?? 'overview';

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'place_order':
                try {
                    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
                        throw new Exception("Invalid request");
                    }
                    
                    $orderData = [
                        'user_id' => $currentUser['id'],
                        'service_id' => (int)$_POST['service_id'],
                        'link' => trim($_POST['link']),
                        'quantity' => (int)$_POST['quantity']
                    ];
                    
                    $orderId = $orders->createOrder($orderData);
                    $message = "Order placed successfully! Order ID: #$orderId";
                    $messageType = 'success';
                    
                    // Refresh data
                    $userOrders = $orders->getUserOrders($currentUser['id']);
                    $orderStats = $orders->getOrderStats($currentUser['id']);
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $messageType = 'error';
                }
                break;
                
            case 'create_ticket':
                try {
                    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
                        throw new Exception("Invalid request");
                    }
                    
                    $ticketData = [
                        'user_id' => $currentUser['id'],
                        'subject' => trim($_POST['subject']),
                        'message' => trim($_POST['message']),
                        'priority' => $_POST['priority']
                    ];
                    
                    $ticketId = $tickets->createTicket($ticketData);
                    $message = "Support ticket created successfully! Ticket ID: #$ticketId";
                    $messageType = 'success';
                    
                    // Refresh data
                    $userTickets = $tickets->getUserTickets($currentUser['id']);
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $messageType = 'error';
                }
                break;
                
            case 'update_profile':
                try {
                    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
                        throw new Exception("Invalid request");
                    }
                    
                    $profileData = [
                        'first_name' => trim($_POST['first_name']),
                        'last_name' => trim($_POST['last_name']),
                        'phone' => trim($_POST['phone'])
                    ];
                    
                    $user->updateProfile($currentUser['id'], $profileData);
                    $message = "Profile updated successfully!";
                    $messageType = 'success';
                    
                    // Refresh user data
                    $currentUser = $user->getCurrentUser();
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $messageType = 'error';
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            overflow-x: hidden;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007AFF;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            color: #333;
        }

        .user-email {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: #007AFF;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: #007AFF;
            border: 1px solid #007AFF;
        }

        .btn-secondary:hover {
            background: #007AFF;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Dashboard Layout */
        .dashboard {
            padding: 2rem 0;
        }

        .tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            padding: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
            flex-wrap: wrap;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #6c757d;
        }

        .tab.active {
            background: #007AFF;
            color: white;
        }

        .tab:hover:not(.active) {
            background: #f8f9fa;
            color: #007AFF;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1d1d1f;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #007AFF;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-weight: 500;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #007AFF;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        /* Tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th, .table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1d1d1f;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-in_progress { background: #fff3cd; color: #856404; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-open { background: #d1ecf1; color: #0c5460; }
        .status-resolved { background: #d4edda; color: #155724; }
        .status-closed { background: #e9ecef; color: #6c757d; }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Service Selection */
        .service-selection {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .price-display {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            text-align: center;
        }

        .price-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #007AFF;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .tabs {
                flex-direction: column;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .table th, .table td {
                padding: 0.5rem;
            }
            
            .user-menu {
                flex-direction: column;
                align-items: flex-end;
            }
            
            .user-info {
                text-align: right;
                margin-bottom: 0.5rem;
            }
        }

        /* Loading States */
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="index.php" class="logo">
                    <i class="fas fa-rocket"></i>
                    <?php echo APP_NAME; ?>
                </a>
                
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                    </div>
                    
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Dashboard -->
    <main class="dashboard">
        <div class="container">
            <!-- Tabs -->
            <div class="tabs">
                <div class="tab <?php echo $activeTab === 'overview' ? 'active' : ''; ?>" data-tab="overview">
                    <i class="fas fa-chart-line"></i> Overview
                </div>
                <div class="tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>" data-tab="orders">
                    <i class="fas fa-shopping-cart"></i> Orders History
                </div>
                <div class="tab <?php echo $activeTab === 'place_order' ? 'active' : ''; ?>" data-tab="place_order">
                    <i class="fas fa-plus-circle"></i> Place Order
                </div>
                <div class="tab <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>" data-tab="tickets">
                    <i class="fas fa-ticket-alt"></i> Support Tickets
                </div>
                <div class="tab <?php echo $activeTab === 'profile' ? 'active' : ''; ?>" data-tab="profile">
                    <i class="fas fa-user"></i> Profile
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?> fade-in">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Overview Tab -->
            <div id="overview" class="tab-content <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $orderStats['pending'] ?? 0; ?></div>
                        <div class="stat-label">Pending Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $orderStats['completed'] ?? 0; ?></div>
                        <div class="stat-label">Completed Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $orderStats['total_spent'] ? formatCurrency($orderStats['total_spent']) : '0 ₣'; ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($userTickets); ?></div>
                        <div class="stat-label">Support Tickets</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Orders</h3>
                        <a href="?tab=orders" class="btn btn-primary">View All</a>
                    </div>
                    
                    <?php if (!empty($userOrders)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Service</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($userOrders, 0, 5) as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['service_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format($order['quantity']); ?></td>
                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>No Orders Yet</h3>
                            <p>Start by placing your first order to boost your social media presence!</p>
                            <a href="?tab=place_order" class="btn btn-primary">Place Order</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Support Tickets</h3>
                        <a href="?tab=tickets" class="btn btn-primary">View All</a>
                    </div>
                    
                    <?php if (!empty($userTickets)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Subject</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($userTickets, 0, 5) as $ticket): ?>
                                        <tr>
                                            <td>#<?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $ticket['priority']; ?>">
                                                    <?php echo ucfirst($ticket['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                                    <?php echo ucfirst($ticket['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <h3>No Support Tickets</h3>
                            <p>Need help? Create a support ticket and we'll assist you!</p>
                            <a href="?tab=tickets" class="btn btn-primary">Create Ticket</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Orders History Tab -->
            <div id="orders" class="tab-content <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Orders History</h3>
                    </div>
                    
                    <?php if (!empty($userOrders)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Service</th>
                                        <th>Link</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['service_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <a href="<?php echo htmlspecialchars($order['link']); ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-external-link-alt"></i> View
                                                </a>
                                            </td>
                                            <td><?php echo number_format($order['quantity']); ?></td>
                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                                    <i class="fas fa-eye"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>No Orders Found</h3>
                            <p>You haven't placed any orders yet. Start by placing your first order!</p>
                            <a href="?tab=place_order" class="btn btn-primary">Place Order</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Place Order Tab -->
            <div id="place_order" class="tab-content <?php echo $activeTab === 'place_order' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Place New Order</h3>
                    </div>
                    
                    <form method="POST" action="" id="orderForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                        <input type="hidden" name="action" value="place_order">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="service" class="form-label">Service</label>
                                <select class="form-select" id="service" name="service_id" required>
                                    <option value="">Select Service First</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="link" class="form-label">Link</label>
                                <input type="url" class="form-input" id="link" name="link" placeholder="https://example.com/post" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-input" id="quantity" name="quantity" min="1" placeholder="100" required>
                            </div>
                        </div>
                        
                        <div class="service-selection" id="serviceInfo" style="display: none;">
                            <h4>Service Information</h4>
                            <div id="serviceDetails"></div>
                        </div>
                        
                        <div class="price-display" id="priceDisplay" style="display: none;">
                            <div class="price-amount" id="totalPrice">0 ₣</div>
                            <div>Total Amount</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>

            <!-- Support Tickets Tab -->
            <div id="tickets" class="tab-content <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Support Tickets</h3>
                        <button class="btn btn-primary" onclick="showCreateTicket()">
                            <i class="fas fa-plus"></i> New Ticket
                        </button>
                    </div>
                    
                    <?php if (!empty($userTickets)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Subject</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userTickets as $ticket): ?>
                                        <tr>
                                            <td>#<?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $ticket['priority']; ?>">
                                                    <?php echo ucfirst($ticket['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $ticket['status']; ?>">
                                                    <?php echo ucfirst($ticket['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm" onclick="viewTicket(<?php echo $ticket['id']; ?>)">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <h3>No Support Tickets</h3>
                            <p>Need help? Create a support ticket and we'll assist you!</p>
                            <button class="btn btn-primary" onclick="showCreateTicket()">
                                <i class="fas fa-plus"></i> Create Ticket
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Create Ticket Form (Hidden by default) -->
                <div class="card" id="createTicketForm" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Create New Support Ticket</h3>
                        <button class="btn btn-secondary" onclick="hideCreateTicket()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                        <input type="hidden" name="action" value="create_ticket">
                        
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-input" id="subject" name="subject" placeholder="Brief description of your issue" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-textarea" id="message" name="message" placeholder="Describe your issue in detail..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Create Ticket
                        </button>
                    </form>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profile" class="tab-content <?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Profile Information</h3>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-input" id="first_name" name="first_name" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-input" id="last_name" name="last_name" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-input" id="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                                <small class="form-text">Email cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-input" id="phone" name="phone" value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="balance" class="form-label">Account Balance</label>
                            <input type="text" class="form-input" id="balance" value="<?php echo formatCurrency($currentUser['balance'] ?? 0); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-input" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-input" id="new_password" name="new_password" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-input" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
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
            const categoryId = this.value;
            const serviceSelect = document.getElementById('service');
            const serviceInfo = document.getElementById('serviceInfo');
            const priceDisplay = document.getElementById('priceDisplay');
            
            if (categoryId) {
                // Load services for selected category
                fetch(`api/services.php?action=get_by_category&category_id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            serviceSelect.innerHTML = '<option value="">Select Service</option>';
                            data.data.forEach(service => {
                                const option = document.createElement('option');
                                option.value = service.id;
                                option.dataset.price = service.unit_price;
                                option.dataset.minQuantity = service.min_quantity;
                                option.dataset.maxQuantity = service.max_quantity;
                                option.textContent = `${service.name} - ${formatCurrency(service.unit_price)} per unit`;
                                serviceSelect.appendChild(option);
                            });
                            serviceSelect.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading services:', error);
                        serviceSelect.innerHTML = '<option value="">Error loading services</option>';
                    });
            } else {
                serviceSelect.innerHTML = '<option value="">Select Service First</option>';
                serviceSelect.disabled = true;
                serviceInfo.style.display = 'none';
                priceDisplay.style.display = 'none';
            }
        });

        // Service change handler
        document.getElementById('service').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const serviceInfo = document.getElementById('serviceInfo');
            const priceDisplay = document.getElementById('priceDisplay');
            
            if (this.value) {
                const price = parseFloat(selectedOption.dataset.price);
                const minQty = parseInt(selectedOption.dataset.minQuantity);
                const maxQty = parseInt(selectedOption.dataset.maxQuantity);
                
                // Update quantity constraints
                document.getElementById('quantity').min = minQty;
                document.getElementById('quantity').max = maxQty;
                document.getElementById('quantity').placeholder = `${minQty} - ${maxQty}`;
                
                // Show service info
                document.getElementById('serviceDetails').innerHTML = `
                    <p><strong>Price:</strong> ${formatCurrency(price)} per unit</p>
                    <p><strong>Quantity Range:</strong> ${minQty.toLocaleString()} - ${maxQty.toLocaleString()}</p>
                `;
                serviceInfo.style.display = 'block';
                
                // Show price display
                priceDisplay.style.display = 'block';
                
                // Calculate initial price
                updatePrice();
            } else {
                serviceInfo.style.display = 'none';
                priceDisplay.style.display = 'none';
            }
        });

        // Quantity change handler
        document.getElementById('quantity').addEventListener('input', updatePrice);

        function updatePrice() {
            const quantity = parseInt(document.getElementById('quantity').value) || 0;
            const selectedOption = document.getElementById('service').options[document.getElementById('service').selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const price = parseFloat(selectedOption.dataset.price);
                const total = quantity * price;
                document.getElementById('totalPrice').textContent = formatCurrency(total);
            }
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'XOF',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Support ticket functions
        function showCreateTicket() {
            document.getElementById('createTicketForm').style.display = 'block';
            document.getElementById('createTicketForm').scrollIntoView({ behavior: 'smooth' });
        }

        function hideCreateTicket() {
            document.getElementById('createTicketForm').style.display = 'none';
        }

        // Order and ticket view functions
        function viewOrderDetails(orderId) {
            // Implement order details modal or redirect
            alert(`Viewing order details for order #${orderId}`);
        }

        function viewTicket(ticketId) {
            // Implement ticket view modal or redirect
            alert(`Viewing ticket #${ticketId}`);
        }

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>