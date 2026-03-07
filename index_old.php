<?php
require_once 'config/database.php';
require_once 'classes/Auth.php';

$auth = new Auth();

// If user is already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    switch ($user['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'lecturer':
            header('Location: lecturer/dashboard.php');
            break;
        case 'student':
            header('Location: student/dashboard.php');
            break;
        default:
            header('Location: login.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Revolutionary Online Tutoring Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="assets/css/unified-style.css" rel="stylesheet">
    <style>
        /* 3D ANIMATED LANDING PAGE STYLES */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --logo-gradient: linear-gradient(135deg, #FF8C00 0%, #FF4500 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-3d: 0 20px 40px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 30px 60px rgba(0, 0, 0, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            overflow-x: hidden;
            perspective: 1000px;
        }

        /* 3D Navigation */
        .navbar-3d {
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid var(--logo-orange);
            transform-style: preserve-3d;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-3d.scrolled {
            background: rgba(26, 26, 26, 0.98);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transform: translateZ(20px);
        }

        .logo-container-3d {
            display: flex;
            align-items: center;
            gap: 1rem;
            transform-style: preserve-3d;
            transition: transform 0.3s ease;
        }

        .logo-container-3d:hover {
            transform: rotateY(15deg) translateZ(10px);
        }

        .logo-image-3d {
            width: 50px;
            height: 50px;
            object-fit: contain;
            filter: drop-shadow(0 5px 15px rgba(255, 140, 0, 0.5));
            animation: logoFloat 3s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px) rotateY(0deg); }
            50% { transform: translateY(-10px) rotateY(5deg); }
        }

        .logo-text-3d {
            font-weight: 900;
            font-size: 2rem;
            background: var(--logo-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 5px 15px rgba(255, 140, 0, 0.3);
        }

        .nav-link-3d {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 600;
            position: relative;
            transform-style: preserve-3d;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link-3d::before {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--logo-gradient);
            transform: translateX(-50%);
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        .nav-link-3d:hover::before {
            width: 100%;
        }

        .nav-link-3d:hover {
            color: var(--logo-orange) !important;
            transform: translateY(-2px) translateZ(10px);
        }

        /* 3D Hero Section */
        .hero-3d {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            transform-style: preserve-3d;
            overflow: hidden;
        }

        .hero-bg-3d {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            z-index: -2;
        }

        .hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .hero-content-3d {
            position: relative;
            z-index: 2;
            transform-style: preserve-3d;
        }

        .hero-title-3d {
            font-size: 4rem;
            font-weight: 900;
            color: white;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transform: translateZ(50px);
            animation: titleFloat 4s ease-in-out infinite;
            margin-bottom: 2rem;
        }

        @keyframes titleFloat {
            0%, 100% { transform: translateZ(50px) translateY(0px); }
            50% { transform: translateZ(50px) translateY(-20px); }
        }

        .hero-subtitle-3d {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            transform: translateZ(30px);
            animation: subtitleFloat 4s ease-in-out infinite 0.5s;
        }

        @keyframes subtitleFloat {
            0%, 100% { transform: translateZ(30px) translateY(0px); }
            50% { transform: translateZ(30px) translateY(-15px); }
        }

        .hero-buttons-3d {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            transform: translateZ(40px);
        }

        .btn-3d {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transform-style: preserve-3d;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-3d:hover::before {
            left: 100%;
        }

        .btn-primary-3d {
            background: var(--logo-gradient);
            color: white;
            box-shadow: 0 10px 30px rgba(255, 140, 0, 0.4);
        }

        .btn-primary-3d:hover {
            transform: translateY(-5px) translateZ(20px) scale(1.05);
            box-shadow: 0 20px 40px rgba(255, 140, 0, 0.6);
            color: white;
        }

        .btn-outline-3d {
            background: transparent;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-outline-3d:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-5px) translateZ(20px) scale(1.05);
            color: white;
        }

        /* 3D Features Section */
        .features-3d {
            padding: 8rem 0;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            position: relative;
            transform-style: preserve-3d;
        }

        .section-title-3d {
            text-align: center;
            font-size: 3rem;
            font-weight: 800;
            color: white;
            margin-bottom: 5rem;
            transform: translateZ(30px);
        }

        .feature-card-3d {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 25px;
            padding: 3rem 2rem;
            text-align: center;
            transform-style: preserve-3d;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card-3d::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 140, 0, 0.1), rgba(255, 69, 0, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-card-3d:hover::before {
            opacity: 1;
        }

        .feature-card-3d:hover {
            transform: translateY(-20px) translateZ(30px) rotateY(5deg);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
        }

        .feature-icon-3d {
            font-size: 4rem;
            color: var(--logo-orange);
            margin-bottom: 2rem;
            display: block;
            transform: translateZ(20px);
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateZ(20px) translateY(0px); }
            50% { transform: translateZ(20px) translateY(-10px); }
        }

        .feature-title-3d {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            transform: translateZ(15px);
        }

        .feature-desc-3d {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            transform: translateZ(10px);
        }

        /* 3D Stats Section */
        .stats-3d {
            padding: 6rem 0;
            background: var(--secondary-gradient);
            position: relative;
            transform-style: preserve-3d;
        }

        .stat-item-3d {
            text-align: center;
            transform: translateZ(20px);
            animation: statFloat 4s ease-in-out infinite;
        }

        @keyframes statFloat {
            0%, 100% { transform: translateZ(20px) translateY(0px); }
            50% { transform: translateZ(20px) translateY(-15px); }
        }

        .stat-number-3d {
            font-size: 3.5rem;
            font-weight: 900;
            color: white;
            text-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
        }

        .stat-label-3d {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
        }

        /* 3D CTA Section */
        .cta-3d {
            padding: 8rem 0;
            background: var(--accent-gradient);
            position: relative;
            transform-style: preserve-3d;
            overflow: hidden;
        }

        .cta-content-3d {
            text-align: center;
            transform: translateZ(40px);
        }

        .cta-title-3d {
            font-size: 3.5rem;
            font-weight: 900;
            color: white;
            margin-bottom: 2rem;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .cta-desc-3d {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            pointer-events: none;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(2) {
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title-3d {
                font-size: 2.5rem;
            }
            
            .hero-subtitle-3d {
                font-size: 1.2rem;
            }
            
            .hero-buttons-3d {
                flex-direction: column;
                align-items: center;
            }
            
            .section-title-3d {
                font-size: 2rem;
            }
            
            .feature-card-3d {
                margin-bottom: 2rem;
            }
        }

        /* Scroll Animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease;
        }

        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--logo-gradient);
            border-radius: 6px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--logo-orange-dark);
        }
    </style>
</head>
<body>
    <!-- 3D Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-3d fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="logo-container-3d">
                    <img src="WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png" alt="StudySmart Logo" class="logo-image-3d">
                    <span class="logo-text-3d"><?php echo APP_NAME; ?></span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-3d" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-3d" href="#stats">Stats</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-3d" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-3d" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-3d ms-2 px-3" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary-3d ms-2 px-3" href="register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 3D Hero Section -->
    <section class="hero-3d" id="hero">
        <div class="hero-bg-3d"></div>
        <div class="hero-particles" id="particles"></div>
        
        <!-- Floating Elements -->
        <div class="floating-element" style="top: 20%; left: 10%; font-size: 3rem; color: rgba(255, 140, 0, 0.3);">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="floating-element" style="top: 60%; right: 15%; font-size: 2.5rem; color: rgba(255, 69, 0, 0.3);">
            <i class="fas fa-book"></i>
        </div>
        <div class="floating-element" style="top: 80%; left: 20%; font-size: 2rem; color: rgba(255, 140, 0, 0.3);">
            <i class="fas fa-laptop-code"></i>
        </div>
        
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content-3d">
                        <h1 class="hero-title-3d">
                            Revolutionize Your <span style="background: var(--logo-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Learning</span>
                        </h1>
                        <p class="hero-subtitle-3d">
                            Experience the future of education with our cutting-edge online tutoring platform. 
                            Connect with expert lecturers, access immersive learning materials, and transform your academic journey.
                        </p>
                        <div class="hero-buttons-3d">
                            <a href="register.php" class="btn btn-primary-3d">
                                <i class="fas fa-rocket"></i>
                                Start Learning Now
                            </a>
                            <a href="#features" class="btn btn-outline-3d">
                                <i class="fas fa-play"></i>
                                Watch Demo
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-visual-3d" style="transform: translateZ(60px);">
                        <div style="font-size: 15rem; color: var(--logo-orange); opacity: 0.8; filter: drop-shadow(0 20px 40px rgba(255, 140, 0, 0.4));">
                            <i class="fas fa-brain" style="animation: brainPulse 2s ease-in-out infinite;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3D Features Section -->
    <section class="features-3d" id="features">
        <div class="container">
            <h2 class="section-title-3d fade-in-up">Why Choose StudySmart?</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-3d fade-in-up">
                        <i class="fas fa-users feature-icon-3d"></i>
                        <h3 class="feature-title-3d">Expert Lecturers</h3>
                        <p class="feature-desc-3d">Learn from industry professionals and experienced educators who are passionate about your success.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-3d fade-in-up">
                        <i class="fas fa-video feature-icon-3d"></i>
                        <h3 class="feature-title-3d">Interactive Sessions</h3>
                        <p class="feature-desc-3d">Engage in real-time video sessions with interactive whiteboards and collaborative tools.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-3d fade-in-up">
                        <i class="fas fa-chart-line feature-icon-3d"></i>
                        <h3 class="feature-title-3d">Progress Tracking</h3>
                        <p class="feature-desc-3d">Monitor your learning progress with detailed analytics and personalized insights.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-3d fade-in-up">
                        <i class="fas fa-mobile-alt feature-icon-3d"></i>
                        <h3 class="feature-title-3d">Mobile First</h3>
                        <p class="feature-desc-3d">Access your courses anywhere, anytime with our responsive mobile-optimized platform.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-3d fade-in-up">
                        <i class="fas fa-shield-alt feature-icon-3d"></i>
                        <h3 class="feature-title-3d">Secure Learning</h3>
                        <p class="feature-desc-3d">Your data and learning materials are protected with enterprise-grade security.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="feature-card-3d fade-in-up">
                        <i class="fas fa-clock feature-icon-3d"></i>
                        <h3 class="feature-title-3d">24/7 Access</h3>
                        <p class="feature-desc-3d">Learn at your own pace with round-the-clock access to resources and materials.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3D Stats Section -->
    <section class="stats-3d" id="stats">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item-3d">
                        <div class="stat-number-3d" data-count="1000">0</div>
                        <div class="stat-label-3d">Happy Students</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item-3d">
                        <div class="stat-number-3d" data-count="50">0</div>
                        <div class="stat-label-3d">Expert Lecturers</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item-3d">
                        <div class="stat-number-3d" data-count="100">0</div>
                        <div class="stat-label-3d">Courses Available</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-item-3d">
                        <div class="stat-number-3d" data-count="95">0</div>
                        <div class="stat-label-3d">Success Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3D CTA Section -->
    <section class="cta-3d" id="cta">
        <div class="container">
            <div class="cta-content-3d">
                <h2 class="cta-title-3d">Ready to Transform Your Learning?</h2>
                <p class="cta-desc-3d">
                    Join thousands of students who have already revolutionized their education with StudySmart. 
                    Start your journey today and unlock your full potential.
                </p>
                <div class="hero-buttons-3d">
                    <a href="register.php" class="btn btn-primary-3d">
                        <i class="fas fa-user-plus"></i>
                        Create Free Account
                    </a>
                    <a href="login.php" class="btn btn-outline-3d">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="logo-container-3d">
                        <img src="WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png" alt="StudySmart Logo" class="logo-image-3d" style="width: 40px; height: 40px;">
                        <span class="logo-text-3d"><?php echo APP_NAME; ?></span>
                    </div>
                    <p class="mt-3">Revolutionizing education through technology and innovation.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Platform</h5>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-white-50 text-decoration-none">Features</a></li>
                        <li><a href="#stats" class="text-white-50 text-decoration-none">Statistics</a></li>
                        <li><a href="login.php" class="text-white-50 text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#contact" class="text-white-50 text-decoration-none">Contact</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-white-50 text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Stay Connected</h5>
                    <p>Get the latest updates and learning tips.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Enter your email">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2025 StudySmart. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/unified-script.js"></script>
    <script>
        // 3D ANIMATION SCRIPT
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar-3d');
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Particle system
            initParticles();
            
            // Scroll animations
            initScrollAnimations();
            
            // Counter animations
            initCounters();
            
            // 3D mouse movement effects
            init3DEffects();
        });

        // Advanced Particle System
        function initParticles() {
            const particlesContainer = document.getElementById('particles');
            if (!particlesContainer) return;
            
            const particleCount = 100;
            
            for (let i = 0; i < particleCount; i++) {
                createParticle(particlesContainer);
            }
        }

        function createParticle(container) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 2}px;
                height: ${Math.random() * 4 + 2}px;
                background: rgba(255, 140, 0, ${Math.random() * 0.5 + 0.3});
                border-radius: 50%;
                pointer-events: none;
                animation: particleFloat ${Math.random() * 10 + 10}s linear infinite;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
            `;
            
            container.appendChild(particle);
        }

        // Scroll animations
        function initScrollAnimations() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.fade-in-up').forEach(el => {
                observer.observe(el);
            });
        }

        // Counter animations
        function initCounters() {
            const counters = document.querySelectorAll('.stat-number-3d');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-count'));
                const increment = target / 100;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.ceil(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                // Start counter when element is visible
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(counter);
            });
        }

        // 3D mouse movement effects
        function init3DEffects() {
            document.addEventListener('mousemove', function(e) {
                const cards = document.querySelectorAll('.feature-card-3d');
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                
                cards.forEach((card, index) => {
                    const rect = card.getBoundingClientRect();
                    const cardX = rect.left + rect.width / 2;
                    const cardY = rect.top + rect.height / 2;
                    
                    const deltaX = (e.clientX - cardX) / window.innerWidth;
                    const deltaY = (e.clientY - cardY) / window.innerHeight;
                    
                    card.style.transform = `
                        translateY(-20px) 
                        translateZ(30px) 
                        rotateY(${deltaX * 10}deg) 
                        rotateX(${-deltaY * 10}deg)
                    `;
                });
            });
        }

        // Smooth scrolling for anchor links
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

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes particleFloat {
                0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
                10% { opacity: 1; }
                90% { opacity: 1; }
                100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
            }
            
            @keyframes brainPulse {
                0%, 100% { transform: scale(1) rotate(0deg); }
                50% { transform: scale(1.1) rotate(5deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html> 