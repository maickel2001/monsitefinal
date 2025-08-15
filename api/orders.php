<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Orders.php';
require_once '../includes/User.php';

$orders = new Orders();
$user = new User();

$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'user_orders':
                        if (!$user->isLoggedIn()) {
                            throw new Exception('User not authenticated');
                        }
                        $currentUser = $user->getCurrentUser();
                        $userOrders = $orders->getUserOrders($currentUser['id']);
                        $response['success'] = true;
                        $response['data'] = $userOrders;
                        break;
                        
                    case 'order_details':
                        if (!isset($_GET['order_id'])) {
                            throw new Exception('Order ID required');
                        }
                        $orderId = (int)$_GET['order_id'];
                        $order = $orders->getOrderById($orderId);
                        if (!$order) {
                            throw new Exception('Order not found');
                        }
                        $response['success'] = true;
                        $response['data'] = $order;
                        break;
                        
                    default:
                        throw new Exception('Invalid action');
                }
            } else {
                throw new Exception('Action parameter required');
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$user->isLoggedIn()) {
                throw new Exception('User not authenticated');
            }
            
            if (isset($_GET['action']) && $_GET['action'] === 'create') {
                $currentUser = $user->getCurrentUser();
                
                // Validate required fields
                $required = ['service_id', 'link', 'quantity'];
                foreach ($required as $field) {
                    if (empty($input[$field])) {
                        throw new Exception("Field '$field' is required");
                    }
                }
                
                $orderData = [
                    'user_id' => $currentUser['id'],
                    'service_id' => (int)$input['service_id'],
                    'link' => trim($input['link']),
                    'quantity' => (int)$input['quantity']
                ];
                
                $orderId = $orders->createOrder($orderData);
                $response['success'] = true;
                $response['message'] = 'Order created successfully';
                $response['data'] = ['order_id' => $orderId];
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        case 'PUT':
            if (!$user->isLoggedIn()) {
                throw new Exception('User not authenticated');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($_GET['action']) && $_GET['action'] === 'update_status') {
                if (!isset($input['order_id']) || !isset($input['status'])) {
                    throw new Exception('Order ID and status required');
                }
                
                $orderId = (int)$input['order_id'];
                $status = $input['status'];
                
                // Verify user owns this order
                $order = $orders->getOrderById($orderId);
                if (!$order || $order['user_id'] != $user->getCurrentUser()['id']) {
                    throw new Exception('Order not found or access denied');
                }
                
                $orders->updateOrderStatus($orderId, $status);
                $response['success'] = true;
                $response['message'] = 'Order status updated successfully';
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
} catch (Error $e) {
    $response['message'] = 'Internal server error';
    http_response_code(500);
}

echo json_encode($response);
exit;