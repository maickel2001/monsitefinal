<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';

$user = new User();
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
        
        $user->login($email, $password);
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Redirect if already logged in
if ($user->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Sign in to your account and manage your social media marketing campaigns.">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #1d1d1f;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        /* Container */
        .container {
            max-width: 450px;
            width: 100%;
        }
        
        /* Form Card */
        .form-card {
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
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
        
        .form-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
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
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #007AFF;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .form-input.error {
            border-color: #dc3545;
        }
        
        .form-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }
        
        .form-error.show {
            display: block;
        }
        
        /* Password Toggle */
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.25rem;
        }
        
        .password-toggle:hover {
            color: #007AFF;
        }
        
        /* Submit Button */
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #007AFF, #5856D6);
            color: white;
            margin-bottom: 1.5rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 122, 255, 0.3);
        }
        
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Forgot Password */
        .forgot-password {
            text-align: right;
            margin-bottom: 1.5rem;
        }
        
        .forgot-password a {
            color: #007AFF;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        /* Register Link */
        .register-link {
            text-align: center;
            color: #6c757d;
        }
        
        .register-link a {
            color: #007AFF;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        /* Back to Home */
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .form-card {
                padding: 2rem;
            }
            
            .container {
                padding: 0;
            }
        }
        
        /* Loading State */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Remember Me */
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #007AFF;
        }
        
        .remember-me label {
            font-size: 0.875rem;
            color: #6c757d;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <a href="index.php" class="logo">
                    <i class="fas fa-rocket"></i> <?php echo APP_NAME; ?>
                </a>
                <h1 class="form-title">Welcome Back</h1>
                <p class="form-subtitle">Sign in to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <div class="form-error" id="email_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-input" required>
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-error" id="password_error"></div>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot your password?</a>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="spinner" style="display: none;"></span>
                </button>
            </form>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Sign up here</a>
            </div>
            
            <div class="back-home">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner');
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        
        // Password toggle functionality
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        });
        
        // Form validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                submitForm();
            }
        });
        
        function validateForm() {
            let isValid = true;
            clearErrors();
            
            // Email
            const email = document.getElementById('email').value.trim();
            if (!isValidEmail(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            }
            
            // Password
            const password = document.getElementById('password').value;
            if (password.length < 1) {
                showError('password', 'Password is required');
                isValid = false;
            }
            
            return isValid;
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function showError(fieldId, message) {
            const errorElement = document.getElementById(fieldId + '_error');
            const inputElement = document.getElementById(fieldId);
            
            errorElement.textContent = message;
            errorElement.classList.add('show');
            inputElement.classList.add('error');
        }
        
        function clearErrors() {
            document.querySelectorAll('.form-error').forEach(error => {
                error.classList.remove('show');
            });
            
            document.querySelectorAll('.form-input').forEach(input => {
                input.classList.remove('error');
            });
        }
        
        function submitForm() {
            // Show loading state
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
            form.classList.add('loading');
            
            // Submit the form
            form.submit();
        }
        
        // Real-time validation
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    validateField(this);
                }
            });
        });
        
        function validateField(input) {
            const fieldId = input.id;
            const value = input.value.trim();
            
            // Clear previous error
            const errorElement = document.getElementById(fieldId + '_error');
            errorElement.classList.remove('show');
            input.classList.remove('error');
            
            // Validate based on field type
            switch (fieldId) {
                case 'email':
                    if (value && !isValidEmail(value)) {
                        showError(fieldId, 'Please enter a valid email address');
                    }
                    break;
                    
                case 'password':
                    if (value && value.length < 1) {
                        showError(fieldId, 'Password is required');
                    }
                    break;
            }
        }
        
        // Enter key to submit
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT' && activeElement.type !== 'checkbox') {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
    </script>
</body>
</html>