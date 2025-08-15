<?php
/**
 * Reviews Class
 * Manages client reviews with admin approval
 */

class Reviews {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function submitReview($data) {
        // Validate review data
        $this->validateReviewData($data);
        
        // Set default status to pending
        $data['status'] = 'pending';
        
        // If user is logged in, get their info
        if (isset($_SESSION['user_id'])) {
            $user = $this->getUserById($_SESSION['user_id']);
            if ($user) {
                $data['user_id'] = $_SESSION['user_id'];
                $data['name'] = $user['first_name'] . ' ' . $user['last_name'];
                $data['email'] = $user['email'];
            }
        }
        
        $reviewId = $this->db->insert('reviews', $data);
        
        // Log the review submission
        $this->logSystemEvent('info', 'Review submitted', [
            'review_id' => $reviewId,
            'user_id' => $data['user_id'] ?? null,
            'email' => $data['email']
        ]);
        
        // Send notification to admin
        $this->notifyAdminOfNewReview($reviewId);
        
        return $reviewId;
    }
    
    public function getApprovedReviews($limit = 10, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT * FROM reviews 
             WHERE status = 'approved' 
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset",
            ['limit' => $limit, 'offset' => $offset]
        );
    }
    
    public function getPendingReviews($limit = 20, $offset = 0) {
        return $this->db->fetchAll(
            "SELECT r.*, u.first_name, u.last_name 
             FROM reviews r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE r.status = 'pending' 
             ORDER BY r.created_at ASC 
             LIMIT :limit OFFSET :offset",
            ['limit' => $limit, 'offset' => $offset]
        );
    }
    
    public function getAllReviews($filters = [], $page = 1, $limit = 20) {
        $where = "1=1";
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['rating'])) {
            $where .= " AND r.rating = :rating";
            $params['rating'] = $filters['rating'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND r.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND r.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $where .= " AND (r.name LIKE :search OR r.title LIKE :search OR r.content LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get reviews
        $reviews = $this->db->fetchAll(
            "SELECT r.*, u.first_name, u.last_name 
             FROM reviews r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE {$where} 
             ORDER BY r.created_at DESC 
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $limit, 'offset' => $offset])
        );
        
        // Get total count for pagination
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reviews r WHERE {$where}",
            $params
        );
        
        return [
            'reviews' => $reviews,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    public function getReviewById($id) {
        return $this->db->fetch(
            "SELECT r.*, u.first_name, u.last_name 
             FROM reviews r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE r.id = :id",
            ['id' => $id]
        );
    }
    
    public function approveReview($reviewId, $adminNotes = '') {
        $data = [
            'status' => 'approved',
            'admin_notes' => $adminNotes
        ];
        
        $result = $this->db->update(
            'reviews',
            $data,
            'id = :id',
            ['id' => $reviewId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Review approved', [
                'review_id' => $reviewId,
                'admin_id' => $_SESSION['admin_id'] ?? null
            ]);
            
            // Send approval notification to user if they have an account
            $review = $this->getReviewById($reviewId);
            if ($review && $review['user_id']) {
                $this->notifyUserOfReviewApproval($review['user_id'], $reviewId);
            }
        }
        
        return $result;
    }
    
    public function rejectReview($reviewId, $adminNotes = '') {
        $data = [
            'status' => 'rejected',
            'admin_notes' => $adminNotes
        ];
        
        $result = $this->db->update(
            'reviews',
            $data,
            'id = :id',
            ['id' => $reviewId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Review rejected', [
                'review_id' => $reviewId,
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'notes' => $adminNotes
            ]);
            
            // Send rejection notification to user if they have an account
            $review = $this->getReviewById($reviewId);
            if ($review && $review['user_id']) {
                $this->notifyUserOfReviewRejection($review['user_id'], $reviewId, $adminNotes);
            }
        }
        
        return $result;
    }
    
    public function updateReview($reviewId, $data) {
        // Validate data
        $this->validateReviewData($data);
        
        $result = $this->db->update(
            'reviews',
            $data,
            'id = :id',
            ['id' => $reviewId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Review updated', [
                'review_id' => $reviewId,
                'admin_id' => $_SESSION['admin_id'] ?? null
            ]);
        }
        
        return $result;
    }
    
    public function deleteReview($reviewId) {
        $result = $this->db->delete('reviews', 'id = :id', ['id' => $reviewId]);
        
        if ($result) {
            $this->logSystemEvent('info', 'Review deleted', [
                'review_id' => $reviewId,
                'admin_id' => $_SESSION['admin_id'] ?? null
            ]);
        }
        
        return $result;
    }
    
    public function getReviewStats() {
        $stats = [];
        
        // Total reviews by status
        $statusStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM reviews GROUP BY status"
        );
        
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }
        
        // Total reviews by rating
        $ratingStats = $this->db->fetchAll(
            "SELECT rating, COUNT(*) as count FROM reviews WHERE status = 'approved' GROUP BY rating"
        );
        
        foreach ($ratingStats as $stat) {
            $stats['by_rating'][$stat['rating']] = $stat['count'];
        }
        
        // Average rating
        $avgRating = $this->db->fetch(
            "SELECT AVG(rating) as average FROM reviews WHERE status = 'approved'"
        );
        
        $stats['average_rating'] = round($avgRating['average'] ?? 0, 1);
        
        // Total reviews
        $totalReviews = $this->db->fetch("SELECT COUNT(*) as count FROM reviews");
        $stats['total_reviews'] = $totalReviews['count'];
        
        return $stats;
    }
    
    public function getUserReviews($userId, $limit = 10, $offset = 0) {
        $reviews = $this->db->fetchAll(
            "SELECT * FROM reviews 
             WHERE user_id = :user_id 
             ORDER BY created_at DESC 
             LIMIT :limit OFFSET :offset",
            [
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => $offset
            ]
        );
        
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM reviews WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        return [
            'reviews' => $reviews,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => ceil(($offset / $limit) + 1)
        ];
    }
    
    private function validateReviewData($data) {
        $required = ['name', 'email', 'rating', 'title', 'content'];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Field '{$field}' is required");
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        if (!is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            throw new Exception("Rating must be between 1 and 5");
        }
        
        if (strlen($data['title']) < 5 || strlen($data['title']) > 255) {
            throw new Exception("Title must be between 5 and 255 characters");
        }
        
        if (strlen($data['content']) < 10) {
            throw new Exception("Review content must be at least 10 characters long");
        }
        
        if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
            throw new Exception("Name must be between 2 and 100 characters");
        }
    }
    
    private function getUserById($id) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $id]
        );
    }
    
    private function notifyAdminOfNewReview($reviewId) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'Admin notification sent for new review', [
            'review_id' => $reviewId
        ]);
    }
    
    private function notifyUserOfReviewApproval($userId, $reviewId) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'User notification sent for review approval', [
            'user_id' => $userId,
            'review_id' => $reviewId
        ]);
    }
    
    private function notifyUserOfReviewRejection($userId, $reviewId, $notes) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'User notification sent for review rejection', [
            'user_id' => $userId,
            'review_id' => $reviewId,
            'notes' => $notes
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