<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'Drabya');
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_dir = rtrim(dirname($_SERVER['SCRIPT_NAME'], 2), '/\\');
// If script is in root, dirname is \ or / so we need to handle that
if ($base_dir === '/' || $base_dir === '\\') {
    $base_dir = '';
}
// check if script is in admin or auth folder, dirname might be root, so just use logic based on document_root
$doc_root = $_SERVER['DOCUMENT_ROOT'];
$script_filename = $_SERVER['SCRIPT_FILENAME'];
$app_path = str_replace('\\', '/', dirname(__DIR__));
$doc_root = str_replace('\\', '/', $doc_root);
$relative_path = str_replace($doc_root, '', $app_path);
define('SITE_URL', $protocol . '://' . $domain . $relative_path);
define('ADMIN_EMAIL', 'admin@drabya.org.in');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PRODUCT_IMAGES_DIR', UPLOAD_DIR . 'products/');
define('PRODUCT_VIDEOS_DIR', UPLOAD_DIR . 'videos/');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB

if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!file_exists(PRODUCT_IMAGES_DIR)) mkdir(PRODUCT_IMAGES_DIR, 0755, true);
if (!file_exists(PRODUCT_VIDEOS_DIR)) mkdir(PRODUCT_VIDEOS_DIR, 0755, true);

define('DEFAULT_CURRENCY', 'INR');
define('SHIPPING_CHARGE', 50);

// Razorpay Configuration
define('RAZORPAY_TEST_MODE', false); // Set to false for live mode
define('RAZORPAY_TEST_KEY', 'rzp_test_Toejm182Hz1CHC'); // Replace with your test key
define('RAZORPAY_TEST_SECRET', 'GDt7mdYFZfPvx0lH7VpOhKro'); // Replace with your test secret
define('RAZORPAY_LIVE_KEY', 'rzp_live_Tzi2GAZyoy0yWr'); // Live key
define('RAZORPAY_LIVE_SECRET', 'P2P7Jo3s7NYgO2LNr7WOdRcz'); // Live secret

// Get active Razorpay credentials based on mode
define('RAZORPAY_KEY', RAZORPAY_TEST_MODE ? RAZORPAY_TEST_KEY : RAZORPAY_LIVE_KEY);
define('RAZORPAY_SECRET', RAZORPAY_TEST_MODE ? RAZORPAY_TEST_SECRET : RAZORPAY_LIVE_SECRET);

date_default_timezone_set('Asia/Kolkata');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/database.php';
// No closing PHP tag to prevent whitespace issues
