<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Admin.php';
require_once '../includes/Services.php';
require_once '../includes/Reviews.php';
require_once '../includes/Tickets.php';

$admin = new Admin();
$services = new Services();
$reviews = new Reviews();
$tickets = new Tickets();

// Redirect if not logged in as admin
if (!$admin->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentAdmin = $admin->getCurrentAdmin();
$dashboardStats = $admin->getDashboardStats();
$monthlyStats = $admin->getMonthlyStats();

// Handle tab switching
$activeTab = $_GET['tab'] ?? 'overview';

// Get data for different tabs
$pendingReviews = $reviews->getPendingReviews(5);
$openTickets = $tickets->getAllTickets(['status' => 'open'], 1, 5);
$recentOrders = $admin->getAllOrders([], 1, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: #f8f9fa; 
            line-height: 1.6;
        }
        .header { 
            background: white; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            padding: 1rem 0; 
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #007AFF; text-decoration: none; }
        .admin-menu { display: flex; align-items: center; gap: 1rem; }
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
        }
        .btn-primary { background: #007AFF; color: white; }
        .btn-primary:hover { background: #0056b3; transform: translateY(-1px); }
        .btn-secondary { background: transparent; color: #007AFF; border: 1px solid #007AFF; }
        .btn-secondary:hover { background: #007AFF; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        
        .dashboard { padding: 2rem 0; }
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
            transition: all 0.3s; 
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tab.active { background: #007AFF; color: white; }
        .tab:hover:not(.active) { background: #f8f9fa; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
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
        .card-title { font-size: 1.25rem; font-weight: 600; color: #1d1d1f; }
        
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
        .stat-number { font-size: 2rem; font-weight: 700; color: #007AFF; }
        .stat-label { color: #6c757d; margin-top: 0.5rem; }
        
        .chart-container { 
            height: 300px; 
            margin: 1rem 0; 
            position: relative;
        }
        
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
        
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
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
        .form-textarea { resize: vertical; min-height: 100px; }
        
        .status-badge { 
            padding: 0.25rem 0.5rem; 
            border-radius: 4px; 
            font-size: 0.875rem; 
            font-weight: 500; 
            text-transform: capitalize;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-open { background: #d1ecf1; color: #0c5460; }
        .status-in_progress { background: #fff3cd; color: #856404; }
        .status-resolved { background: #d4edda; color: #155724; }
        .status-closed { background: #e9ecef; color: #6c757d; }
        
        .filters { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 1rem; 
            flex-wrap: wrap; 
            align-items: end;
        }
        .filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
        .filter-group label { font-size: 0.875rem; font-weight: 500; color: #6c757d; }
        
        .pagination { 
            display: flex; 
            justify-content: center; 
            gap: 0.5rem; 
            margin-top: 1rem; 
            flex-wrap: wrap;
        }
        .page-link { 
            padding: 0.5rem 0.75rem; 
            border: 1px solid #e9ecef; 
            border-radius: 4px; 
            text-decoration: none; 
            color: #007AFF; 
            transition: all 0.3s ease;
        }
        .page-link:hover {
            background: #f8f9fa;
        }
        .page-link.active { 
            background: #007AFF; 
            color: white; 
            border-color: #007AFF; 
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .action-icon {
            font-size: 2rem;
            color: #007AFF;
            margin-bottom: 1rem;
        }
        .action-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1d1d1f;
        }
        .action-desc {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }
        
        @media (max-width: 768px) {
            .tabs { flex-direction: column; }
            .stats-grid { grid-template-columns: 1fr; }
            .filters { flex-direction: column; }
            .quick-actions { grid-template-columns: 1fr; }
            .table { font-size: 0.875rem; }
            .table th, .table td { padding: 0.5rem; }
        }
        
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
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="../index.php" class="logo">
                    <i class="fas fa-rocket"></i> <?php echo APP_NAME; ?> Admin
                </a>
                <div class="admin-menu">
                    <span style="color: #6c757d;">
                        <i class="fas fa-user-shield"></i> 
                        <?php echo htmlspecialchars($currentAdmin['name']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
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
                    <i class="fas fa-shopping-cart"></i> Orders
                </div>
                <div class="tab <?php echo $activeTab === 'services' ? 'active' : ''; ?>" data-tab="services">
                    <i class="fas fa-cogs"></i> Services
                </div>
                <div class="tab <?php echo $activeTab === 'users' ? 'active' : ''; ?>" data-tab="users">
                    <i class="fas fa-users"></i> Users
                </div>
                <div class="tab <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>" data-tab="reviews">
                    <i class="fas fa-star"></i> Reviews
                </div>
                <div class="tab <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>" data-tab="tickets">
                    <i class="fas fa-ticket-alt"></i> Support
                </div>
            </div>

            <!-- Overview Tab -->
            <div id="overview" class="tab-content <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $dashboardStats['total_users'] ?? 0; ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo array_sum($dashboardStats['orders'] ?? []); ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo formatCurrency($dashboardStats['total_revenue'] ?? 0); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $dashboardStats['pending_reviews'] ?? 0; ?></div>
                        <div class="stat-label">Pending Reviews</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $dashboardStats['open_tickets'] ?? 0; ?></div>
                        <div class="stat-label">Open Tickets</div>
                    </div>
                </div>

                <div class="quick-actions">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="action-title">Recent Orders</div>
                        <div class="action-desc">Monitor latest order activities</div>
                        <a href="?tab=orders" class="btn btn-primary">View Orders</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="action-title">Pending Reviews</div>
                        <div class="action-desc">Moderate client reviews</div>
                        <a href="?tab=reviews" class="btn btn-primary">Moderate Reviews</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="action-title">Support Tickets</div>
                        <div class="action-desc">Handle customer support</div>
                        <a href="?tab=tickets" class="btn btn-primary">View Tickets</a>
                    </div>
                    
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="action-title">Manage Services</div>
                        <div class="action-desc">Update SMM services</div>
                        <a href="?tab=services" class="btn btn-primary">Manage Services</a>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">Monthly Statistics</h3>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title">Order Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Orders Tab -->
            <div id="orders" class="tab-content <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Orders Management</h3>
                        <div>
                            <button class="btn btn-primary" onclick="refreshOrders()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="filters">
                        <div class="filter-group">
                            <label>Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Search</label>
                            <input type="text" class="form-input" id="searchFilter" placeholder="Search orders...">
                        </div>
                        <div class="filter-group">
                            <label>Date From</label>
                            <input type="date" class="form-input" id="dateFromFilter">
                        </div>
                        <div class="filter-group">
                            <label>Date To</label>
                            <input type="date" class="form-input" id="dateToFilter">
                        </div>
                    </div>
                    
                    <div id="ordersTable">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading orders...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Tab -->
            <div id="services" class="tab-content <?php echo $activeTab === 'services' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Services Management</h3>
                        <div>
                            <button class="btn btn-primary" onclick="openServiceModal()">
                                <i class="fas fa-plus"></i> Add Service
                            </button>
                            <button class="btn btn-secondary" onclick="openCategoryModal()">
                                <i class="fas fa-folder-plus"></i> Add Category
                            </button>
                        </div>
                    </div>
                    
                    <div id="servicesTable">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading services...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div id="users" class="tab-content <?php echo $activeTab === 'users' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User Management</h3>
                        <div>
                            <button class="btn btn-primary" onclick="refreshUsers()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div id="usersTable">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading users...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews" class="tab-content <?php echo $activeTab === 'reviews' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Reviews Management</h3>
                        <div>
                            <button class="btn btn-primary" onclick="refreshReviews()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div id="reviewsTable">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading reviews...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Tickets Tab -->
            <div id="tickets" class="tab-content <?php echo $activeTab === 'tickets' ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Support Tickets</h3>
                        <div>
                            <button class="btn btn-primary" onclick="refreshTickets()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div id="ticketsTable">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading tickets...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Initialize charts
        const monthlyData = <?php echo json_encode($monthlyStats); ?>;
        const orderStatusData = <?php echo json_encode($dashboardStats['orders'] ?? []); ?>;

        // Monthly Statistics Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: Object.keys(monthlyData),
                datasets: [{
                    label: 'Orders',
                    data: Object.values(monthlyData).map(d => d.orders),
                    borderColor: '#007AFF',
                    backgroundColor: 'rgba(0, 122, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Revenue (FCFA)',
                    data: Object.values(monthlyData).map(d => d.revenue),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Orders'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Revenue (FCFA)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(orderStatusData).map(status => 
                    status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')
                ),
                datasets: [{
                    data: Object.values(orderStatusData),
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#007AFF',
                        '#28a745',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

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
                
                // Load tab data
                loadTabData(tabName);
            });
        });

        // Load tab data
        function loadTabData(tabName) {
            switch(tabName) {
                case 'orders':
                    loadOrders();
                    break;
                case 'services':
                    loadServices();
                    break;
                case 'users':
                    loadUsers();
                    break;
                case 'reviews':
                    loadReviews();
                    break;
                case 'tickets':
                    loadTickets();
                    break;
            }
        }

        // Load orders
        function loadOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const searchFilter = document.getElementById('searchFilter').value;
            const dateFromFilter = document.getElementById('dateFromFilter').value;
            const dateToFilter = document.getElementById('dateToFilter').value;
            
            document.getElementById('ordersTable').innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading orders...
                </div>
            `;
            
            // Simulate loading - in real implementation, this would be an AJAX call
            setTimeout(() => {
                document.getElementById('ordersTable').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No Orders Found</h3>
                        <p>Orders will appear here when they are created.</p>
                    </div>
                `;
            }, 1000);
        }

        // Load services
        function loadServices() {
            document.getElementById('servicesTable').innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading services...
                </div>
            `;
            
            setTimeout(() => {
                document.getElementById('servicesTable').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-cogs"></i>
                        <h3>No Services Found</h3>
                        <p>Services will appear here when they are created.</p>
                    </div>
                `;
            }, 1000);
        }

        // Load users
        function loadUsers() {
            document.getElementById('usersTable').innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading users...
                </div>
            `;
            
            setTimeout(() => {
                document.getElementById('usersTable').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No Users Found</h3>
                        <p>Users will appear here when they register.</p>
                    </div>
                `;
            }, 1000);
        }

        // Load reviews
        function loadReviews() {
            document.getElementById('reviewsTable').innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading reviews...
                </div>
            `;
            
            setTimeout(() => {
                document.getElementById('reviewsTable').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <h3>No Reviews Found</h3>
                        <p>Reviews will appear here when they are submitted.</p>
                    </div>
                `;
            }, 1000);
        }

        // Load tickets
        function loadTickets() {
            document.getElementById('ticketsTable').innerHTML = `
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading tickets...
                </div>
            `;
            
            setTimeout(() => {
                document.getElementById('ticketsTable').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>No Tickets Found</h3>
                        <p>Support tickets will appear here when they are created.</p>
                    </div>
                `;
            }, 1000);
        }

        // Filter change handlers
        document.getElementById('statusFilter').addEventListener('change', loadOrders);
        document.getElementById('searchFilter').addEventListener('input', loadOrders);
        document.getElementById('dateFromFilter').addEventListener('change', loadOrders);
        document.getElementById('dateToFilter').addEventListener('change', loadOrders);

        // Refresh functions
        function refreshOrders() { loadOrders(); }
        function refreshServices() { loadServices(); }
        function refreshUsers() { loadUsers(); }
        function refreshReviews() { loadReviews(); }
        function refreshTickets() { loadTickets(); }

        // Modal functions
        function openServiceModal() {
            alert('Service modal will be implemented here');
        }
        
        function openCategoryModal() {
            alert('Category modal will be implemented here');
        }

        // Load initial tab data
        loadTabData('<?php echo $activeTab; ?>');
    </script>
</body>
</html>