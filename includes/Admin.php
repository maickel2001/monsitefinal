<?php
/**
 * Admin Class
 * Handles admin authentication and management
 */

class Admin {
    private $db;
    private $id;
    private $email;
    private $name;
    private $role;
    private $status;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }
        
        // Get admin by email
        $admin = $this->db->fetch(
            "SELECT * FROM admins WHERE email = :email AND status = 'active'",
            ['email' => $email]
        );
        
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            throw new Exception("Invalid email or password");
        }
        
        // Update last login
        $this->db->update(
            'admins',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $admin['id']]
        );
        
        // Set session data
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['user_role'] = 'admin';
        
        // Log the login
        $this->logSystemEvent('info', 'Admin logged in', [
            'admin_id' => $admin['id'],
            'email' => $admin['email'],
            'role' => $admin['role']
        ]);
        
        return $admin;
    }
    
    public function logout() {
        // Log the logout
        if (isset($_SESSION['admin_id'])) {
            $this->logSystemEvent('info', 'Admin logged out', [
                'admin_id' => $_SESSION['admin_id']
            ]);
        }
        
        // Clear admin session
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['user_role']);
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function hasRole($requiredRole) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $roleHierarchy = [
            'moderator' => 1,
            'admin' => 2,
            'super_admin' => 3
        ];
        
        $userRoleLevel = $roleHierarchy[$_SESSION['admin_role']] ?? 0;
        $requiredRoleLevel = $roleHierarchy[$requiredRole] ?? 0;
        
        return $userRoleLevel >= $requiredRoleLevel;
    }
    
    public function getCurrentAdmin() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->db->fetch(
            "SELECT * FROM admins WHERE id = :id",
            ['id' => $_SESSION['admin_id']]
        );
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        // Total orders by status
        $orderStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM orders GROUP BY status"
        );
        
        foreach ($orderStats as $stat) {
            $stats['orders'][$stat['status']] = $stat['count'];
        }
        
        // Total users
        $userCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE status = 'active'"
        );
        $stats['total_users'] = $userCount['count'];
        
        // Total revenue (completed orders)
        $revenue = $this->db->fetch(
            "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'"
        );
        $stats['total_revenue'] = $revenue['total'] ?? 0;
        
        // Pending reviews
        $pendingReviews = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'"
        );
        $stats['pending_reviews'] = $pendingReviews['count'];
        
        // Open tickets
        $openTickets = $this->db->fetch(
            "SELECT COUNT(*) as count FROM tickets WHERE status IN ('open', 'in_progress')"
        );
        $stats['open_tickets'] = $openTickets['count'];
        
        return $stats;
    }
    
    public function getMonthlyStats($months = 6) {
        $stats = [];
        
        for ($i = 0; $i < $months; $i++) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $startDate = $date . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            
            // Orders count
            $orders = $this->db->fetch(
                "SELECT COUNT(*) as count FROM orders WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate . ' 23:59:59']
            );
            
            // Revenue
            $revenue = $this->db->fetch(
                "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed' AND created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate . ' 23:59:59']
            );
            
            // New users
            $users = $this->db->fetch(
                "SELECT COUNT(*) as count FROM users WHERE created_at BETWEEN :start AND :end",
                ['start' => $startDate, 'end' => $endDate . ' 23:59:59']
            );
            
            $stats[$date] = [
                'orders' => $orders['count'],
                'revenue' => $revenue['total'] ?? 0,
                'users' => $users['count']
            ];
        }
        
        return array_reverse($stats);
    }
    
    public function getAllOrders($filters = [], $page = 1, $limit = 20) {
        $where = "1=1";
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND o.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['user_id'])) {
            $where .= " AND o.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['service_id'])) {
            $where .= " AND o.service_id = :service_id";
            $params['service_id'] = $filters['service_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND o.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND o.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get orders
        $orders = $this->db->fetchAll(
            "SELECT o.*, u.email as user_email, u.first_name, u.last_name, 
                    s.name as service_name, c.name as category_name 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             JOIN services s ON o.service_id = s.id 
             JOIN categories c ON s.category_id = c.id 
             WHERE {$where} 
             ORDER BY o.created_at DESC 
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $limit, 'offset' => $offset])
        );
        
        // Get total count for pagination
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders o WHERE {$where}",
            $params
        );
        
        return [
            'orders' => $orders,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    public function updateOrderStatus($orderId, $status, $notes = '', $cancellationReason = '') {
        $data = ['status' => $status];
        
        if (!empty($notes)) {
            $data['admin_notes'] = $notes;
        }
        
        if ($status === 'cancelled' && !empty($cancellationReason)) {
            $data['cancellation_reason'] = $cancellationReason;
        }
        
        if ($status === 'in_progress') {
            $data['started_at'] = date('Y-m-d H:i:s');
        }
        
        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->db->update(
            'orders',
            $data,
            'id = :id',
            ['id' => $orderId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Order status updated', [
                'order_id' => $orderId,
                'status' => $status,
                'admin_id' => $_SESSION['admin_id']
            ]);
        }
        
        return $result;
    }
    
    public function getAllUsers($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $users = $this->db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            ['limit' => $limit, 'offset' => $offset]
        );
        
        $totalCount = $this->db->fetch("SELECT COUNT(*) as count FROM users");
        
        return [
            'users' => $users,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    public function updateUserStatus($userId, $status) {
        $result = $this->db->update(
            'users',
            ['status' => $status],
            'id = :id',
            ['id' => $userId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'User status updated', [
                'user_id' => $userId,
                'status' => $status,
                'admin_id' => $_SESSION['admin_id']
            ]);
        }
        
        return $result;
    }
    
    private function logSystemEvent($level, $message, $context = []) {
        try {
            $this->db->insert('system_logs', [
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Failed to log system event: " . $e->getMessage());
        }
    }
}