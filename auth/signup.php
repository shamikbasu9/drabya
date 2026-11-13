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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $conn = getDBConnection();
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashedPassword);
            
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                
                // Auto login immediately after signup
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                
                $stmt->close();
                $conn->close();
                
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Failed to create account';
            }
        }
        
        if (isset($stmt) && $stmt) $stmt->close();
        if (isset($conn) && $conn) $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Drabya</title>
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
            padding: 2rem 0;
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
            <div class="col-md-6">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Drabya" height="60" class="mb-3 rounded-circle" style="object-fit: cover;">
                            <h2 class="fw-bold">Create Account</h2>
                            <p class="text-muted">Join Drabya</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                <a href="login.php" class="alert-link">Login now</a>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" autocomplete="off">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="name" required autocomplete="off">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" autocomplete="off">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required autocomplete="off">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required minlength="6" autocomplete="new-password">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the Terms & Conditions
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="login.php<?php echo !empty($redirect) && $redirect !== SITE_URL ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-decoration-none fw-bold">Login</a></p>
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
