<?php
$pageTitle = 'FAQ';
require_once 'includes/header.php';

$conn = getDBConnection();
$faqs = [];
$result = $conn->query("SELECT * FROM faqs WHERE is_active = TRUE ORDER BY display_order");
while ($row = $result->fetch_assoc()) {
    $faqs[] = $row;
}
$conn->close();
?>

<div class="container my-5 reveal-up">
    <div class="text-center mb-5">
        <h1 class="fw-bold h1 mb-2">Frequently Asked <span class="text-primary">Questions</span></h1>
        <div class="mx-auto mb-3" style="width: 60px; height: 4px; background: var(--primary); border-radius: 2px;"></div>
        <p class="text-muted">Quick answers to common questions about our products and services</p>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion futuristic-accordion" id="faqAccordion">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="accordion-item mb-3 border-0 shadow-sm reveal-up" style="border-radius: 16px; overflow: hidden; transition-delay: <?php echo 0.1 * $index; ?>s;">
                        <h2 class="accordion-header">
                            <button class="accordion-button py-4 fw-bold <?php echo $index > 0 ? 'collapsed' : ''; ?>" 
                                    type="button" data-mdb-toggle="collapse" 
                                    data-mdb-target="#faq<?php echo $faq['id']; ?>"
                                    style="background: transparent; box-shadow: none;">
                                <i class="fas fa-question-circle text-primary me-3"></i>
                                <?php echo htmlspecialchars($faq['question']); ?>
                            </button>
                        </h2>
                        <div id="faq<?php echo $faq['id']; ?>" 
                             class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                             data-mdb-parent="#faqAccordion">
                            <div class="accordion-body text-muted pt-0 pb-4 ps-5" style="line-height: 1.7;">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="card bg-glass-soft border-0 mt-5 reveal-up" style="border-radius: 24px;">
                <div class="card-body text-center p-5">
                    <div class="icon-circle mb-4 mx-auto d-flex align-items-center justify-content-center" 
                         style="width: 70px; height: 70px; background: rgba(var(--primary-rgb), 0.1); border-radius: 50%;">
                        <i class="fas fa-comment-dots fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2 h3">Still have questions?</h5>
                    <p class="text-muted mb-4">Our support team is ready to help you with detailed inquiries.</p>
                    <a href="contact.php" class="btn btn-primary btn-lg rounded-pill px-5">
                        <i class="fas fa-envelope me-2"></i> Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .futuristic-accordion .accordion-button:not(.collapsed) {
        color: var(--primary);
    }
    .futuristic-accordion .accordion-button::after {
        background-size: 1rem;
        transition: transform 0.3s ease;
    }
    .accordion-item {
        background: var(--card-bg);
    }
</style>

<script>
// Use standard reveal observer
const f_observerOptions = { threshold: 0.1 };
const f_observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
        }
    });
}, f_observerOptions);

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.reveal-up').forEach(el => f_observer.observe(el));
    
    // MDB Collapse Manual Logic (if MDB script isn't handling it)
    const accordionButtons = document.querySelectorAll('.accordion-button');
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-mdb-target');
            const targetCollapse = document.querySelector(targetId);
            const isExpanded = !this.classList.contains('collapsed');
            
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                if (collapse !== targetCollapse) {
                    collapse.classList.remove('show');
                    const btn = document.querySelector(`[data-mdb-target="#${collapse.id}"]`);
                    if (btn) btn.classList.add('collapsed');
                }
            });
            
            if (isExpanded) {
                targetCollapse.classList.remove('show');
                this.classList.add('collapsed');
            } else {
                targetCollapse.classList.add('show');
                this.classList.remove('collapsed');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
