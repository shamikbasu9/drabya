<?php
$pageTitle = 'Gifts That Tell Your Story';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get featured products
$featuredProducts = [];
$result = $conn->query("SELECT p.*, pi.image_path, c.name as category_name FROM products p 
                        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = TRUE 
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.is_featured = TRUE AND p.status = 'active' 
                        ORDER BY p.stock_quantity > 0 DESC, p.sales_count DESC LIMIT 6");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
}

// Get categories from database
$categories = [];
$result = $conn->query("SELECT * FROM categories ORDER BY id ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();

// Category-Specific Content Configuration
$curatedCategories = [
    'coffee-mugs' => [
        'name' => 'Coffee Mugs',
        'icon' => 'fa-mug-hot',
        'accent' => '#E8431C',
        'badge' => 'Daily Essential',
        'description' => "Your morning coffee just got a glow-up. Whether you're a minimalist or a maximalist, our premium mugs are designed to match your mood. Grab your favorite and start the day with a smile.",
        'is_coming_soon' => false,
        'keywords' => ['coffee', 'mug']
    ],
    'fridge-magnets' => [
        'name' => 'Fridge Magnets',
        'icon' => 'fa-magnet',
        'accent' => '#00BCD4',
        'badge' => 'Personality Pop',
        'description' => "Small items, big personality. Turn your fridge into a mood board with our vibrant, high-quality magnets. They’re the perfect little treat for yourself or a friend.",
        'is_coming_soon' => false,
        'keywords' => ['magnet', 'fridge']
    ],
    'coasters' => [
        'name' => 'Coasters',
        'icon' => 'fa-circle-dot',
        'accent' => '#FF9800',
        'badge' => 'Desk Décor',
        'description' => "Protect your desk, but make it fashionable. Our coasters add a pop of color to your space while keeping things clean. Functional art for your home or office.",
        'is_coming_soon' => false,
        'keywords' => ['coaster']
    ],
    'tshirts' => [
        'name' => 'T-shirt',
        'icon' => 'fa-tshirt',
        'accent' => '#8B5CF6',
        'badge' => 'Coming Soon',
        'description' => "coming soon",
        'is_coming_soon' => true,
        'keywords' => ['tshirt', 't-shirt', 'shirt']
    ]
];

// Helper to resolve category ID from DB
function getCategoryIdFromDB($dbCats, $slugKey, $keywords) {
    foreach ($dbCats as $cat) {
        $slug = strtolower($cat['slug'] ?? '');
        $name = strtolower($cat['name'] ?? '');
        if ($slug === $slugKey) {
            return $cat['id'];
        }
        foreach ($keywords as $kw) {
            if (strpos($slug, $kw) !== false || strpos($name, $kw) !== false) {
                return $cat['id'];
            }
        }
    }
    return 0;
}
?>

<style>
/* Modern Scoped Aesthetics for Landing Page */
.hero-glow-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.18;
    pointer-events: none;
    z-index: 0;
}
.hero-badge {
    background: rgba(var(--primary-rgb), 0.08);
    border: 1px solid rgba(var(--primary-rgb), 0.2);
    color: var(--primary);
    font-weight: 700;
    letter-spacing: 1px;
}
.hero-highlight-pill {
    color: #000000 !important;
    background-color: #ffffff !important;
    border: 1px solid rgba(0, 0, 0, 0.12) !important;
}
.hero-highlight-pill span {
    color: #000000 !important;
}
.why-card {
    background: var(--card-bg);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 2.2rem 1.8rem;
    height: 100%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    overflow: hidden;
}
.why-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.why-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(var(--primary-rgb), 0.12);
}
.why-card:hover::before {
    opacity: 1;
}
.why-icon-box {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    transition: transform 0.3s ease;
}
.why-card:hover .why-icon-box {
    transform: scale(1.1) rotate(4deg);
}

.category-showcase-card {
    background: var(--card-bg);
    border: 1px solid var(--glass-border);
    border-radius: 24px;
    padding: 2rem 1.6rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-shadow: 0 8px 24px rgba(0,0,0,0.04);
    transition: all 0.35s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
}
.category-showcase-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    border-color: rgba(var(--primary-rgb), 0.3);
}
.category-icon-orb {
    width: 65px;
    height: 65px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.2rem;
    transition: all 0.3s ease;
}
.category-showcase-card:hover .category-icon-orb {
    transform: scale(1.08);
}

.community-card {
    background: var(--card-bg);
    border: 1px solid var(--glass-border);
    border-radius: 22px;
    overflow: hidden;
    height: 100%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    transition: all 0.35s ease;
    display: flex;
    flex-direction: column;
}
.community-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    border-color: rgba(var(--primary-rgb), 0.25);
}
.community-visual-mock {
    height: 220px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.community-visual-mock::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.2);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.community-card:hover .community-visual-mock::after {
    opacity: 1;
}
.community-overlay-icon {
    position: absolute;
    z-index: 2;
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
}
.community-card:hover .community-overlay-icon {
    opacity: 1;
    transform: scale(1);
}
</style>

<!-- ==========================================
     1. HERO SECTION
     Headline: Gifts That Tell Your Story
     Sub-headline: Make it Personal. Make it Drabya.
     ========================================== -->
<section class="hero-section py-5 position-relative overflow-hidden">
    <!-- Floating Background Lighting -->
    <div class="hero-glow-blob" style="top: -50px; left: -50px; width: 350px; height: 350px; background: var(--primary);"></div>
    <div class="hero-glow-blob" style="bottom: 10px; right: -50px; width: 400px; height: 400px; background: var(--secondary);"></div>

    <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center py-lg-4">
            <div class="col-lg-7 mb-5 mb-lg-0 reveal-up">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill hero-badge mb-3">
                    <i class="fas fa-sparkles text-primary small"></i>
                    <span class="small text-uppercase">Make it Personal. Make it Drabya.</span>
                </div>

                <h1 class="display-3 fw-bold mb-3" style="line-height: 1.15; letter-spacing: -0.5px;">
                    Gifts That Tell <span class="text-primary">Your Story</span>
                </h1>

                <h2 class="h4 fw-bold mb-3" style="color: var(--primary); letter-spacing: -0.2px;">
                    Make it Personal. Make it Drabya.
                </h2>

                <p class="lead text-muted mb-4" style="max-width: 580px; font-size: 1.15rem; line-height: 1.75;">
                    From morning coffee to desk décor, we create premium, custom-made merchandise designed to make every moment personal.
                </p>

                <!-- Value Highlights Bar -->
                <div class="d-flex flex-wrap gap-2 mb-4 pb-2">
                    <span class="badge rounded-pill bg-white border shadow-xs py-2 px-3 fw-normal small hero-highlight-pill" style="color: #000000 !important;">
                        <i class="fas fa-award text-primary me-1"></i> <span style="color: #000000 !important; font-weight: 500;">Premium Quality</span>
                    </span>
                    <span class="badge rounded-pill bg-white border shadow-xs py-2 px-3 fw-normal small hero-highlight-pill" style="color: #000000 !important;">
                        <i class="fas fa-wand-magic-sparkles text-warning me-1"></i> <span style="color: #000000 !important; font-weight: 500;">Custom-Made</span>
                    </span>
                    <span class="badge rounded-pill bg-white border shadow-xs py-2 px-3 fw-normal small hero-highlight-pill" style="color: #000000 !important;">
                        <i class="fas fa-truck-fast text-info me-1"></i> <span style="color: #000000 !important; font-weight: 500;">Fast Shipping Across India</span>
                    </span>
                    <span class="badge rounded-pill bg-white border shadow-xs py-2 px-3 fw-normal small hero-highlight-pill" style="color: #000000 !important;">
                        <i class="fas fa-undo text-success me-1"></i> <span style="color: #000000 !important; font-weight: 500;">7-Day Returns</span>
                    </span>
                </div>

                <!-- CTAs -->
                <div class="d-flex flex-wrap gap-3">
                    <a href="products.php" class="btn btn-primary btn-lg px-4 px-md-5 rounded-pill shadow-lg">
                        <i class="fas fa-bolt me-2"></i> Explore Store
                    </a>
                    <a href="#why-drabya" class="btn btn-outline-primary btn-lg px-4 px-md-5 rounded-pill">
                        <i class="fas fa-heart me-2"></i> Why Choose Drabya?
                    </a>
                </div>
            </div>

            <div class="col-lg-5 text-center reveal-up" style="transition-delay: 0.15s;">
                <div class="hero-image-container position-relative">
                    <!-- Hero Showcase Card -->
                    <div class="card p-4 rounded-4 shadow-lg border-0 mx-auto" style="max-width: 340px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px);">
                        <div class="mb-3 mx-auto d-flex align-items-center justify-content-center" 
                             style="width: 90px; height: 90px; background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.15) 0%, rgba(var(--primary-rgb), 0.05) 100%); border-radius: 50%;">
                            <i class="fas fa-gift fa-3x text-primary animate-float" style="filter: drop-shadow(0 4px 12px rgba(var(--primary-rgb), 0.35));"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Gifts That Speak</h5>
                        <p class="text-muted small mb-3">Custom merchandise crafted to express your identity</p>
                        
                        <div class="p-3 rounded-3 bg-light text-start mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-main"><i class="fas fa-mug-hot text-primary me-2"></i>Coffee Mugs</span>
                                <span class="badge bg-success rounded-pill small">Ready</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-main"><i class="fas fa-magnet text-info me-2"></i>Fridge Magnets</span>
                                <span class="badge bg-success rounded-pill small">Ready</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-main"><i class="fas fa-circle-dot text-warning me-2"></i>Coasters</span>
                                <span class="badge bg-success rounded-pill small">Ready</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small fw-bold text-main"><i class="fas fa-tshirt text-purple me-2"></i>T-shirt</span>
                                <span class="badge bg-dark rounded-pill small">Soon</span>
                            </div>
                        </div>

                        <a href="products.php" class="btn btn-primary btn-sm rounded-pill py-2 w-100 fw-bold">
                            Shop The Collection <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     2. WHY CHOOSE DRABYA? SECTION
     ◦ Premium Quality: Hand-picked materials for a long-lasting finish.
     ◦ Custom-Made: Unique designs crafted just for you or your loved ones.
     ◦ Fast & Reliable: Flat-rate shipping across India and a hassle-free 7-day return policy.
     ========================================== -->
<section class="py-5 bg-glass-soft" id="why-drabya">
    <div class="container">
        <div class="text-center mb-5 reveal-up">
            <span class="badge rounded-pill hero-badge px-3 py-2 text-uppercase mb-2">Our Promise</span>
            <h2 class="fw-bold display-6 mb-2">Why Choose Drabya?</h2>
            <p class="text-muted mx-auto mb-3" style="max-width: 600px;">
                Designed to make every moment personal with craftsmanship you can feel.
            </p>
            <div class="mx-auto" style="width: 50px; height: 3px; background: var(--primary); border-radius: 2px;"></div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- 1. Premium Quality -->
            <div class="col-md-6 col-lg-4 reveal-up">
                <div class="why-card">
                    <div class="why-icon-box" style="background: rgba(var(--primary-rgb), 0.1);">
                        <i class="fas fa-award fa-2x text-primary"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Premium Quality</h4>
                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        Hand-picked materials for a long-lasting finish. Every piece is carefully crafted and checked so your memories stay vibrant for years.
                    </p>
                    <div class="d-inline-flex align-items-center text-primary small fw-bold">
                        <i class="fas fa-check-circle me-2"></i> Hand-picked Materials
                    </div>
                </div>
            </div>

            <!-- 2. Custom-Made -->
            <div class="col-md-6 col-lg-4 reveal-up" style="transition-delay: 0.1s;">
                <div class="why-card">
                    <div class="why-icon-box" style="background: rgba(255, 193, 7, 0.12);">
                        <i class="fas fa-wand-magic-sparkles fa-2x text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Custom-Made</h4>
                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        Unique designs crafted just for you or your loved ones. Thoughtful, personalized gifts that tell your true story and stand out from the ordinary.
                    </p>
                    <div class="d-inline-flex align-items-center text-warning small fw-bold">
                        <i class="fas fa-heart me-2"></i> Crafted Just for You
                    </div>
                </div>
            </div>

            <!-- 3. Fast & Reliable -->
            <div class="col-md-6 col-lg-4 reveal-up" style="transition-delay: 0.2s;">
                <div class="why-card">
                    <div class="why-icon-box" style="background: rgba(0, 188, 212, 0.12);">
                        <i class="fas fa-truck-fast fa-2x text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Fast & Reliable</h4>
                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        Flat-rate shipping across India and a hassle-free 7-day return policy. Reliable doorstep delivery with real-time tracking for peace of mind.
                    </p>
                    <div class="d-inline-flex align-items-center text-info small fw-bold">
                        <i class="fas fa-shield-alt me-2"></i> Flat-rate Shipping • 7-Day Returns
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==========================================
     3. CATEGORY-SPECIFIC CONTENT SECTION
     Coffee Mugs, Fridge Magnets, Coasters, T-shirt (Coming Soon)
     ========================================== -->
<section class="py-5" id="categories">
    <div class="container">
        <div class="text-center mb-5 reveal-up">
            <span class="badge rounded-pill hero-badge px-3 py-2 text-uppercase mb-2">Curated Collections</span>
            <h2 class="fw-bold display-6 mb-2">Shop by Category</h2>
            <p class="text-muted mx-auto mb-3" style="max-width: 620px;">
                Find the perfect piece tailored to your mood, home, or workspace.
            </p>
            <div class="mx-auto" style="width: 50px; height: 3px; background: var(--primary); border-radius: 2px;"></div>
        </div>

        <div class="row g-4">
            <?php 
            $catIndex = 0;
            foreach ($curatedCategories as $catKey => $catData): 
                $resolvedId = getCategoryIdFromDB($categories, $catKey, $catData['keywords']);
                $catLink = $resolvedId > 0 ? "products.php?category=" . $resolvedId : "products.php";
                $catIndex++;
            ?>
                <div class="col-md-6 col-lg-3 reveal-up" style="transition-delay: <?php echo 0.08 * $catIndex; ?>s;">
                    <div class="category-showcase-card">
                        <!-- Top Header / Badge -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="category-icon-orb" style="background: <?php echo $catData['accent']; ?>15; color: <?php echo $catData['accent']; ?>;">
                                <i class="fas <?php echo $catData['icon']; ?> fa-2x"></i>
                            </div>
                            <?php if ($catData['is_coming_soon']): ?>
                                <span class="badge rounded-pill bg-dark text-white px-3 py-2 small fw-bold">
                                    <i class="fas fa-clock me-1"></i> COMING SOON
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill px-3 py-2 small fw-semibold" style="background: <?php echo $catData['accent']; ?>15; color: <?php echo $catData['accent']; ?>;">
                                    <?php echo $catData['badge']; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Title -->
                        <h4 class="fw-bold mb-3 text-main"><?php echo htmlspecialchars($catData['name']); ?></h4>

                        <!-- Specific Copy Description -->
                        <p class="text-muted mb-4 small flex-grow-1" style="line-height: 1.7; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($catData['description']); ?>
                        </p>

                        <!-- Action Link / Button -->
                        <div class="mt-auto pt-2">
                            <?php if ($catData['is_coming_soon']): ?>
                                <button class="btn btn-outline-secondary w-100 rounded-pill py-2 disabled" style="opacity: 0.7;">
                                    <i class="fas fa-hourglass-half me-1"></i> Coming Soon
                                </button>
                            <?php else: ?>
                                <a href="<?php echo $catLink; ?>" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center">
                                    <span>Explore <?php echo htmlspecialchars($catData['name']); ?></span>
                                    <i class="fas fa-arrow-right ms-2 small"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==========================================
     4. FEATURED PRODUCTS
     ========================================== -->
<section class="py-5 bg-light" id="featured">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 reveal-up">
            <div>
                <span class="badge rounded-pill hero-badge px-3 py-2 text-uppercase mb-2">Hand-Picked</span>
                <h2 class="fw-bold display-6 mb-0">Featured Products</h2>
            </div>
            <a href="products.php" class="btn btn-outline-primary rounded-pill px-4">
                View All <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php if (empty($featuredProducts)): ?>
                <div class="col-12 text-center py-5">
                    <div class="mb-3 mx-auto d-flex align-items-center justify-content-center rounded-circle bg-white shadow-sm" style="width: 90px; height: 90px;">
                        <i class="fas fa-box-open fa-3x text-muted"></i>
                    </div>
                    <h5 class="fw-bold">No featured products yet</h5>
                    <p class="text-muted">Check back soon or explore our complete catalog.</p>
                    <a href="products.php" class="btn btn-primary rounded-pill px-4">Browse Catalog</a>
                </div>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="col-6 col-md-4 col-lg-2 reveal-up" style="transition-delay: <?php echo 0.08 * ($product['id'] % 6); ?>s;">
                        <div class="card product-card h-100 border-0 shadow-sm" style="border-radius: 20px;">
                            <div class="position-relative overflow-hidden" style="border-radius: 20px 20px 0 0;">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="d-block">
                                    <?php if ($product['image_path']): ?>
                                        <img src="<?php echo getProductImageUrl($product['image_path']); ?>" 
                                             class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>"
                                             style="transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1); aspect-ratio: 1/1; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <i class="fas fa-gift fa-3x text-muted opacity-50"></i>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                    <span class="position-absolute top-0 start-0 m-2 badge rounded-pill bg-danger shadow">
                                        -<?php echo calculateDiscountPercentage($product['price'], $product['discount_price']); ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-3 d-flex flex-column">
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name'] ?? 'Drabya Custom'); ?></p>
                                <h6 class="card-title fw-bold mb-2 small">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-main stretched-link">
                                        <?php echo displayTitle($product['title']); ?>
                                    </a>
                                </h6>
                                <div class="d-flex align-items-center justify-content-between mt-auto pt-2" style="position: relative; z-index: 2;">
                                    <div>
                                        <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                            <span class="text-primary fw-bold"><?php echo formatCurrency($product['discount_price']); ?></span>
                                            <span class="text-muted text-decoration-line-through x-small ms-1 d-block" style="font-size: 0.7rem;">
                                                <?php echo formatCurrency($product['price']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-primary fw-bold"><?php echo formatCurrency($product['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <button onclick="event.preventDefault(); addToCart(<?php echo $product['id']; ?>)" 
                                            class="btn btn-primary btn-sm rounded-circle shadow-sm" 
                                            style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;"
                                            title="Add to cart">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==========================================
     5. JOIN THE COMMUNITY SECTION
     Tag us in your photos @DRABYA.INDIA for a chance to be featured!
     We love seeing how you style your Drabya gear.  #GiftsThatSpeak
     ========================================== -->
<section class="py-5 my-3 position-relative overflow-hidden" id="community">
    <div class="container position-relative">
        <div class="card border-0 shadow-sm p-4 p-md-5 rounded-5" style="background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.04) 0%, rgba(0, 242, 255, 0.04) 100%); border: 1px solid var(--glass-border) !important;">
            
            <!-- Section Header -->
            <div class="text-center mb-5 reveal-up">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill hero-badge mb-3">
                    <i class="fab fa-instagram text-primary"></i>
                    <span class="small text-uppercase">#GiftsThatSpeak</span>
                </div>
                
                <h2 class="display-5 fw-bold mb-3">Join the Community</h2>
                
                <p class="lead text-muted mx-auto mb-3" style="max-width: 650px;">
                    Tag us in your photos <a href="https://instagram.com/DRABYA.INDIA" target="_blank" rel="noopener noreferrer" class="fw-bold text-primary text-decoration-none">@DRABYA.INDIA</a> for a chance to be featured! We love seeing how you style your Drabya gear.
                </p>

                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white shadow-xs border">
                    <span class="badge bg-primary rounded-pill"><i class="fas fa-hashtag"></i></span>
                    <span class="fw-bold text-main" style="letter-spacing: 0.5px;">#GiftsThatSpeak</span>
                </div>
            </div>

            <!-- Community Visual Grid -->
            <div class="row g-4 mb-5">
                <!-- Post 1: Coffee Mug -->
                <div class="col-6 col-md-3 reveal-up">
                    <a href="https://instagram.com/DRABYA.INDIA" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="community-card">
                            <div class="community-visual-mock" style="background: linear-gradient(135deg, #FFE8E2 0%, #FFD6CC 100%);">
                                <div class="text-center">
                                    <i class="fas fa-mug-hot fa-4x text-primary mb-2 animate-float"></i>
                                    <div class="small fw-bold text-main">Morning Glow Mug</div>
                                </div>
                                <div class="community-overlay-icon text-white">
                                    <i class="fab fa-instagram fa-3x"></i>
                                </div>
                            </div>
                            <div class="p-3 bg-white flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-bold text-primary">@DRABYA.INDIA</span>
                                    <span class="small text-muted"><i class="fas fa-heart text-danger me-1"></i>284</span>
                                </div>
                                <p class="x-small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                    "Morning coffee ritual hits different with my custom mug ☕✨"
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Post 2: Fridge Magnets -->
                <div class="col-6 col-md-3 reveal-up" style="transition-delay: 0.1s;">
                    <a href="https://instagram.com/DRABYA.INDIA" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="community-card">
                            <div class="community-visual-mock" style="background: linear-gradient(135deg, #E0F7FA 0%, #B2EBF2 100%);">
                                <div class="text-center">
                                    <i class="fas fa-magnet fa-4x text-info mb-2 animate-float" style="animation-delay: -1s;"></i>
                                    <div class="small fw-bold text-main">Fridge Mood Board</div>
                                </div>
                                <div class="community-overlay-icon text-white">
                                    <i class="fab fa-instagram fa-3x"></i>
                                </div>
                            </div>
                            <div class="p-3 bg-white flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-bold text-primary">@DRABYA.INDIA</span>
                                    <span class="small text-muted"><i class="fas fa-heart text-danger me-1"></i>342</span>
                                </div>
                                <p class="x-small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                    "Turned our fridge into an actual personality board! 🧲❤️"
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Post 3: Coasters -->
                <div class="col-6 col-md-3 reveal-up" style="transition-delay: 0.2s;">
                    <a href="https://instagram.com/DRABYA.INDIA" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="community-card">
                            <div class="community-visual-mock" style="background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);">
                                <div class="text-center">
                                    <i class="fas fa-circle-dot fa-4x text-warning mb-2 animate-float" style="animation-delay: -2s;"></i>
                                    <div class="small fw-bold text-main">Desk Art Coaster</div>
                                </div>
                                <div class="community-overlay-icon text-white">
                                    <i class="fab fa-instagram fa-3x"></i>
                                </div>
                            </div>
                            <div class="p-3 bg-white flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-bold text-primary">@DRABYA.INDIA</span>
                                    <span class="small text-muted"><i class="fas fa-heart text-danger me-1"></i>196</span>
                                </div>
                                <p class="x-small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                    "Functional art for my work setup. No coffee rings ever! ✨"
                                </p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Post 4: Unboxing Gift -->
                <div class="col-6 col-md-3 reveal-up" style="transition-delay: 0.3s;">
                    <a href="https://instagram.com/DRABYA.INDIA" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                        <div class="community-card">
                            <div class="community-visual-mock" style="background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);">
                                <div class="text-center">
                                    <i class="fas fa-gift fa-4x text-purple mb-2 animate-float" style="color: #8B5CF6; animation-delay: -1.5s;"></i>
                                    <div class="small fw-bold text-main">Unboxing Moment</div>
                                </div>
                                <div class="community-overlay-icon text-white">
                                    <i class="fab fa-instagram fa-3x"></i>
                                </div>
                            </div>
                            <div class="p-3 bg-white flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-bold text-primary">@DRABYA.INDIA</span>
                                    <span class="small text-muted"><i class="fas fa-heart text-danger me-1"></i>419</span>
                                </div>
                                <p class="x-small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                    "The joy of gifting personalized merchandise #GiftsThatSpeak 🎁"
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Instagram CTA Button -->
            <div class="text-center reveal-up">
                <a href="https://instagram.com/DRABYA.INDIA" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg">
                    <i class="fab fa-instagram me-2"></i> Follow Us @DRABYA.INDIA
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Add To Cart & Reveal Logic -->
<script>
function addToCart(productId) {
    fetch('cart-action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-4 px-4 py-3 bg-dark text-white rounded-pill shadow-lg reveal-up active';
            toast.style.zIndex = '9999';
            toast.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i> Added to cart!';
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('active');
                setTimeout(() => toast.remove(), 600);
            }, 3000);
            
            const countBadge = document.querySelector('.badge-notification');
            if (countBadge) {
                countBadge.innerText = parseInt(countBadge.innerText) + 1;
            } else {
                location.reload(); 
            }
        } else {
            alert(data.message || 'Failed to add to cart');
        }
    })
    .catch(err => {
        console.error(err);
    });
}

// Reveal on Scroll Logic
const observerOptions = {
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
        }
    });
}, observerOptions);

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));
});
</script>

<?php require_once 'includes/footer.php'; ?>
