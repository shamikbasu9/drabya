<?php
// Start output buffering at the very beginning
if (!headers_sent()) {
    ob_start();
}

// Include configuration and functions
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

// Process any session or user-related logic
$currentUser = getCurrentUser();
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// If we need to redirect, do it before any output
if (strpos($_SERVER['PHP_SELF'], 'admin/') !== false && !isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-mdb-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Drabya'; ?> - Drabya</title>
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:title" content="<?php echo isset($product['title']) ? htmlspecialchars($product['title']) : 'Drabya - Premium Merchandise'; ?>">
    <meta property="og:description" content="<?php 
        if (isset($product['description'])) {
            // Clean up the description for meta tags
            $desc = strip_tags(fullyDecode($product['description']));
            $desc = str_replace(["\r", "\n"], ' ', $desc);
            echo htmlspecialchars(substr($desc, 0, 200) . (strlen($desc) > 200 ? '...' : ''));
        } else {
            echo 'Gifts That Tell Your Story. Make it Personal. Make it Drabya. Custom coffee mugs, coasters, fridge magnets, and merchandise designed to make every moment personal.';
        }
    ?>">
    <meta property="og:site_name" content="Drabya">
    
    <?php if (isset($product) && !empty($images[0]['image_path'])): ?>
        <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/uploads/products/' . $images[0]['image_path']; ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
    <?php else: ?>
        <meta property="og:image" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?>/assets/images/logo.jpg">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:title" content="<?php echo isset($product['title']) ? htmlspecialchars($product['title']) : 'Drabya - Premium Merchandise'; ?>">
    <meta name="twitter:description" content="<?php 
        if (isset($product['description'])) {
            $desc = strip_tags(fullyDecode($product['description']));
            $desc = str_replace(["\r", "\n"], ' ', $desc);
            echo htmlspecialchars(substr($desc, 0, 200) . (strlen($desc) > 200 ? '...' : ''));
        } else {
            echo 'Gifts That Tell Your Story. Make it Personal. Make it Drabya. Custom coffee mugs, coasters, fridge magnets, and merchandise designed to make every moment personal.';
        }
    ?>">
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- MDBootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #E8431C;
            --primary-rgb: 232, 67, 28;
            --primary-dark: #C73515;
            --primary-light: #FF6B47;
            --secondary: #00F2FF;
            --accent: #FFD700;
            
            /* Glassmorphism Tokens */
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.3);
            --glass-blur: 15px;
            
            /* Shadows & Glows */
            --neon-glow: 0 0 15px rgba(var(--primary-rgb), 0.4);
            --neon-glow-strong: 0 0 25px rgba(var(--primary-rgb), 0.6);
            --card-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }
        
        [data-mdb-theme="light"] {
            --body-bg: #F8F9FA;
            --card-bg: #FFFFFF;
            --text-main: #1A1A1B;
            --text-muted: #6C757D;
        }
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-main);
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--body-bg);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
        
        /* Floating Glass Navbar */
        .desktop-nav {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1050;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        .desktop-nav.scrolled {
            padding: 0.6rem 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .desktop-nav .navbar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        
        .desktop-nav .nav-link {
            color: var(--text-main) !important;
            font-weight: 500;
            margin: 0 0.8rem;
            position: relative;
            padding: 0.5rem 0;
            transition: color 0.3s;
        }
        
        .desktop-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            transform: translateX(-50%);
            border-radius: 2px;
            box-shadow: var(--neon-glow);
        }
        
        .desktop-nav .nav-link:hover::after,
        .desktop-nav .nav-link.active::after {
            width: 100%;
        }

        .desktop-nav .nav-link:hover {
            color: var(--primary) !important;
        }
        
        /* Mobile AppBar - Futuristic Glass */
        .mobile-appbar {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-main);
            padding: 1.2rem;
            display: none;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .mobile-appbar h1 {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Mobile Bottom Nav - Floating Dock style */
        .mobile-bottom-nav {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            -webkit-backdrop-filter: blur(var(--glass-blur));
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            padding: 5px 10px;
        }
        
        .mobile-bottom-nav .nav-link {
            color: var(--text-muted);
            padding: 0.8rem 0;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        
        .mobile-bottom-nav .nav-link.active {
            color: var(--primary);
            background: rgba(var(--primary-rgb), 0.1);
            transform: translateY(-5px);
        }

        .mobile-bottom-nav .nav-link i {
            font-size: 1.4rem;
        }

        /* Buttons - Futuristic Neomorphism/Glass mix */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: var(--neon-glow);
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: var(--neon-glow-strong);
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 12px;
            background: transparent;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            box-shadow: var(--neon-glow);
            transform: translateY(-2px);
        }
        
        /* Cards Overhaul */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: var(--primary);
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }

        .reveal-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .reveal-up.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .desktop-nav { display: none !important; }
            .mobile-appbar { display: flex; align-items: center; justify-content: space-between; }
            .mobile-bottom-nav { display: flex; }
            body { padding-bottom: 100px; }
        }
        
        @media (min-width: 769px) {
            .desktop-nav { display: flex !important; }
        }



    </style>
</head>
<body>

<!-- Desktop Navigation -->
<nav class="navbar navbar-expand-lg desktop-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Drabya" height="40" class="me-2 rounded-circle" style="object-fit: cover;"> Drabya
        </a>
        
        <button class="navbar-toggler text-white" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'products' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/products.php">
                        <i class="fas fa-store"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'faq' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/faq.php">
                        <i class="fas fa-question-circle"></i> FAQ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'contact' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact.php">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?php echo SITE_URL; ?>/cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="badge rounded-pill badge-notification bg-danger"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($currentUser): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-mdb-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($currentUser['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/orders.php"><i class="fas fa-box me-2"></i> Orders</a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/cart.php"><i class="fas fa-shopping-cart me-2"></i> Cart</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm ms-2" href="<?php echo SITE_URL; ?>/auth/signup.php">
                            Sign Up
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- Mobile AppBar -->
<div class="mobile-appbar">
    <h1 class="m-0 d-flex align-items-center auth-logo"><img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="Drabya" height="32" class="me-2 rounded-circle" style="object-fit: cover;"> Drabya</h1>

</div>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav">
    <ul class="nav nav-justified w-100">
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $currentPage == 'products' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/products.php">
                <i class="fas fa-store"></i>
                <span>Products</span>
            </a>
        </li>
        <li class="nav-item position-relative">
            <a class="nav-link <?php echo $currentPage == 'cart' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/cart.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart</span>
                <?php if ($cartCount > 0): ?>
                    <span class="badge rounded-pill badge-notification bg-danger"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo in_array($currentPage, ['profile', 'orders']) ? 'active' : ''; ?>" 
               href="<?php echo $currentUser ? SITE_URL . '/profile.php' : SITE_URL . '/auth/login.php'; ?>">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>
    </ul>
</nav>

<script>


// Navbar Scroll Effect
window.addEventListener('scroll', function() {
    const nav = document.querySelector('.desktop-nav');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});



// Initialize dropdowns when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Simple dropdown toggle without MDBootstrap dependency
    const dropdownToggle = document.getElementById('userDropdown');
    if (dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdownMenu = this.nextElementSibling;
            const isShown = dropdownMenu.classList.contains('show');
            
            // Close all dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            
            // Toggle current dropdown
            if (!isShown) {
                dropdownMenu.classList.add('show');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }
});
</script>
