<?php
$pageTitle = 'Products';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get filters
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$sql = "SELECT p.*, pi.image_path, c.name as category_name 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = TRUE 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active'";

if ($categoryId > 0) {
    $sql .= " AND p.category_id = $categoryId";
}
if ($search) {
    $sql .= " AND (p.title LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if ($minPrice > 0) {
    $sql .= " AND p.price >= $minPrice";
}
if ($maxPrice < 10000) {
    $sql .= " AND p.price <= $maxPrice";
}

// Sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.stock_quantity > 0 DESC, p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.stock_quantity > 0 DESC, p.price DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.stock_quantity > 0 DESC, p.sales_count DESC";
        break;
    default:
        $sql .= " ORDER BY p.stock_quantity > 0 DESC, p.created_at DESC";
}

$result = $conn->query($sql);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get categories for filter
$categories = [];
$catResult = $conn->query("SELECT * FROM categories ORDER BY name");
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

$conn->close();
?>

<div class="container my-4">
    <div class="row">
        <!-- Filters Sidebar (Desktop) -->
        <div class="col-lg-3 mb-4 d-none d-lg-block">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px; border-radius: 24px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center">
                        <i class="fas fa-sliders-h me-2 text-primary"></i> Filters
                    </h5>
                    
                    <form method="GET" action="">
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Search</label>
                            <div class="input-group input-group-lg border rounded-pill overflow-hidden bg-light">
                                <span class="input-group-text border-0 bg-transparent"><i class="fas fa-search text-muted small"></i></span>
                                <input type="text" class="form-control border-0 bg-transparent" name="search" 
                                       placeholder="Find anything..." value="<?php echo htmlspecialchars($search); ?>" style="font-size: 0.9rem;">
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Category</label>
                            <select class="form-select border-0 bg-light rounded-pill px-3" name="category" style="font-size: 0.9rem;">
                                <option value="0">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control border-0 bg-light rounded-pill" name="min_price" 
                                           placeholder="Min" value="<?php echo $minPrice; ?>" style="font-size: 0.9rem;">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control border-0 bg-light rounded-pill" name="max_price" 
                                           placeholder="Max" value="<?php echo $maxPrice; ?>" style="font-size: 0.9rem;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Sort By</label>
                            <select class="form-select border-0 bg-light rounded-pill px-3" name="sort" style="font-size: 0.9rem;">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-2">Apply Changes</button>
                        <a href="products.php" class="btn btn-link w-100 text-muted text-decoration-none small">Reset All</a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Mobile Filters -->
            <div class="d-lg-none mb-4">
                <div class="row g-2">
                    <div class="col-12 mb-2">
                        <div class="input-group bg-white rounded-pill shadow-sm overflow-hidden border">
                            <span class="input-group-text border-0 bg-transparent"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-0" placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   onchange="window.location.href='?search=' + encodeURIComponent(this.value)">
                        </div>
                    </div>
                </div>
            </div>
            
            <?php 
            $currentCategory = null;
            if ($categoryId > 0) {
                foreach ($categories as $c) {
                    if ($c['id'] == $categoryId) {
                        $currentCategory = $c;
                        break;
                    }
                }
            }
            $catDefaultDescriptions = [
                'coffee-mugs' => "Your morning coffee just got a glow-up. Whether you're a minimalist or a maximalist, our premium mugs are designed to match your mood. Grab your favorite and start the day with a smile.",
                'fridge-magnets' => "Small items, big personality. Turn your fridge into a mood board with our vibrant, high-quality magnets. They’re the perfect little treat for yourself or a friend.",
                'coasters' => "Protect your desk, but make it fashionable. Our coasters add a pop of color to your space while keeping things clean. Functional art for your home or office.",
                'tshirts' => "Coming soon"
            ];
            $catSlug = $currentCategory['slug'] ?? '';
            $catDesc = ($currentCategory && !empty($currentCategory['description']) && strlen($currentCategory['description']) > 20)
                ? $currentCategory['description']
                : ($catDefaultDescriptions[$catSlug] ?? '');
            ?>
            
            <div class="card border-0 shadow-sm p-4 mb-4 reveal-up" style="border-radius: 20px; background: var(--card-bg);">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($currentCategory && !empty($currentCategory['icon'])): ?>
                            <div class="d-flex align-items-center justify-content-center text-primary rounded-circle" 
                                 style="width: 55px; height: 55px; background: rgba(var(--primary-rgb), 0.1); flex-shrink: 0;">
                                <i class="fas <?php echo htmlspecialchars($currentCategory['icon']); ?> fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h4 class="fw-bold mb-1">
                                <?php echo $currentCategory ? htmlspecialchars($currentCategory['name']) : 'All Collections'; ?>
                                <?php if ($currentCategory && ($catSlug === 'tshirts' || stripos($currentCategory['name'], 't-shirt') !== false)): ?>
                                    <span class="badge bg-dark rounded-pill ms-2 fs-6">COMING SOON</span>
                                <?php endif; ?>
                            </h4>
                            <p class="text-muted mb-0 small" style="max-width: 600px;">
                                <?php 
                                if ($catDesc) {
                                    echo htmlspecialchars($catDesc);
                                } else {
                                    echo 'Gifts That Tell Your Story. Make it Personal. Make it Drabya.';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill px-3 py-2 align-self-start align-self-md-center">
                        <?php echo count($products); ?> <?php echo count($products) === 1 ? 'item' : 'items'; ?> found
                    </span>
                </div>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="text-center py-5 reveal-up">
                    <div class="mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: rgba(var(--primary-rgb), 0.05); border-radius: 50%;">
                        <i class="fas fa-search fa-4x text-muted opacity-50"></i>
                    </div>
                    <h5 class="fw-bold">No results found</h5>
                    <p class="text-muted">We couldn't find anything matching your filters.</p>
                    <a href="products.php" class="btn btn-primary rounded-pill px-4 mt-2">Clear all filters</a>
                </div>
            <?php else: ?>
                <div class="row g-3 g-md-4">
                    <?php foreach ($products as $product): ?>
                        <div class="col-6 col-md-4 col-lg-3 reveal-up" style="transition-delay: <?php echo 0.05 * ($product['id'] % 10); ?>s;">
                            <div class="card product-card h-100 border-0 shadow-sm">
                                <div class="position-relative overflow-hidden">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="d-block">
                                        <?php if ($product['image_path']): ?>
                                            <img src="<?php echo getProductImageUrl($product['image_path']); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>"
                                                 style="transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                                                <i class="fas fa-image fa-3x text-muted opacity-50"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                    <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                        <span class="position-absolute top-0 start-0 m-2 badge rounded-pill bg-danger shadow">
                                            -<?php echo calculateDiscountPercentage($product['price'], $product['discount_price']); ?>%
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock_quantity'] <= 0): ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge rounded-pill bg-dark shadow-sm">SOLD OUT</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-3 d-flex flex-column">
                                    <p class="text-muted small mb-1"><?php echo htmlspecialchars($product['category_name'] ?? 'Merchandise'); ?></p>
                                    <h6 class="card-title fw-bold mb-3 small">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-main stretched-link">
                                            <?php echo displayTitle($product['title']); ?>
                                        </a>
                                    </h6>
                                    
                                    <div class="d-flex align-items-center justify-content-between mt-auto" style="position: relative; z-index: 2;">
                                        <div>
                                            <?php if (!empty($product['discount_price']) && $product['discount_price'] < $product['price']): ?>
                                                <span class="text-primary fw-bold" style="font-size: 1rem;"><?php echo formatCurrency($product['discount_price']); ?></span>
                                                <span class="text-muted text-decoration-line-through d-block px-1" style="font-size: 0.7rem;">
                                                    <?php echo formatCurrency($product['price']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-primary fw-bold" style="font-size: 1rem;"><?php echo formatCurrency($product['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($product['stock_quantity'] > 0): ?>
                                            <button onclick="event.preventDefault(); addToCart(<?php echo $product['id']; ?>)" 
                                                    class="btn btn-primary btn-sm p-2 rounded-circle shadow-sm"
                                                    style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;"
                                                    title="Add to Cart">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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
            // Modern toast notification logic
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-5 px-4 py-3 bg-dark text-white rounded-pill shadow-lg reveal-up active';
            toast.style.zIndex = '9999';
            toast.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i> Added to cart!</div>';
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('active');
                setTimeout(() => toast.remove(), 600);
            }, 3000);
            
            // Update cart count badge
            const countBadge = document.querySelector('.badge-notification');
            if (countBadge) {
                countBadge.innerText = parseInt(countBadge.innerText) + 1;
            } else {
                window.location.reload(); 
            }
        } else {
            alert(data.message || 'Error adding to cart');
        }
    });
}

// Reuse reveal animation observer
const observerOptions = { threshold: 0.1 };
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
