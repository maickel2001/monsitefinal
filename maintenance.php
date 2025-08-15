<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - SMM Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .maintenance-card { 
            background: white; 
            padding: 3rem; 
            border-radius: 24px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.2); 
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .maintenance-icon { 
            font-size: 4rem; 
            color: #007AFF; 
            margin-bottom: 1.5rem; 
            animation: pulse 2s infinite;
        }
        .maintenance-title { 
            font-size: 2.5rem; 
            font-weight: 700; 
            margin-bottom: 1rem; 
            background: linear-gradient(135deg, #007AFF, #5856D6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .maintenance-subtitle { 
            font-size: 1.25rem; 
            color: #6c757d; 
            margin-bottom: 2rem; 
            line-height: 1.6;
        }
        .maintenance-description { 
            color: #6c757d; 
            margin-bottom: 2rem; 
            line-height: 1.6;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            margin: 2rem 0;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #007AFF, #5856D6);
            border-radius: 4px;
            animation: progress 3s ease-in-out infinite;
        }
        .estimated-time {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            border: 1px solid #e9ecef;
        }
        .estimated-time h3 {
            color: #007AFF;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .contact-info {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            border: 1px solid #bbdefb;
        }
        .contact-info h3 {
            color: #1976d2;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .contact-info p {
            color: #1565c0;
            margin-bottom: 0.5rem;
        }
        .refresh-button {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .refresh-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 122, 255, 0.3);
        }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #d4edda;
            color: #155724;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .status-indicator i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .maintenance-card { padding: 2rem; }
            .maintenance-title { font-size: 2rem; }
            .maintenance-subtitle { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="maintenance-card">
            <div class="status-indicator">
                <i class="fas fa-cog"></i>
                Maintenance in Progress
            </div>
            
            <div class="maintenance-icon">
                <i class="fas fa-tools"></i>
            </div>
            
            <h1 class="maintenance-title">We're Upgrading!</h1>
            
            <p class="maintenance-subtitle">
                We're currently performing essential maintenance to improve your experience.
            </p>
            
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            
            <div class="estimated-time">
                <h3><i class="fas fa-clock"></i> Estimated Completion</h3>
                <p>We expect to be back online within the next 2-3 hours.</p>
            </div>
            
            <div class="contact-info">
                <h3><i class="fas fa-envelope"></i> Need Immediate Assistance?</h3>
                <p><strong>Email:</strong> support@smmplatform.com</p>
                <p><strong>WhatsApp:</strong> +225 0123456789</p>
            </div>
            
            <p class="maintenance-description">
                Thank you for your patience. We're working hard to bring you an even better SMM platform experience.
            </p>
            
            <button class="refresh-button" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Check Status
            </button>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(() => {
            location.reload();
        }, 5 * 60 * 1000);
        
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.querySelector('.progress-fill');
            const refreshButton = document.querySelector('.refresh-button');
            
            // Simulate progress updates
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 10;
                if (progress > 100) progress = 100;
                progressBar.style.width = progress + '%';
                
                if (progress >= 100) {
                    clearInterval(progressInterval);
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            }, 3000);
            
            // Button click effect
            refreshButton.addEventListener('click', function() {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
                this.disabled = true;
                
                setTimeout(() => {
                    location.reload();
                }, 1000);
            });
        });
    </script>
</body>
</html>