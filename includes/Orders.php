<?php
/**
 * Orders Class
 * Manages SMM orders
 */

class Orders {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createOrder($data) {
        // Validate order data
        $this->validateOrderData($data);
        
        // Calculate total amount
        $service = $this->getServiceById($data['service_id']);
        if (!$service) {
            throw new Exception("Service not found");
        }
        
        $data['total_amount'] = $service['unit_price'] * $data['quantity'];
        
        // Check if user has sufficient balance (if applicable)
        if (isset($data['user_id'])) {
            $user = $this->getUserById($data['user_id']);
            if ($user && $user['balance'] < $data['total_amount']) {
                throw new Exception("Insufficient balance");
            }
        }
        
        try {
            $this->db->beginTransaction();
            
            $orderId = $this->db->insert('orders', $data);
            
            // Deduct balance if user has sufficient funds
            if (isset($data['user_id']) && $user && $user['balance'] >= $data['total_amount']) {
                $this->db->update(
                    'users',
                    ['balance' => $user['balance'] - $data['total_amount']],
                    'id = :id',
                    ['id' => $data['user_id']]
                );
            }
            
            // Log the order creation
            $this->logSystemEvent('info', 'Order created', [
                'order_id' => $orderId,
                'user_id' => $data['user_id'] ?? null,
                'service_id' => $data['service_id'],
                'amount' => $data['total_amount']
            ]);
            
            $this->db->commit();
            
            // Send order confirmation email
            if (isset($data['user_id'])) {
                $this->sendOrderConfirmationEmail($data['user_id'], $orderId);
            }
            
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function getOrderById($id) {
        return $this->db->fetch(
            "SELECT o.*, u.email as user_email, u.first_name, u.last_name, 
                    s.name as service_name, s.unit_price, s.min_quantity, s.max_quantity,
                    c.name as category_name 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             JOIN services s ON o.service_id = s.id 
             JOIN categories c ON s.category_id = c.id 
             WHERE o.id = :id",
            ['id' => $id]
        );
    }
    
    public function getUserOrders($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $orders = $this->db->fetchAll(
            "SELECT o.*, s.name as service_name, c.name as category_name 
             FROM orders o 
             JOIN services s ON o.service_id = s.id 
             JOIN categories c ON s.category_id = c.id 
             WHERE o.user_id = :user_id 
             ORDER BY o.created_at DESC 
             LIMIT :limit OFFSET :offset",
            [
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => $offset
            ]
        );
        
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        return [
            'orders' => $orders,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    public function updateOrderStatus($orderId, $status, $adminNotes = '', $cancellationReason = '') {
        $data = ['status' => $status];
        
        if (!empty($adminNotes)) {
            $data['admin_notes'] = $adminNotes;
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
        
        // If order is cancelled or refunded, refund the user's balance
        if (in_array($status, ['cancelled', 'refunded'])) {
            $order = $this->getOrderById($orderId);
            if ($order && $order['user_id']) {
                $this->refundUserBalance($order['user_id'], $order['total_amount']);
            }
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
                'admin_notes' => $adminNotes
            ]);
            
            // Send status update email
            $order = $this->getOrderById($orderId);
            if ($order && $order['user_id']) {
                $this->sendStatusUpdateEmail($order['user_id'], $orderId, $status, $adminNotes);
            }
        }
        
        return $result;
    }
    
    public function addPaymentProof($orderId, $proofFile) {
        // Validate file
        if (!$this->validateProofFile($proofFile)) {
            throw new Exception("Invalid payment proof file");
        }
        
        // Upload file
        $uploadPath = $this->uploadProofFile($proofFile);
        
        $result = $this->db->update(
            'orders',
            ['payment_proof' => $uploadPath],
            'id = :id',
            ['id' => $orderId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Payment proof added', [
                'order_id' => $orderId,
                'proof_file' => $uploadPath
            ]);
        }
        
        return $result;
    }
    
    public function getOrderStats($userId = null) {
        $where = $userId ? "WHERE user_id = :user_id" : "";
        $params = $userId ? ['user_id' => $userId] : [];
        
        $stats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count, SUM(total_amount) as total 
             FROM orders {$where} 
             GROUP BY status",
            $params
        );
        
        $result = [];
        foreach ($stats as $stat) {
            $result[$stat['status']] = [
                'count' => $stat['count'],
                'total' => $stat['total'] ?? 0
            ];
        }
        
        return $result;
    }
    
    public function searchOrders($filters = [], $page = 1, $limit = 20) {
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
        
        if (!empty($filters['search'])) {
            $where .= " AND (o.link LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
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
            "SELECT COUNT(*) as count FROM orders o JOIN users u ON o.user_id = u.id WHERE {$where}",
            $params
        );
        
        return [
            'orders' => $orders,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    private function validateOrderData($data) {
        $required = ['service_id', 'link', 'quantity'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        if (!filter_var($data['link'], FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid link format");
        }
        
        if (!is_numeric($data['quantity']) || $data['quantity'] < 1) {
            throw new Exception("Quantity must be a positive number");
        }
    }
    
    private function getServiceById($id) {
        return $this->db->fetch(
            "SELECT * FROM services WHERE id = :id AND status = 'active'",
            ['id' => $id]
        );
    }
    
    private function getUserById($id) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $id]
        );
    }
    
    private function refundUserBalance($userId, $amount) {
        $this->db->update(
            'users',
            ['balance' => 'balance + :amount'],
            'id = :id',
            ['id' => $userId, 'amount' => $amount]
        );
    }
    
    private function validateProofFile($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return false;
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return false;
        }
        
        return true;
    }
    
    private function uploadProofFile($file) {
        $uploadDir = UPLOAD_DIR . 'proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to upload file");
        }
        
        return 'uploads/proofs/' . $filename;
    }
    
    private function sendOrderConfirmationEmail($userId, $orderId) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'Order confirmation email sent', [
            'user_id' => $userId,
            'order_id' => $orderId
        ]);
    }
    
    private function sendStatusUpdateEmail($userId, $orderId, $status, $notes) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'Status update email sent', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'status' => $status
        ]);
    }
    
    private function logSystemEvent($level, $message, $context = []) {
        try {
            $this->db->insert('system_logs', [
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'user_id' => $_SESSION['user_id'] ?? null,
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Failed to log system event: " . $e->getMessage());
        }
    }
}