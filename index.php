<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Subscription.php';
require_once 'includes/brand_logo.php';

$db = new Database();
$subscription = new Subscription();

// Get active subscription plans
$plans = $subscription->getSubscriptionPlans();

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'lecturer':
            header('Location: lecturer/courses.php');
            break;
        case 'student':
            header('Location: student/dashboard.php');
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudySmart - Online Learning Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/brand-logo.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ff8c00 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 40px;
            opacity: 0.95;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(255, 140, 0, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 140, 0, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: transparent;
            border: 2px solid white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-secondary-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }

        .features-section {
            padding: 80px 0;
            background: white;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }

        .feature-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }

        .pricing-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .pricing-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 50%, #ff8c00 100%);
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 60px rgba(102, 126, 234, 0.3);
        }

        .pricing-card.featured {
            border: 3px solid #ff8c00;
            transform: scale(1.05);
        }

        .pricing-card.featured::before {
            height: 8px;
        }

        .price {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 20px 0;
        }

        .price-featured {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6b00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: #333;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #667eea;
        }

        .btn-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .install-fallback {
            max-width: 760px;
            margin: 20px auto 0;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            padding: 14px 18px;
            text-align: left;
        }

        .footer {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 50px 0 20px;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .pricing-card.featured {
                transform: scale(1);
            }
        }
    </style>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#667eea">
</head>
<body>
    <nav class="navbar navbar-expand-lg nav-custom fixed-top">
        <div class="container">
            <?php render_brand_logo(['href' => "index.php", 'class' => "navbar-brand", 'size' => "md", 'logo_path' => "WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item d-none" id="installNavItem">
                        <button type="button" id="installButtonNav" class="btn btn-nav me-lg-2">
                            <i class="fas fa-download me-2"></i>Install app
                        </button>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a href="register.php" class="btn btn-nav">Sign Up</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="hero-content text-center">
                <h1 class="hero-title">Learn Without Limits</h1>
                <p class="hero-subtitle">Access premium courses, resources, and expert tutoring at your own pace</p>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <a href="register.php" class="btn btn-primary-custom">
                        <i class="fas fa-rocket me-2"></i>Get Started
                    </a>
                    <a href="login.php" class="btn btn-secondary-custom">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <button type="button" id="installButtonHero" class="btn btn-secondary-custom d-none">
                        <i class="fas fa-download me-2"></i>Install app
                    </button>
                </div>
                <div id="installFallback" class="install-fallback d-none" role="status" aria-live="polite"></div>
            </div>
        </div>
    </section>

    <section id="features" class="features-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold mb-3">Why Choose StudySmart?</h2>
                <p class="lead text-muted">Everything you need to succeed in your studies</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-book-open"></i></div>
                        <h4 class="fw-bold mb-3">Premium Courses</h4>
                        <p class="text-muted">Access to high-quality courses taught by expert lecturers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-video"></i></div>
                        <h4 class="fw-bold mb-3">Video Resources</h4>
                        <p class="text-muted">Watch and learn from comprehensive video tutorials</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                        <h4 class="fw-bold mb-3">Live Sessions</h4>
                        <p class="text-muted">Join interactive tutoring sessions with your lecturers</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-file-alt"></i></div>
                        <h4 class="fw-bold mb-3">Study Materials</h4>
                        <p class="text-muted">Download and access comprehensive study resources</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                        <h4 class="fw-bold mb-3">Track Progress</h4>
                        <p class="text-muted">Monitor your learning progress and grades</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                        <h4 class="fw-bold mb-3">Learn Anywhere</h4>
                        <p class="text-muted">Access your courses from any device, anytime</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="pricing-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold mb-3">Choose Your Plan</h2>
                <p class="lead text-muted">Flexible subscription options to fit your learning needs</p>
            </div>
            <div class="row g-4 justify-content-center">
                <?php if (empty($plans)): ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Subscription plans coming soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($plans as $index => $plan): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="pricing-card <?php echo $index === 1 ? 'featured' : ''; ?>">
                                <h3 class="fw-bold mb-3"><?php echo ucfirst(str_replace('_', ' ', $plan['subscription_type'])); ?></h3>
                                <div class="price <?php echo $index === 1 ? 'price-featured' : ''; ?>">
                                    K<?php echo number_format($plan['price'], 2); ?>
                                </div>
                                <p class="text-muted mb-4"><?php echo $plan['period_days']; ?> Days</p>
                                <?php if ($plan['description']): ?>
                                    <p class="mb-4"><?php echo htmlspecialchars($plan['description']); ?></p>
                                <?php endif; ?>
                                <a href="register.php?plan=<?php echo $plan['id']; ?>" class="btn btn-primary-custom w-100">
                                    Subscribe Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <?php render_brand_logo(['href' => "index.php", 'class' => "mb-3 text-white", 'size' => "sm", 'logo_path' => "WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png", 'alt' => "StudySmart logo"]); ?>
                    <p>Your trusted online learning platform for quality education.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="text-white text-decoration-none">Login</a></li>
                        <li><a href="register.php" class="text-white text-decoration-none">Register</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> StudySmart. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>if ('serviceWorker' in navigator) { window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(()=>{})); }</script>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="installToast" class="toast align-items-center text-bg-success border-0" role="status" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">StudySmart installed successfully. You can now open it like an app.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <script>
        let deferredInstallPrompt = null;
        const installButtons = [document.getElementById('installButtonNav'), document.getElementById('installButtonHero')];
        const installNavItem = document.getElementById('installNavItem');
        const fallbackBox = document.getElementById('installFallback');
        const installToastElement = document.getElementById('installToast');

        const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        const setInstallVisibility = (isVisible) => {
            installButtons.forEach((button) => {
                if (!button) {
                    return;
                }

                button.classList.toggle('d-none', !isVisible);
            });

            if (installNavItem) {
                installNavItem.classList.toggle('d-none', !isVisible);
            }
        };

        const showFallbackInstructions = () => {
            if (!fallbackBox || isStandalone()) {
                return;
            }

            const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
            const message = isIOS
                ? 'To install StudySmart on iOS, open Share in Safari and select “Add to Home Screen”.'
                : 'Install is not currently available from this browser. Open your browser menu and choose “Install app” or “Add to Home screen” when available.';

            fallbackBox.textContent = message;
            fallbackBox.classList.remove('d-none');
        };

        const handleInstallClick = async () => {
            if (!deferredInstallPrompt) {
                showFallbackInstructions();
                return;
            }

            deferredInstallPrompt.prompt();
            const { outcome } = await deferredInstallPrompt.userChoice;

            if (outcome === 'accepted') {
                fallbackBox?.classList.add('d-none');
            } else {
                showFallbackInstructions();
            }

            deferredInstallPrompt = null;
            setInstallVisibility(false);
        };

        installButtons.forEach((button) => button?.addEventListener('click', handleInstallClick));

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredInstallPrompt = event;
            fallbackBox?.classList.add('d-none');
            setInstallVisibility(true);
        });

        window.addEventListener('appinstalled', () => {
            deferredInstallPrompt = null;
            setInstallVisibility(false);
            fallbackBox?.classList.add('d-none');

            if (window.bootstrap?.Toast && installToastElement) {
                const installToast = new bootstrap.Toast(installToastElement);
                installToast.show();
            }
        });

        if (isStandalone()) {
            setInstallVisibility(false);
        } else {
            window.setTimeout(() => {
                if (!deferredInstallPrompt) {
                    showFallbackInstructions();
                }
            }, 3500);
        }
    </script>
</body>
</html>
