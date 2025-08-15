<?php
/**
 * Services Class
 * Manages SMM services and categories
 */

class Services {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllCategories($activeOnly = true) {
        $where = $activeOnly ? "WHERE status = 'active'" : "";
        $sql = "SELECT * FROM categories {$where} ORDER BY sort_order ASC, name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getCategoryById($id) {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE id = :id",
            ['id' => $id]
        );
    }
    
    public function createCategory($data) {
        // Validate data
        $this->validateCategoryData($data);
        
        // Generate icon if not provided
        if (empty($data['icon'])) {
            $data['icon'] = $this->generateIcon($data['name']);
        }
        
        // Set default sort order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = $this->db->fetch("SELECT MAX(sort_order) as max_order FROM categories");
            $data['sort_order'] = ($maxOrder['max_order'] ?? 0) + 1;
        }
        
        $categoryId = $this->db->insert('categories', $data);
        
        // Log the creation
        $this->logSystemEvent('info', 'Category created', [
            'category_id' => $categoryId,
            'name' => $data['name']
        ]);
        
        return $categoryId;
    }
    
    public function updateCategory($id, $data) {
        // Validate data
        $this->validateCategoryData($data);
        
        $result = $this->db->update(
            'categories',
            $data,
            'id = :id',
            ['id' => $id]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Category updated', [
                'category_id' => $id,
                'name' => $data['name']
            ]);
        }
        
        return $result;
    }
    
    public function deleteCategory($id) {
        // Check if category has services
        $servicesCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM services WHERE category_id = :id",
            ['id' => $id]
        );
        
        if ($servicesCount['count'] > 0) {
            throw new Exception("Cannot delete category with existing services");
        }
        
        $result = $this->db->delete('categories', 'id = :id', ['id' => $id]);
        
        if ($result) {
            $this->logSystemEvent('info', 'Category deleted', [
                'category_id' => $id
            ]);
        }
        
        return $result;
    }
    
    public function getAllServices($activeOnly = true) {
        $where = $activeOnly ? "WHERE s.status = 'active'" : "";
        $sql = "SELECT s.*, c.name as category_name, c.icon as category_icon 
                FROM services s 
                JOIN categories c ON s.category_id = c.id 
                {$where} 
                ORDER BY c.sort_order ASC, s.name ASC";
        
        return $this->db->fetchAll($sql);
    }
    
    public function getServicesByCategory($categoryId, $activeOnly = true) {
        $where = $activeOnly ? "AND s.status = 'active'" : "";
        $sql = "SELECT s.*, c.name as category_name 
                FROM services s 
                JOIN categories c ON s.category_id = c.id 
                WHERE s.category_id = :category_id {$where} 
                ORDER BY s.name ASC";
        
        return $this->db->fetchAll($sql, ['category_id' => $categoryId]);
    }
    
    public function getServiceById($id) {
        return $this->db->fetch(
            "SELECT s.*, c.name as category_name 
             FROM services s 
             JOIN categories c ON s.category_id = c.id 
             WHERE s.id = :id",
            ['id' => $id]
        );
    }
    
    public function createService($data) {
        // Validate data
        $this->validateServiceData($data);
        
        // Calculate total for given quantity (for reference)
        if (isset($data['quantity']) && isset($data['unit_price'])) {
            $data['total_amount'] = $data['quantity'] * $data['unit_price'];
        }
        
        $serviceId = $this->db->insert('services', $data);
        
        // Log the creation
        $this->logSystemEvent('info', 'Service created', [
            'service_id' => $serviceId,
            'name' => $data['name']
        ]);
        
        return $serviceId;
    }
    
    public function updateService($id, $data) {
        // Validate data
        $this->validateServiceData($data);
        
        $result = $this->db->update(
            'services',
            $data,
            'id = :id',
            ['id' => $id]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Service updated', [
                'service_id' => $id,
                'name' => $data['name']
            ]);
        }
        
        return $result;
    }
    
    public function deleteService($id) {
        // Check if service has orders
        $ordersCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE service_id = :id",
            ['id' => $id]
        );
        
        if ($ordersCount['count'] > 0) {
            throw new Exception("Cannot delete service with existing orders");
        }
        
        $result = $this->db->delete('services', 'id = :id', ['id' => $id]);
        
        if ($result) {
            $this->logSystemEvent('info', 'Service deleted', [
                'service_id' => $id
            ]);
        }
        
        return $result;
    }
    
    public function calculateServicePrice($serviceId, $quantity) {
        $service = $this->getServiceById($serviceId);
        
        if (!$service) {
            throw new Exception("Service not found");
        }
        
        if ($quantity < $service['min_quantity']) {
            throw new Exception("Quantity must be at least {$service['min_quantity']}");
        }
        
        if ($quantity > $service['max_quantity']) {
            throw new Exception("Quantity cannot exceed {$service['max_quantity']}");
        }
        
        return [
            'unit_price' => $service['unit_price'],
            'total_price' => $service['unit_price'] * $quantity,
            'min_quantity' => $service['min_quantity'],
            'max_quantity' => $service['max_quantity']
        ];
    }
    
    public function getServicesForOrderForm() {
        $categories = $this->getAllCategories();
        $services = $this->getAllServices();
        
        $organized = [];
        foreach ($categories as $category) {
            $organized[$category['id']] = [
                'category' => $category,
                'services' => []
            ];
        }
        
        foreach ($services as $service) {
            if (isset($organized[$service['category_id']])) {
                $organized[$service['category_id']]['services'][] = $service;
            }
        }
        
        return $organized;
    }
    
    private function validateCategoryData($data) {
        if (empty($data['name'])) {
            throw new Exception("Category name is required");
        }
        
        if (strlen($data['name']) < 2) {
            throw new Exception("Category name must be at least 2 characters long");
        }
        
        if (strlen($data['name']) > 100) {
            throw new Exception("Category name cannot exceed 100 characters");
        }
    }
    
    private function validateServiceData($data) {
        if (empty($data['name'])) {
            throw new Exception("Service name is required");
        }
        
        if (empty($data['category_id'])) {
            throw new Exception("Category is required");
        }
        
        if (!isset($data['unit_price']) || $data['unit_price'] <= 0) {
            throw new Exception("Unit price must be greater than 0");
        }
        
        if (!isset($data['min_quantity']) || $data['min_quantity'] < 1) {
            throw new Exception("Minimum quantity must be at least 1");
        }
        
        if (!isset($data['max_quantity']) || $data['max_quantity'] < $data['min_quantity']) {
            throw new Exception("Maximum quantity must be greater than minimum quantity");
        }
        
        // Check if category exists
        $category = $this->getCategoryById($data['category_id']);
        if (!$category) {
            throw new Exception("Selected category does not exist");
        }
    }
    
    private function generateIcon($categoryName) {
        // Simple icon mapping based on category name
        $iconMap = [
            'social' => 'fas fa-share-alt',
            'youtube' => 'fab fa-youtube',
            'instagram' => 'fab fa-instagram',
            'tiktok' => 'fab fa-tiktok',
            'twitter' => 'fab fa-twitter',
            'facebook' => 'fab fa-facebook',
            'linkedin' => 'fab fa-linkedin',
            'snapchat' => 'fab fa-snapchat',
            'pinterest' => 'fab fa-pinterest',
            'telegram' => 'fab fa-telegram',
            'whatsapp' => 'fab fa-whatsapp',
            'discord' => 'fab fa-discord',
            'reddit' => 'fab fa-reddit',
            'twitch' => 'fab fa-twitch',
            'spotify' => 'fab fa-spotify'
        ];
        
        $name = strtolower($categoryName);
        
        foreach ($iconMap as $keyword => $icon) {
            if (strpos($name, $keyword) !== false) {
                return $icon;
            }
        }
        
        // Default icon
        return 'fas fa-star';
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