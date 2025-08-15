<?php
/**
 * Support Tickets Class
 * Manages support tickets and responses
 */

class Tickets {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createTicket($data) {
        // Validate ticket data
        $this->validateTicketData($data);
        
        // Set default values
        $data['status'] = 'open';
        $data['priority'] = $data['priority'] ?? 'medium';
        
        // If user is logged in, get their info
        if (isset($_SESSION['user_id'])) {
            $data['user_id'] = $_SESSION['user_id'];
        }
        
        $ticketId = $this->db->insert('tickets', $data);
        
        // Log the ticket creation
        $this->logSystemEvent('info', 'Support ticket created', [
            'ticket_id' => $ticketId,
            'user_id' => $data['user_id'] ?? null,
            'subject' => $data['subject']
        ]);
        
        // Send notification to admin
        $this->notifyAdminOfNewTicket($ticketId);
        
        return $ticketId;
    }
    
    public function getTicketById($id) {
        return $this->db->fetch(
            "SELECT t.*, u.email as user_email, u.first_name, u.last_name,
                    a.name as admin_name 
             FROM tickets t 
             LEFT JOIN users u ON t.user_id = u.id 
             LEFT JOIN admins a ON t.assigned_to = a.id 
             WHERE t.id = :id",
            ['id' => $id]
        );
    }
    
    public function getUserTickets($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $tickets = $this->db->fetchAll(
            "SELECT * FROM tickets 
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
            "SELECT COUNT(*) as count FROM tickets WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        return [
            'tickets' => $tickets,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    public function getAllTickets($filters = [], $page = 1, $limit = 20) {
        $where = "1=1";
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $where .= " AND t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $where .= " AND t.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }
        
        if (!empty($filters['date_from'])) {
            $where .= " AND t.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $where .= " AND t.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        
        if (!empty($filters['search'])) {
            $where .= " AND (t.subject LIKE :search OR t.message LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get tickets
        $tickets = $this->db->fetchAll(
            "SELECT t.*, u.email as user_email, u.first_name, u.last_name,
                    a.name as admin_name 
             FROM tickets t 
             LEFT JOIN users u ON t.user_id = u.id 
             LEFT JOIN admins a ON t.assigned_to = a.id 
             WHERE {$where} 
             ORDER BY 
                CASE t.priority 
                    WHEN 'urgent' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'medium' THEN 3 
                    WHEN 'low' THEN 4 
                END,
                t.created_at ASC 
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $limit, 'offset' => $offset])
        );
        
        // Get total count for pagination
        $totalCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM tickets t WHERE {$where}",
            $params
        );
        
        return [
            'tickets' => $tickets,
            'total' => $totalCount['count'],
            'pages' => ceil($totalCount['count'] / $limit),
            'current_page' => $page
        ];
    }
    
    public function updateTicketStatus($ticketId, $status, $assignedTo = null) {
        $data = ['status' => $status];
        
        if ($assignedTo !== null) {
            $data['assigned_to'] = $assignedTo;
        }
        
        $result = $this->db->update(
            'tickets',
            $data,
            'id = :id',
            ['id' => $ticketId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Ticket status updated', [
                'ticket_id' => $ticketId,
                'status' => $status,
                'assigned_to' => $assignedTo,
                'admin_id' => $_SESSION['admin_id'] ?? null
            ]);
            
            // Send status update notification to user
            $ticket = $this->getTicketById($ticketId);
            if ($ticket && $ticket['user_id']) {
                $this->notifyUserOfTicketUpdate($ticket['user_id'], $ticketId, $status);
            }
        }
        
        return $result;
    }
    
    public function addResponse($ticketId, $message, $isInternal = false) {
        // Validate response data
        if (empty($message)) {
            throw new Exception("Response message is required");
        }
        
        $data = [
            'ticket_id' => $ticketId,
            'message' => $message,
            'is_internal' => $isInternal
        ];
        
        // Set user or admin ID based on who's responding
        if (isset($_SESSION['user_id'])) {
            $data['user_id'] = $_SESSION['user_id'];
        } elseif (isset($_SESSION['admin_id'])) {
            $data['admin_id'] = $_SESSION['admin_id'];
        } else {
            throw new Exception("User not authenticated");
        }
        
        $responseId = $this->db->insert('ticket_responses', $data);
        
        // Update ticket status to in_progress if it was open
        $ticket = $this->getTicketById($ticketId);
        if ($ticket && $ticket['status'] === 'open') {
            $this->updateTicketStatus($ticketId, 'in_progress');
        }
        
        // Log the response
        $this->logSystemEvent('info', 'Ticket response added', [
            'ticket_id' => $ticketId,
            'response_id' => $responseId,
            'is_internal' => $isInternal,
            'user_id' => $data['user_id'] ?? null,
            'admin_id' => $data['admin_id'] ?? null
        ]);
        
        // Send notification
        if (isset($data['user_id'])) {
            // User responded, notify admin
            $this->notifyAdminOfUserResponse($ticketId, $responseId);
        } else {
            // Admin responded, notify user
            $this->notifyUserOfAdminResponse($ticket['user_id'], $ticketId, $responseId);
        }
        
        return $responseId;
    }
    
    public function getTicketResponses($ticketId) {
        return $this->db->fetchAll(
            "SELECT r.*, u.first_name as user_first_name, u.last_name as user_last_name,
                    a.name as admin_name 
             FROM ticket_responses r 
             LEFT JOIN users u ON r.user_id = u.id 
             LEFT JOIN admins a ON r.admin_id = a.id 
             WHERE r.ticket_id = :ticket_id 
             ORDER BY r.created_at ASC",
            ['ticket_id' => $ticketId]
        );
    }
    
    public function closeTicket($ticketId, $resolution = '') {
        $data = ['status' => 'closed'];
        
        if (!empty($resolution)) {
            $data['admin_notes'] = $resolution;
        }
        
        $result = $this->db->update(
            'tickets',
            $data,
            'id = :id',
            ['id' => $ticketId]
        );
        
        if ($result) {
            $this->logSystemEvent('info', 'Ticket closed', [
                'ticket_id' => $ticketId,
                'admin_id' => $_SESSION['admin_id'] ?? null,
                'resolution' => $resolution
            ]);
            
            // Send closure notification to user
            $ticket = $this->getTicketById($ticketId);
            if ($ticket && $ticket['user_id']) {
                $this->notifyUserOfTicketClosure($ticket['user_id'], $ticketId, $resolution);
            }
        }
        
        return $result;
    }
    
    public function getTicketStats() {
        $stats = [];
        
        // Total tickets by status
        $statusStats = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM tickets GROUP BY status"
        );
        
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }
        
        // Total tickets by priority
        $priorityStats = $this->db->fetchAll(
            "SELECT priority, COUNT(*) as count FROM tickets GROUP BY priority"
        );
        
        foreach ($priorityStats as $stat) {
            $stats['by_priority'][$stat['priority']] = $stat['count'];
        }
        
        // Open tickets count
        $openTickets = $this->db->fetch(
            "SELECT COUNT(*) as count FROM tickets WHERE status IN ('open', 'in_progress')"
        );
        $stats['open_tickets'] = $openTickets['count'];
        
        // Total tickets
        $totalTickets = $this->db->fetch("SELECT COUNT(*) as count FROM tickets");
        $stats['total_tickets'] = $totalTickets['count'];
        
        return $stats;
    }
    
    private function validateTicketData($data) {
        if (empty($data['subject'])) {
            throw new Exception("Ticket subject is required");
        }
        
        if (empty($data['message'])) {
            throw new Exception("Ticket message is required");
        }
        
        if (strlen($data['subject']) < 5 || strlen($data['subject']) > 255) {
            throw new Exception("Subject must be between 5 and 255 characters");
        }
        
        if (strlen($data['message']) < 10) {
            throw new Exception("Message must be at least 10 characters long");
        }
        
        if (isset($data['priority']) && !in_array($data['priority'], ['low', 'medium', 'high', 'urgent'])) {
            throw new Exception("Invalid priority level");
        }
    }
    
    private function notifyAdminOfNewTicket($ticketId) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'Admin notification sent for new ticket', [
            'ticket_id' => $ticketId
        ]);
    }
    
    private function notifyUserOfTicketUpdate($userId, $ticketId, $status) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'User notification sent for ticket update', [
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'status' => $status
        ]);
    }
    
    private function notifyAdminOfUserResponse($ticketId, $responseId) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'Admin notification sent for user response', [
            'ticket_id' => $ticketId,
            'response_id' => $responseId
        ]);
    }
    
    private function notifyUserOfAdminResponse($userId, $ticketId, $responseId) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'User notification sent for admin response', [
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'response_id' => $responseId
        ]);
    }
    
    private function notifyUserOfTicketClosure($userId, $ticketId, $resolution) {
        // This would integrate with your email system
        $this->logSystemEvent('info', 'User notification sent for ticket closure', [
            'user_id' => $userId,
            'ticket_id' => $ticketId,
            'resolution' => $resolution
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