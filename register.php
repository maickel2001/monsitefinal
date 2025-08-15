<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';

$user = new User();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[CSRF_TOKEN_NAME]) {
            throw new Exception("Invalid request");
        }
        
        $userData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];
        
        $userId = $user->register($userData);
        $success = "Registration successful! You can now login to your account.";
        
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
    <title>Register - <?php echo APP_NAME; ?></title>
    <meta name="description" content="Create your account and start boosting your social media presence today.">
    
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
            max-width: 500px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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
        
        .form-input.success {
            border-color: #28a745;
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
        
        /* Password Strength */
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
        
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
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Login Link */
        .login-link {
            text-align: center;
            color: #6c757d;
        }
        
        .login-link a {
            color: #007AFF;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
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
            
            .form-row {
                grid-template-columns: 1fr;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="form-card">
            <div class="form-header">
                <a href="index.php" class="logo">
                    <i class="fas fa-rocket"></i> <?php echo APP_NAME; ?>
                </a>
                <h1 class="form-title">Create Your Account</h1>
                <p class="form-subtitle">Start boosting your social media presence today</p>
            </div>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form id="registerForm" method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION[CSRF_TOKEN_NAME]; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" required 
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
                        <div class="form-error" id="first_name_error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" required 
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
                        <div class="form-error" id="last_name_error"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <div class="form-error" id="email_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number (Optional)</label>
                    <input type="tel" id="phone" name="phone" class="form-input" 
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    <div class="form-error" id="phone_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="form-error" id="password_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                    <div class="form-error" id="confirm_password_error"></div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <span class="btn-text">Create Account</span>
                    <span class="spinner" style="display: none;"></span>
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign in here</a>
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
        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const spinner = submitBtn.querySelector('.spinner');
        
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('passwordStrengthBar');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrength(strength);
        });
        
        function checkPasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;
            
            if (score < 2) return 'weak';
            if (score < 3) return 'fair';
            if (score < 4) return 'good';
            return 'strong';
        }
        
        function updatePasswordStrength(strength) {
            strengthBar.className = 'password-strength-bar strength-' + strength;
        }
        
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
            
            // First Name
            const firstName = document.getElementById('first_name').value.trim();
            if (firstName.length < 2) {
                showError('first_name', 'First name must be at least 2 characters long');
                isValid = false;
            }
            
            // Last Name
            const lastName = document.getElementById('last_name').value.trim();
            if (lastName.length < 2) {
                showError('last_name', 'Last name must be at least 2 characters long');
                isValid = false;
            }
            
            // Email
            const email = document.getElementById('email').value.trim();
            if (!isValidEmail(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            }
            
            // Password
            const password = document.getElementById('password').value;
            if (password.length < 8) {
                showError('password', 'Password must be at least 8 characters long');
                isValid = false;
            }
            
            // Confirm Password
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                showError('confirm_password', 'Passwords do not match');
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
                case 'first_name':
                case 'last_name':
                    if (value.length < 2) {
                        showError(fieldId, `${fieldId.replace('_', ' ')} must be at least 2 characters long`);
                    }
                    break;
                    
                case 'email':
                    if (value && !isValidEmail(value)) {
                        showError(fieldId, 'Please enter a valid email address');
                    }
                    break;
                    
                case 'password':
                    if (value && value.length < 8) {
                        showError(fieldId, 'Password must be at least 8 characters long');
                    }
                    break;
                    
                case 'confirm_password':
                    const password = document.getElementById('password').value;
                    if (value && value !== password) {
                        showError(fieldId, 'Passwords do not match');
                    }
                    break;
            }
        }
    </script>
</body>
</html>