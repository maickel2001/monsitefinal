<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Reviews.php';

$user = new User();
$reviews = new Reviews();

$isLoggedIn = $user->isLoggedIn();
$approvedReviews = $reviews->getApprovedReviews(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Premium Social Media Marketing Platform</title>
    <meta name="description" content="Boost your social media presence with our premium SMM services. Get real likes, followers, views, and engagement for all major platforms.">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            background-color: #ffffff;
            overflow-x: hidden;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .header.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #007AFF;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #1d1d1f;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover {
            color: #007AFF;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 122, 255, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 122, 255, 0.4);
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
        
        /* Hero Section */
        .hero {
            padding: 120px 0 80px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(0,122,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #1d1d1f, #007AFF);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }
        
        .hero p {
            font-size: 1.25rem;
            color: #6c757d;
            margin-bottom: 2.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Features Section */
        .features {
            padding: 80px 0;
            background: white;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            color: #1d1d1f;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #007AFF, #5856D6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1d1d1f;
        }
        
        .feature-card p {
            color: #6c757d;
            line-height: 1.6;
        }
        
        /* Reviews Section */
        .reviews {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .review-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .review-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .review-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #007AFF, #5856D6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            margin-right: 1rem;
        }
        
        .review-info h4 {
            font-weight: 600;
            color: #1d1d1f;
        }
        
        .review-rating {
            color: #FFD700;
            margin-top: 0.25rem;
        }
        
        .review-content {
            color: #6c757d;
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta {
            padding: 80px 0;
            background: linear-gradient(135deg, #007AFF, #5856D6);
            color: white;
            text-align: center;
        }
        
        .cta h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .cta p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta .btn {
            background: white;
            color: #007AFF;
            font-size: 1.1rem;
            padding: 1rem 2rem;
        }
        
        .cta .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        /* Footer */
        .footer {
            background: #1d1d1f;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #007AFF;
        }
        
        .footer-section p,
        .footer-section a {
            color: #a1a1a6;
            text-decoration: none;
            line-height: 1.6;
        }
        
        .footer-section a:hover {
            color: #007AFF;
        }
        
        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 2rem;
            text-align: center;
            color: #a1a1a6;
        }
        
        /* Mobile menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #1d1d1f;
            cursor: pointer;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
            
            .hero {
                padding: 100px 0 60px;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .features-grid,
            .reviews-grid {
                grid-template-columns: 1fr;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .container {
                padding: 0 15px;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Smooth transitions */
        .feature-card,
        .review-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Loading states */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        /* Success/Error messages */
        .message {
            padding: 1rem;
            border-radius: 12px;
            margin: 1rem 0;
            font-weight: 500;
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <nav class="nav">
                <a href="#" class="logo">
                    <i class="fas fa-rocket"></i> <?php echo APP_NAME; ?>
                </a>
                
                <div class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#reviews">Reviews</a>
                    <a href="#contact">Contact</a>
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary">Login</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
                
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content fade-in-up">
                <h1>Boost Your Social Media Presence</h1>
                <p>Get real engagement, followers, and growth for all major social media platforms. Premium SMM services with instant delivery and 24/7 support.</p>
                <div class="hero-buttons">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">Get Started Free</a>
                        <a href="#features" class="btn btn-secondary">Learn More</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Why Choose Our Platform?</h2>
            <div class="features-grid">
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Reliable</h3>
                    <p>Bank-grade security with encrypted transactions and secure user data protection.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Instant Delivery</h3>
                    <p>Get your orders delivered instantly with our automated system and real-time tracking.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer support with live chat, tickets, and dedicated account managers.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Real Analytics</h3>
                    <p>Track your growth with detailed analytics, reports, and performance insights.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3>Global Reach</h3>
                    <p>Connect with audiences worldwide across all major social media platforms.</p>
                </div>
                
                <div class="feature-card fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Premium Quality</h3>
                    <p>High-quality, genuine engagement that boosts your credibility and reach.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews" id="reviews">
        <div class="container">
            <h2 class="section-title">What Our Clients Say</h2>
            <div class="reviews-grid">
                <?php if (!empty($approvedReviews)): ?>
                    <?php foreach ($approvedReviews as $review): ?>
                        <div class="review-card fade-in-up">
                            <div class="review-header">
                                <div class="review-avatar">
                                    <?php echo strtoupper(substr($review['name'], 0, 1)); ?>
                                </div>
                                <div class="review-info">
                                    <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                    <div class="review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="review-content">
                                <h5><?php echo htmlspecialchars($review['title']); ?></h5>
                                <p><?php echo htmlspecialchars($review['content']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="review-card fade-in-up">
                        <div class="review-header">
                            <div class="review-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="review-info">
                                <h4>Be the First!</h4>
                                <div class="review-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <div class="review-content">
                            <h5>Share Your Experience</h5>
                            <p>Join thousands of satisfied customers and share your success story with us!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <a href="submit-review.php" class="btn btn-primary">Submit Your Review</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Boost Your Social Media?</h2>
            <p>Join thousands of satisfied customers and start growing your online presence today.</p>
            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn">Get Started Now</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo APP_NAME; ?></h3>
                    <p>Premium social media marketing platform providing real engagement and growth for all major platforms.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="#features">Features</a></p>
                    <p><a href="#reviews">Reviews</a></p>
                    <p><a href="login.php">Login</a></p>
                    <p><a href="register.php">Register</a></p>
                </div>
                
                <div class="footer-section">
                    <h3>Support</h3>
                    <p><a href="support.php">Help Center</a></p>
                    <p><a href="contact.php">Contact Us</a></p>
                    <p><a href="terms.php">Terms of Service</a></p>
                    <p><a href="privacy.php">Privacy Policy</a></p>
                </div>
                
                <div class="footer-section">
                    <h3>Connect</h3>
                    <p><a href="#"><i class="fab fa-twitter"></i> Twitter</a></p>
                    <p><a href="#"><i class="fab fa-facebook"></i> Facebook</a></p>
                    <p><a href="#"><i class="fab fa-instagram"></i> Instagram</a></p>
                    <p><a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a></p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>