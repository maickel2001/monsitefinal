<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Admin.php';

$admin = new Admin();
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
            throw new Exception("Invalid request");
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            throw new Exception("Email and password are required");
        }
        
        $admin->login($email, $password);
        
        // Redirect to admin dashboard
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Redirect if already logged in
if ($admin->isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
        }
        .container { max-width: 400px; width: 100%; }
        .form-card { 
            background: white; 
            padding: 3rem; 
            border-radius: 24px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.1); 
            border: 1px solid rgba(0,0,0,0.05);
        }
        .form-header { text-align: center; margin-bottom: 2rem; }
        .logo { 
            font-size: 2rem; 
            font-weight: 700; 
            color: #007AFF; 
            margin-bottom: 0.5rem; 
            display: block;
        }
        .form-title { 
            font-size: 1.75rem; 
            font-weight: 700; 
            color: #1d1d1f; 
            margin-bottom: 0.5rem; 
        }
        .form-subtitle { color: #6c757d; }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 600; 
            color: #1d1d1f; 
        }
        .form-input { 
            width: 100%; 
            padding: 1rem; 
            border: 2px solid #e9ecef; 
            border-radius: 12px; 
            font-size: 1rem; 
            transition: all 0.3s ease;
        }
        .form-input:focus { 
            outline: none; 
            border-color: #007AFF; 
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        .btn { 
            width: 100%; 
            padding: 1rem; 
            border: none; 
            border-radius: 12px; 
            font-size: 1rem; 
            font-weight: 600; 
            background: linear-gradient(135deg, #007AFF, #5856D6); 
            color: white; 
            cursor: pointer; 
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 122, 255, 0.3);
        }
        .message { 
            padding: 1rem; 
            border-radius: 12px; 
            margin-bottom: 1.5rem; 
            text-align: center; 
            font-weight: 500;
        }
        .message.error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        .back-home { 
            text-align: center; 
            margin-top: 1.5rem; 
        }
        .back-home a { 
            color: #6c757d; 
            text-decoration: none; 
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .back-home a:hover {
            color: #007AFF;
        }
        .admin-badge {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <div class="admin-badge">
                    <i class="fas fa-shield-alt"></i> Admin Access
                </div>
                <a href="../index.php" class="logo">
                    <i class="fas fa-rocket"></i> <?php echo APP_NAME; ?>
                </a>
                <h1 class="form-title">Admin Login</h1>
                <p class="form-subtitle">Access the admin dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="Enter admin email">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required
                           placeholder="Enter admin password">
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Admin Panel
                </button>
            </form>
            
            <div class="back-home">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>