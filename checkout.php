<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
requireUserLogin('signup');

$pageTitle = 'Checkout';
require_once 'includes/header.php';

$cartItems = getCartItems();
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

$subtotal = getCartTotal();
$shipping = calculateShipping($subtotal);
$discount = 0;
$couponCode = '';

// Handle coupon
if (isset($_POST['apply_coupon'])) {
    $couponCode = sanitize($_POST['coupon_code']);
    $validation = validateCoupon($couponCode, $subtotal);
    
    if ($validation['valid']) {
        $discount = $validation['discount'];
        $_SESSION['applied_coupon'] = $validation['coupon'];
    } else {
        $couponError = $validation['message'];
    }
}

if (isset($_SESSION['applied_coupon'])) {
    $discount = $_SESSION['applied_coupon']['discount_type'] === 'flat' 
        ? $_SESSION['applied_coupon']['discount_value'] 
        : ($subtotal * $_SESSION['applied_coupon']['discount_value']) / 100;
    
    if ($_SESSION['applied_coupon']['max_discount'] && $discount > $_SESSION['applied_coupon']['max_discount']) {
        $discount = $_SESSION['applied_coupon']['max_discount'];
    }
}

$total = $subtotal + $shipping - $discount;

// Get current user
$currentUser = getCurrentUser();

// Get user addresses
$conn = getDBConnection();
$userId = $_SESSION['user_id'];
$addresses = [];
$result = $conn->query("SELECT * FROM addresses WHERE user_id = $userId ORDER BY is_default DESC");
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
$conn->close();
?>

<div class="container my-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-lock"></i> Secure Checkout</h2>
    
    <div class="row g-4">
        <!-- Checkout Form -->
        <div class="col-lg-8">
            <!-- Shipping Address -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Shipping Address</h5>
                    
                    <?php if (empty($addresses)): ?>
                        <form id="addressForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone *</label>
                                    <input type="tel" class="form-control" name="phone" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 1 *</label>
                                    <input type="text" class="form-control" name="address_line1" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" class="form-control" name="address_line2">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City *</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State *</label>
                                    <input type="text" class="form-control" name="state" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pincode *</label>
                                    <input type="text" class="form-control" name="pincode" required>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <select class="form-select" id="addressSelect">
                            <?php foreach ($addresses as $address): ?>
                                <option value="<?php echo $address['id']; ?>" <?php echo $address['is_default'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($address['full_name']); ?> - 
                                    <?php echo htmlspecialchars($address['address_line1']); ?>, 
                                    <?php echo htmlspecialchars($address['city']); ?>, 
                                    <?php echo htmlspecialchars($address['state']); ?> - 
                                    <?php echo htmlspecialchars($address['pincode']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Payment Method</h5>
                    
                    <?php if (RAZORPAY_TEST_MODE): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-flask"></i> <strong>Test Mode Active</strong> - Use test cards for payment
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-mobile-alt me-2"></i> Secure payment via UPI (Google Pay, PhonePe, Paytm)
                        <?php if (RAZORPAY_TEST_MODE): ?>
                            <span class="badge bg-warning text-dark ms-2">TEST MODE</span>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="payment_method" value="razorpay">
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    
                    <!-- Cart Items -->
                    <div class="mb-3" style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <small><?php echo htmlspecialchars($item['title']); ?> x<?php echo $item['quantity']; ?></small>
                                <small><?php echo formatCurrency($item['price'] * $item['quantity']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <!-- Coupon -->
                    <form method="POST" class="mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" name="coupon_code" placeholder="Coupon code" 
                                   value="<?php echo isset($_SESSION['applied_coupon']) ? $_SESSION['applied_coupon']['code'] : ''; ?>">
                            <button type="submit" name="apply_coupon" class="btn btn-outline-success">Apply</button>
                        </div>
                        <?php if (isset($couponError)): ?>
                            <small class="text-danger"><?php echo $couponError; ?></small>
                        <?php endif; ?>
                    </form>
                    
                    <!-- Totals -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping</span>
                        <span><?php echo $shipping > 0 ? formatCurrency($shipping) : 'FREE'; ?></span>
                    </div>
                    
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>-<?php echo formatCurrency($discount); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong class="text-success"><?php echo formatCurrency($total); ?></strong>
                    </div>
                    
                    <button type="button" id="placeOrderBtn" onclick="placeOrder(event)" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
function resetButton() {
    const btn = document.getElementById('placeOrderBtn');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Place Order';
    }
}

async function placeOrder(e) {
    if (e && e.preventDefault) e.preventDefault();

    const btn = document.getElementById('placeOrderBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    try {
        const paymentMethod = document.querySelector('input[name="payment_method"]');
        if (!paymentMethod || !paymentMethod.value) {
            alert('Please select a payment method');
            resetButton();
            return;
        }

        let addressId = document.getElementById('addressSelect')?.value;

        // If no saved address, create one from the form
        if (!addressId) {
            const addressForm = document.getElementById('addressForm');
            if (!addressForm) {
                alert('Please provide a shipping address');
                resetButton();
                return;
            }

            if (!addressForm.reportValidity()) {
                resetButton();
                return;
            }

            const formData = new FormData(addressForm);
            formData.append('is_default', '1');

            try {
                const response = await fetch('save-address.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (!result.success) {
                    alert(result.message || 'Failed to save address');
                    resetButton();
                    return;
                }

                addressId = result.address_id;
            } catch (error) {
                console.error('Error saving address:', error);
                alert('Error saving address. Please try again.');
                resetButton();
                return;
            }
        }

        // Create order
        const response = await fetch('process-order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'payment_method=' + encodeURIComponent(paymentMethod.value) + '&address_id=' + encodeURIComponent(addressId)
        });

        const text = await response.text();
        console.log('Order creation response:', text);

        let data;
        try {
            data = JSON.parse(text);
        } catch (err) {
            console.error('JSON parse error:', err, text);
            alert('Server error: ' + text.substring(0, 150));
            resetButton();
            return;
        }

        if (data.success) {
            createRazorpayOrder(data.order_id, data.order_number, data.order_amount);
        } else {
            alert(data.message || 'Failed to place order');
            resetButton();
        }
    } catch (error) {
        console.error('placeOrder error:', error);
        alert('An unexpected error occurred: ' + error.message);
        resetButton();
    }
}

function createRazorpayOrder(orderId, orderNumber, amount) {
    const btn = document.getElementById('placeOrderBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Preparing Payment...';
    }

    const formattedAmount = parseFloat(amount).toFixed(2);

    fetch('create-razorpay-order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'order_id=' + encodeURIComponent(orderId) + '&amount=' + encodeURIComponent(formattedAmount)
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Invalid gateway response: ' + text.substring(0, 150));
        }
    })
    .then(data => {
        if (data.success && data.razorpay_order_id) {
            initiateRazorpayPayment(orderId, orderNumber, data.razorpay_order_id, data.amount);
        } else {
            throw new Error(data.message || 'Failed to initialize payment gateway');
        }
    })
    .catch(error => {
        console.error('Razorpay order error:', error);
        alert(error.message || 'Payment initiation failed. Please try again.');
        resetButton();
    });
}

function initiateRazorpayPayment(orderId, orderNumber, razorpayOrderId, amount) {
    const btn = document.getElementById('placeOrderBtn');

    if (typeof Razorpay === 'undefined') {
        alert('Razorpay payment library failed to load. Please disable ad-blockers or check your internet connection.');
        resetButton();
        return;
    }

    const options = {
        key: <?php echo json_encode(RAZORPAY_KEY); ?>,
        amount: amount, // Amount in paise
        currency: 'INR',
        name: <?php echo json_encode(SITE_NAME); ?>,
        description: 'Order #' + orderNumber,
        order_id: razorpayOrderId,
        prefill: {
            name: <?php echo json_encode($currentUser['name'] ?? ''); ?>,
            email: <?php echo json_encode($currentUser['email'] ?? ''); ?>,
            contact: <?php echo json_encode($currentUser['phone'] ?? ''); ?>
        },
        theme: {
            color: '#2e7d32'
        },
        handler: function(response) {
            verifyPayment(orderId, response.razorpay_payment_id, response.razorpay_signature);
        },
        modal: {
            ondismiss: function() {
                alert('Payment cancelled. Your order has been saved. You can complete payment later from your orders page.');
                window.location.href = 'orders.php';
            }
        }
    };

    try {
        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function(response) {
            console.error('Payment failed:', response.error);
            alert('Payment failed: ' + (response.error.description || response.error.reason || 'Unknown error'));
        });
        rzp.open();
    } catch (e) {
        console.error('Razorpay open error:', e);
        alert('Failed to open payment modal: ' + e.message);
        resetButton();
    }
}

function verifyPayment(orderId, paymentId, signature) {
    const btn = document.getElementById('placeOrderBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying Payment...';
    }

    fetch('verify-payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'order_id=' + encodeURIComponent(orderId) + '&payment_id=' + encodeURIComponent(paymentId) + '&signature=' + encodeURIComponent(signature)
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Invalid verification response: ' + text.substring(0, 150));
        }
    })
    .then(data => {
        if (data.success) {
            window.location.href = 'order-success.php?order=' + orderId;
        } else {
            alert('Payment verification failed: ' + (data.message || 'Please contact support.'));
            window.location.href = 'orders.php';
        }
    })
    .catch(error => {
        console.error('Verification error:', error);
        alert('Error during verification: ' + error.message);
        window.location.href = 'orders.php';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
