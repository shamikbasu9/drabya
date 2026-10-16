<?php
$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getDBConnection();
        $userId = isUserLoggedIn() ? $_SESSION['user_id'] : null;
        
        $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $userId, $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            $success = 'Your message has been sent! We will get back to you soon.';
        } else {
            $error = 'Failed to send message';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<div class="container my-5 reveal-up">
    <div class="row g-4 d-flex align-items-stretch">
        <div class="col-lg-5">
            <h1 class="fw-bold mb-4">Get in <span class="text-primary">Touch</span></h1>
            <p class="text-muted mb-5 pe-lg-4" style="line-height: 1.8;">
                Have questions about our custom merchandise or an existing order? 
                Our team is ready to assist you. Reach out through any of these channels.
            </p>
            
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3 d-flex align-items-center justify-content-center bg-soft-primary" 
                             style="width: 50px; height: 50px; background: rgba(var(--primary-rgb), 0.1); border-radius: 12px; flex-shrink: 0;">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Our Location</h6>
                            <p class="text-muted small mb-0">Kolkata - 700026, India</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3 d-flex align-items-center justify-content-center bg-soft-info" 
                             style="width: 50px; height: 50px; background: rgba(0, 242, 255, 0.1); border-radius: 12px; flex-shrink: 0;">
                            <i class="fas fa-phone-alt text-info"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Phone Support</h6>
                            <p class="text-muted small mb-0">+91 82404 47187</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        <div class="icon-box me-3 d-flex align-items-center justify-content-center bg-soft-warning" 
                             style="width: 50px; height: 50px; background: rgba(255, 215, 0, 0.1); border-radius: 12px; flex-shrink: 0;">
                            <i class="fas fa-envelope text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Online Query</h6>
                            <p class="text-muted small mb-0">support@drabya.org.in</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert bg-glass-soft border-0 reveal-up" style="border-radius: 20px; transition-delay: 0.2s;">
                <p class="small text-muted mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i> 
                    Average response time: <strong>4 business hours</strong>.
                </p>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card border-0 shadow-lg h-100" style="border-radius: 24px;">
                <div class="card-body p-4 p-md-5">
                    <h4 class="fw-bold mb-4">Drop us a Message</h4>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-pill px-4 animate-float">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-pill px-4">
                            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">NAME</label>
                                <input type="text" class="form-control border-0 bg-light rounded-pill px-4" name="name" 
                                       placeholder="Your Name" required style="height: 50px;">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold text-muted">EMAIL</label>
                                <input type="email" class="form-control border-0 bg-light rounded-pill px-4" name="email" 
                                       placeholder="Your Email" required style="height: 50px;">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">SUBJECT</label>
                            <input type="text" class="form-control border-0 bg-light rounded-pill px-4" name="subject" 
                                   placeholder="How can we help?" required style="height: 50px;">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">MESSAGE</label>
                            <textarea class="form-control border-0 bg-light px-4 py-3" name="message" rows="5" 
                                      placeholder="Write your message here..." required style="border-radius: 20px;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold">
                            <i class="fas fa-paper-plane me-2"></i> Dispatch Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
