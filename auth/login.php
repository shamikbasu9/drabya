<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

$redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) 
    ? $_POST['redirect'] 
    : (isset($_GET['redirect']) && !empty($_GET['redirect']) ? $_GET['redirect'] : SITE_URL);

if (isUserLoggedIn()) {
    header('Location: ' . $redirect);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, name, email, password, is_blocked FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_blocked']) {
                $error = 'Your account has been blocked. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Drabya</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #E8431C;
            --primary-light: #FF6B47;
            --primary-dark: #C73515;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-blur: 15px;
            --neon-glow: 0 0 15px rgba(232, 67, 28, 0.4);
            --card-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: var(--neon-glow);
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }
        .text-primary {
            color: var(--primary) !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Drabya" height="60" class="mb-3 rounded-circle" style="object-fit: cover;">
                            <h2 class="fw-bold">Welcome Back</h2>
                            <p class="text-muted">Login to your account</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" autocomplete="off">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" required autocomplete="off">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required autocomplete="new-password">
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? <a href="signup.php<?php echo !empty($redirect) && $redirect !== SITE_URL ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-decoration-none fw-bold">Sign Up</a></p>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="text-center">
                            <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                                <i class="fas fa-home"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
