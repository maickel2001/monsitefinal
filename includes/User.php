<?php
/**
 * User Class
 * Handles user authentication, registration, and management
 */

class User {
    private $db;
    private $id;
    private $email;
    private $firstName;
    private $lastName;
    private $phone;
    private $balance;
    private $status;
    private $emailVerified;
    private $createdAt;
    private $lastLogin;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($data) {
        // Validate input data
        $this->validateRegistrationData($data);
        
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            throw new Exception("Email already registered");
        }
        
        // Hash password
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        
        // Remove password from data array
        unset($data['password']);
        unset($data['confirm_password']);
        
        try {
            $this->db->beginTransaction();
            
            $userId = $this->db->insert('users', $data);
            
            // Log the registration
            $this->logSystemEvent('info', 'User registered', [
                'user_id' => $userId,
                'email' => $data['email']
            ]);
            
            $this->db->commit();
            
            // Send welcome email
            $this->sendWelcomeEmail($data['email'], $data['first_name']);
            
            return $userId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    public function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }
        
        // Get user by email
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = :email AND status = 'active'",
            ['email' => $email]
        );
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception("Invalid email or password");
        }
        
        // Update last login
        $this->db->update(
            'users',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $user['id']]
        );
        
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = 'user';
        
        // Log the login
        $this->logSystemEvent('info', 'User logged in', [
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);
        
        return $user;
    }
    
    public function logout() {
        // Log the logout
        if (isset($_SESSION['user_id'])) {
            $this->logSystemEvent('info', 'User logged out', [
                'user_id' => $_SESSION['user_id']
            ]);
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && 
               isset($_SESSION['user_role']) && 
               $_SESSION['user_role'] === 'user' &&
               !empty($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE id = :id AND status = 'active'",
                ['id' => $_SESSION['user_id']]
            );
            
            if (!$user) {
                // L'utilisateur n'existe pas ou n'est pas actif
                $this->logout();
                return null;
            }
            
            return $user;
        } catch (Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }
    
    public function updateProfile($userId, $data) {
        // Validate input
        $this->validateProfileData($data);
        
        // Remove sensitive fields
        unset($data['password']);
        unset($data['confirm_password']);
        
        $result = $this->db->update(
            'users',
            $data,
            'id = :id',
            ['id' => $userId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Profile updated', [
                'user_id' => $userId
            ]);
        }
        
        return $result;
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Get current user
        $user = $this->db->fetch(
            "SELECT password_hash FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception("Current password is incorrect");
        }
        
        // Validate new password
        if (strlen($newPassword) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        
        // Hash new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
        
        $result = $this->db->update(
            'users',
            ['password_hash' => $newPasswordHash],
            'id = :id',
            ['id' => $userId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Password changed', [
                'user_id' => $userId
            ]);
        }
        
        return $result;
    }
    
    public function getOrders($userId, $limit = 10, $offset = 0) {
        return $this->db->fetchAll(
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
    }
    
    public function getOrderCount($userId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        return $result['count'] ?? 0;
    }
    
    private function validateRegistrationData($data) {
        $required = ['email', 'password', 'confirm_password', 'first_name', 'last_name'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        if (strlen($data['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception("Passwords do not match");
        }
        
        if (strlen($data['first_name']) < 2 || strlen($data['last_name']) < 2) {
            throw new Exception("First and last names must be at least 2 characters long");
        }
    }
    
    private function validateProfileData($data) {
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        if (isset($data['first_name']) && strlen($data['first_name']) < 2) {
            throw new Exception("First name must be at least 2 characters long");
        }
        
        if (isset($data['last_name']) && strlen($data['last_name']) < 2) {
            throw new Exception("Last name must be at least 2 characters long");
        }
    }
    
    private function emailExists($email) {
        $result = $this->db->fetch(
            "SELECT id FROM users WHERE email = :email",
            ['email' => $email]
        );
        
        return $result !== false;
    }
    
    private function sendWelcomeEmail($email, $firstName) {
        // This would integrate with your email system
        // For now, just log it
        $this->logSystemEvent('info', 'Welcome email sent', [
            'email' => $email,
            'first_name' => $firstName
        ]);
    }
    
    private function logSystemEvent($level, $message, $context = []) {
        try {
            $this->db->insert('system_logs', [
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'user_id' => $_SESSION['user_id'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Failed to log system event: " . $e->getMessage());
        }
    }
}