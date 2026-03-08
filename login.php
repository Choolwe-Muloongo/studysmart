<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Auth.php';

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
            header('Location: student/courses.php');
        break;
        default:
            header('Location: index.php');
    }
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        try {
            $auth = new Auth();
            $result = $auth->login($username, $password, $remember_me);
            
            if ($result['success']) {
                $user = $result['user'];
                $role = $user['role'];
                
                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header('Location: admin/dashboard.php');
                        break;
                    case 'lecturer':
                        header('Location: lecturer/courses.php');
                        break;
                    case 'student':
                        header('Location: student/courses.php');
                        break;
                    default:
                        header('Location: index.php');
                }
                exit();
            } else {
                $error_message = $result['message'];
            }
        } catch (Exception $e) {
            $error_message = 'An error occurred during login. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudySmart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #000000 0%, #FF8C00 50%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            border: 3px solid #FF8C00;
            transition: all 0.2s ease; /* Reduced from 0.3s */
        }

        .login-container:hover {
            transform: translateY(-5px); /* Reduced from -10px */
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        }

        .login-header {
            background: linear-gradient(135deg, #FF8C00 0%, #FF4500 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .logo-image {
            width: 50px;
            height: 50px;
            object-fit: contain;
            filter: brightness(1.2) contrast(1.2);
        }

        .logo-text {
            font-weight: 800;
            font-size: 2rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .login-header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #FF8C00;
            font-size: 1.1rem;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.2s ease; /* Reduced from 0.3s */
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #FF8C00;
            box-shadow: 0 0 0 0.3rem rgba(255, 140, 0, 0.25);
            background: white;
            transform: translateY(-1px); /* Reduced from -2px */
        }

        .form-check-input:checked {
            background-color: #FF8C00;
            border-color: #FF8C00;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(255, 140, 0, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #FF8C00 0%, #FF4500 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: all 0.2s ease; /* Reduced from 0.3s */
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px); /* Reduced from -3px */
            box-shadow: 0 10px 25px rgba(255, 140, 0, 0.4);
            color: white;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: #FF8C00;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease; /* Reduced from 0.3s */
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-link a:hover {
            color: #FF4500;
            transform: translateX(-3px); /* Reduced from -5px */
        }

        .demo-credentials {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            border-left: 5px solid #FF8C00;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .demo-credentials h6 {
            color: #FF8C00;
            margin-bottom: 20px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .demo-credentials h6 i {
            font-size: 1.2rem;
        }

        .credential-item {
            margin-bottom: 12px;
            font-size: 0.95em;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 140, 0, 0.2);
        }

        .credential-item:last-child {
            border-bottom: none;
        }

        .credential-item strong {
            color: #333;
            font-weight: 600;
        }

        .credential-item small {
            color: #666;
            font-style: italic;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
            
            .logo-image {
                width: 40px;
                height: 40px;
            }
        }
    </style>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#667eea">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <img src="WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png" alt="StudySmart Logo" class="logo-image">
                <span class="logo-text">StudySmart</span>
            </div>
            <p>Welcome back! Please login to your account.</p>
        </div>
        
        <div class="login-body">
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>Username or Email
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                           placeholder="Enter your username or email" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">
                        Remember me for 30 days
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            
            <div class="back-link">
                <a href="index.php">
                    <i class="fas fa-arrow-left"></i>Back to Home
                </a>
            </div>
            
            <div class="demo-credentials">
                <h6><i class="fas fa-info-circle"></i>Demo Credentials</h6>
                <div class="credential-item">
                    <strong>Admin:</strong> admin / password
                </div>
                <div class="credential-item">
                    <strong>Lecturer:</strong> lecturer1 / password
                </div>
                <div class="credential-item">
                    <strong>Student:</strong> student1 / password
                </div>
                <small class="text-muted">All demo accounts use "password" as the password</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>if ('serviceWorker' in navigator) { window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(()=>{})); }</script>
</body>
</html>
