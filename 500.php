<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - SMM Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
            color: #1d1d1f;
        }
        .container { 
            max-width: 600px; 
            width: 100%; 
            text-align: center; 
        }
        .error-card { 
            background: white; 
            padding: 3rem; 
            border-radius: 24px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.2); 
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .error-icon { 
            font-size: 6rem; 
            color: #ff6b6b; 
            margin-bottom: 1.5rem; 
            animation: shake 2s infinite;
        }
        .error-code { 
            font-size: 4rem; 
            font-weight: 700; 
            color: #ff6b6b; 
            margin-bottom: 1rem; 
        }
        .error-title { 
            font-size: 2rem; 
            font-weight: 600; 
            margin-bottom: 1rem; 
            color: #1d1d1f;
        }
        .error-description { 
            font-size: 1.1rem; 
            color: #6c757d; 
            margin-bottom: 2rem; 
            line-height: 1.6;
        }
        .status-info {
            background: #fff5f5;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            border: 1px solid #fed7d7;
        }
        .status-info h3 {
            color: #e53e3e;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .status-info p {
            color: #c53030;
            margin-bottom: 0.5rem;
        }
        .btn { 
            padding: 1rem 2rem; 
            border-radius: 12px; 
            text-decoration: none; 
            font-weight: 600; 
            border: none; 
            cursor: pointer; 
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem;
        }
        .btn-primary { 
            background: linear-gradient(135deg, #ff6b6b, #ee5a24); 
            color: white; 
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
        }
        .btn-secondary { 
            background: transparent; 
            color: #ff6b6b; 
            border: 2px solid #ff6b6b; 
        }
        .btn-secondary:hover {
            background: #ff6b6b;
            color: white;
        }
        .technical-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            border: 1px solid #e9ecef;
            text-align: left;
        }
        .technical-details h3 {
            color: #ff6b6b;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .technical-details pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            overflow-x: auto;
            margin-top: 1rem;
        }
        .contact-support {
            background: #e3f2fd;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            border: 1px solid #bbdefb;
        }
        .contact-support h3 {
            color: #1976d2;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .contact-support p {
            color: #1565c0;
            margin-bottom: 0.5rem;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        @media (max-width: 768px) {
            .error-card { padding: 2rem; }
            .error-code { font-size: 3rem; }
            .error-title { font-size: 1.5rem; }
            .technical-details { font-size: 0.875rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <div class="error-code">500</div>
            
            <h1 class="error-title">Internal Server Error</h1>
            
            <p class="error-description">
                Oops! Something went wrong on our end. We're experiencing technical difficulties and our team has been notified.
            </p>
            
            <div class="status-info">
                <h3><i class="fas fa-info-circle"></i> What Happened?</h3>
                <p>• Our servers encountered an unexpected error</p>
                <p>• The issue has been logged and our team is investigating</p>
                <p>• This is not related to your device or internet connection</p>
            </div>
            
            <div class="contact-support">
                <h3><i class="fas fa-headset"></i> Need Immediate Help?</h3>
                <p><strong>Email:</strong> support@smmplatform.com</p>
                <p><strong>WhatsApp:</strong> +225 0123456789</p>
                <p><strong>Response Time:</strong> Usually within 1-2 hours</p>
            </div>
            
            <div class="technical-details">
                <h3><i class="fas fa-bug"></i> Technical Details</h3>
                <p>Error ID: <?php echo uniqid('ERR_'); ?></p>
                <p>Timestamp: <?php echo date('Y-m-d H:i:s'); ?></p>
                <p>Server: <?php echo $_SERVER['SERVER_NAME'] ?? 'Unknown'; ?></p>
                <details>
                    <summary style="cursor: pointer; color: #ff6b6b; margin-top: 1rem;">
                        <i class="fas fa-code"></i> Show Error Details
                    </summary>
                    <pre style="margin-top: 1rem;">
Error Type: Internal Server Error
Status Code: 500
Request URI: <?php echo $_SERVER['REQUEST_URI'] ?? 'Unknown'; ?>
User Agent: <?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; ?>
                    </pre>
                </details>
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <button class="btn btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Try Again
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            const errorIcon = document.querySelector('.error-icon');
            const errorCard = document.querySelector('.error-card');
            const technicalDetails = document.querySelector('.technical-details details');
            
            // Add hover effect to error card
            errorCard.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 25px 70px rgba(0,0,0,0.25)';
            });
            
            errorCard.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 20px 60px rgba(0,0,0,0.2)';
            });
            
            // Add click effect to error icon
            errorIcon.addEventListener('click', function() {
                this.style.transform = 'scale(1.1) rotate(5deg)';
                setTimeout(() => {
                    this.style.transform = 'scale(1) rotate(0deg)';
                }, 300);
            });
            
            // Auto-expand technical details on mobile
            if (window.innerWidth <= 768) {
                technicalDetails.setAttribute('open', '');
            }
            
            // Add retry functionality with exponential backoff
            let retryCount = 0;
            const maxRetries = 3;
            
            function retryWithBackoff() {
                if (retryCount < maxRetries) {
                    retryCount++;
                    const delay = Math.pow(2, retryCount) * 1000; // Exponential backoff
                    
                    setTimeout(() => {
                        location.reload();
                    }, delay);
                }
            }
            
            // Auto-retry after 30 seconds
            setTimeout(retryWithBackoff, 30000);
        });
        
        // Add error reporting functionality
        function reportError() {
            const errorId = document.querySelector('.technical-details p').textContent.split(': ')[1];
            const errorDetails = {
                errorId: errorId,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent,
                url: window.location.href
            };
            
            // In a real implementation, this would send to your error tracking service
            console.log('Error Report:', errorDetails);
            alert('Error has been reported to our team. Thank you for your patience.');
        }
    </script>
</body>
</html>