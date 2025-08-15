<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - SMM Platform</title>
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
            box-shadow: 0 20px 60px rgba(0,0,0,0.1); 
            border: 1px solid rgba(0,0,0,0.05);
        }
        .error-icon { 
            font-size: 6rem; 
            color: #007AFF; 
            margin-bottom: 1.5rem; 
            animation: bounce 2s infinite;
        }
        .error-code { 
            font-size: 4rem; 
            font-weight: 700; 
            color: #007AFF; 
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
        .search-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            border: 1px solid #e9ecef;
        }
        .search-box h3 {
            color: #007AFF;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .search-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            outline: none;
            border-color: #007AFF;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
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
            background: linear-gradient(135deg, #007AFF, #5856D6); 
            color: white; 
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 122, 255, 0.3);
        }
        .btn-secondary { 
            background: transparent; 
            color: #007AFF; 
            border: 2px solid #007AFF; 
        }
        .btn-secondary:hover {
            background: #007AFF;
            color: white;
        }
        .suggestions {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 2rem 0;
            border: 1px solid #e9ecef;
        }
        .suggestions h3 {
            color: #007AFF;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .suggestion-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        .suggestion-link {
            background: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            color: #007AFF;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 0.875rem;
        }
        .suggestion-link:hover {
            background: #007AFF;
            color: white;
            transform: translateY(-1px);
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        @media (max-width: 768px) {
            .error-card { padding: 2rem; }
            .error-code { font-size: 3rem; }
            .error-title { font-size: 1.5rem; }
            .suggestion-links { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>
            
            <div class="error-code">404</div>
            
            <h1 class="error-title">Page Not Found</h1>
            
            <p class="error-description">
                Oops! The page you're looking for doesn't exist. It might have been moved, deleted, or you entered the wrong URL.
            </p>
            
            <div class="search-box">
                <h3><i class="fas fa-search"></i> Search Our Site</h3>
                <input type="text" class="search-input" placeholder="What are you looking for?" id="searchInput">
                <button class="btn btn-primary" onclick="performSearch()">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            
            <div class="suggestions">
                <h3><i class="fas fa-lightbulb"></i> Popular Pages</h3>
                <div class="suggestion-links">
                    <a href="index.php" class="suggestion-link">Home</a>
                    <a href="services.php" class="suggestion-link">Services</a>
                    <a href="pricing.php" class="suggestion-link">Pricing</a>
                    <a href="about.php" class="suggestion-link">About</a>
                    <a href="contact.php" class="suggestion-link">Contact</a>
                </div>
            </div>
            
            <div style="margin-top: 2rem;">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go Home
                </a>
                <button class="btn btn-secondary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Go Back
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Search functionality
        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            if (searchTerm) {
                // Redirect to search results page or implement search logic
                alert('Search functionality will be implemented here. You searched for: ' + searchTerm);
            }
        }
        
        // Enter key support for search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        // Auto-focus search input
        document.getElementById('searchInput').focus();
        
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            const errorIcon = document.querySelector('.error-icon');
            const errorCard = document.querySelector('.error-card');
            
            // Add hover effect to error card
            errorCard.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 25px 70px rgba(0,0,0,0.15)';
            });
            
            errorCard.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 20px 60px rgba(0,0,0,0.1)';
            });
            
            // Add click effect to error icon
            errorIcon.addEventListener('click', function() {
                this.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 200);
            });
        });
    </script>
</body>
</html>