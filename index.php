<?php 
session_start();

// 1. Point to your central database file
// Use the path relative to where index.php is located
include 'backend/db.php'; 

try {
    // 2. Fetch all products (pdo is defined inside db.php)
    $stmt = $pdo->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the database fails, we log it and show an empty list so the site doesn't crash
    error_log($e->getMessage());
    $products = []; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="utf-8"/>
    <title>J.A Farm & Market</title>
    <link rel="stylesheet" href="globals.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main class="frame">
    <header>
        <div class="j-a-FARM">J.A Farm & Market</div>
        <nav aria-label="Primary">
            <a href="#contact" class="text-wrapper-3">Contact</a>
            <a href="#about" class="text-wrapper">About</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-dropdown">
                    <span class="text-wrapper dropbtn">
                        Hi, <?php echo htmlspecialchars($_SESSION['username']); ?> ▾
                    </span>
                    <div class="dropdown-content">
                        <a href="profile.php">My Profile</a>
                        <a href="profile.php?page=cart">My Cart (<span id="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>)</a>
                        <hr>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="javascript:void(0)" id="openModal" class="rectangle text-wrapper-2">Sign in</a>
            <?php endif; ?>
        </nav>
    </header>

    <?php if(!isset($_SESSION['user_id'])): ?>
    <section aria-labelledby="hero-title">
        <h1 class="healthy-fresh" id="hero-title">Grown with Care,</h1>
        <p class="text-wrapper-4">Delivered with Freshness.</p>
    </section>
<?php else: ?>
    <div class="category-filter" style="margin: 40px 5%; text-align: center;">
        <label for="cat-dropdown" style="display: block; margin-bottom: 12px; font-weight: 600; color: #4a5568;">
            Select Category
        </label>
        
        <select id="cat-dropdown" onchange="location = this.value;">
            <option value="index.php" <?= !isset($_GET['cat']) ? 'selected' : '' ?>>
                All Categories
            </option>

            <?php
            // 1. Fetch Categories for the dropdown options
            $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
            foreach($categories as $cat): 
                $isSelected = (isset($_GET['cat']) && $_GET['cat'] == $cat['id']) ? 'selected' : '';
            ?>
                <option value="index.php?cat=<?= $cat['id'] ?>#products" <?= $isSelected ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <style>
        /* Modernized Dropdown Styling */
        #cat-dropdown {
            padding: 12px 32px;
            border-radius: 30px;
            border: 1px solid #e2e8f0;
            background-color: #fff;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            outline: none;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: all 0.25s ease;
        }
        
        #cat-dropdown:focus, #cat-dropdown:hover {
            border-color: var(--primary-green);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        }

        @media (max-width: 600px) {
            #cat-dropdown {
                width: 90%;
            }
        }
    </style>

    <?php 
    // 2. Filter Products logic
    $selected_cat = isset($_GET['cat']) && is_numeric($_GET['cat']) ? (int)$_GET['cat'] : null;

    if ($selected_cat) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? ORDER BY id DESC");
        $stmt->execute([$selected_cat]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    }

    $products = $stmt->fetchAll();
    ?>

    <section class="products-grid" id="products">
        <h2 class="section-title">
            <?php echo $selected_cat ? "Category Results" : "Our Fresh Products"; ?>
        </h2>
        
        <div class="grid-container" id="product-container">
            <?php if (empty($products)): ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 60px; color: #718096; background: #fff; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                    No products found in this category.
                </p>
            <?php else: ?>
                <?php 
                $initial_display = array_slice($products, 0, 6); 
                foreach ($initial_display as $product): 
                    // Calculate stock and determine if sold out
                    $is_out_of_stock = ($product['stock'] <= 0);
                ?>
                    <div class="product-card">
                        <div class="image-wrapper">
                            <img src="<?= htmlspecialchars($product['image_path']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']); ?></h3>
                            <div class="price-stock">
                                <span class="price">₱<?= number_format($product['price'], 2); ?> / kg</span>
                                <span class="stock-badge" style="color: <?= $is_out_of_stock ? '#e74c3c' : '#2ecc71'; ?>">
                                    <?= $is_out_of_stock ? "Sold Out" : $product['stock'] . " left"; ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($is_out_of_stock): ?>
                            <button class="rectangle" style="background: #cbd5e1; cursor: not-allowed; width: 100%; border: none; padding: 14px; border-radius: 10px; color: #64748b; font-weight: 600;" disabled>
                                Out of Stock
                            </button>
                        <?php else: ?>
                            <button class="add-to-cart-btn rectangle" data-id="<?= $product['id']; ?>" style="width: 100%; border: none; padding: 14px; border-radius: 10px; background: var(--primary-green); color: #fff; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                                Add to Cart
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (count($products) > 6): ?>
            <div style="text-align: center; margin-top: 50px;">
                <button id="loadMoreBtn" class="rectangle" data-offset="6" style="padding: 14px 36px; border-radius: 30px; border: 1px solid var(--primary-green); background: transparent; color: var(--primary-green); font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    Load More
                </button>
            </div>
        <?php endif; ?>
    </section>

    <style>
        /* Enhanced product card layout and micro-interactions */
        .products-grid {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }

        .product-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
            border-color: var(--primary-green);
        }

        .product-card .image-wrapper {
            background: #f8fafc;
            border-radius: 16px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 12px;
        }

        .product-info h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 12px;
            color: #1e293b;
        }

        .price-stock {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .price-stock .price {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-dark, #16a085);
        }

        .stock-badge {
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        #loadMoreBtn:hover {
            background: var(--primary-green);
            color: #fff;
        }
    </style>
<?php endif; ?>

<section id="about" style="padding: 100px 5%; background: #fff;">
    <div style="display: flex; align-items: center; gap: 50px; max-width: 1200px; margin: 0 auto; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
            <img src="assets/about-fruits.jpg" alt="Fresh Fruits" style="width: 100%; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
        </div>
        <div style="flex: 1; min-width: 300px;">
            <h4 style="color: var(--primary-green); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;">Our Story</h4>
            <h2 style="font-size: 36px; margin-bottom: 20px;">Freshness From Our Farm to Your Table</h2>
            <p style="color: #666; line-height: 1.8; margin-bottom: 20px;">
                At <strong>J.A Farm & Market</strong>, we believe that nature provides the best nutrition. Founded in Pagadian City, our mission is to provide the community with the highest quality, locally-sourced, and imported farm products.
            </p>
            <p style="color: #666; line-height: 1.8;">
                Every piece of products in our shop is hand-picked and inspected for quality. Whether you are looking for tropical delights or seasonal favorites, we ensure that you get the peak of freshness in every bite.
            </p>
        </div>
    </div>
</section>

<section id="contact" style="padding: 100px 5%; background: #f9f9f9;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h2 style="font-size: 32px;">Get In Touch</h2>
            <p style="color: #888;">Have questions about our deliveries or bulk orders? Message us!</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
            <div class="glass-card" style="padding: 40px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
                <div style="margin-bottom: 30px;">
                    <h3 style="color: var(--primary-green); margin-bottom: 10px;">Our Office</h3>
                    <p style="color: #666;">123 Rizal Avenue, Pagadian City,<br>Zamboanga del Sur, Philippines</p>
                </div>
                <div style="margin-bottom: 30px;">
                    <h3 style="color: var(--primary-green); margin-bottom: 10px;">Contact Details</h3>
                    <p style="color: #666;">Phone: +63 912 345 6789</p>
                    <p style="color: #666;">Email: hello@jafruits.com</p>
                </div>
                <div>
                    <h3 style="color: var(--primary-green); margin-bottom: 10px;">Working Hours</h3>
                    <p style="color: #666;">Mon - Sat: 8:00 AM - 6:00 PM</p>
                    <p style="color: #666;">Sunday: Closed</p>
                </div>
            </div>

            <form action="backend/process_contact.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" name="name" placeholder="Your Name" required style="padding: 15px; border-radius: 10px; border: 1px solid #ddd; outline: none;">
                <input type="email" name="email" placeholder="Your Email" required style="padding: 15px; border-radius: 10px; border: 1px solid #ddd; outline: none;">
                <textarea name="message" placeholder="How can we help you?" rows="5" required style="padding: 15px; border-radius: 10px; border: 1px solid #ddd; outline: none; resize: none;"></textarea>
                <button type="submit" class="rectangle auth-btn" style="width: 100%; border: none; cursor: pointer;">Send Message</button>
            </form>
        </div>
    </div>
</section>
</main>

<div id="signinModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div class="tab-header">
            <button class="tab-btn active" onclick="openTab(event, 'loginForm')">Sign In</button>
            <button class="tab-btn" onclick="openTab(event, 'signupForm')">Sign Up</button>
        </div>

        <form id="loginForm" class="tab-content show" action="backend/auth.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="rectangle auth-btn">Login</button>
        </form>

        <form id="signupForm" class="tab-content" action="backend/auth.php" method="POST">
            <input type="hidden" name="action" value="register">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="fullname" required>
            </div>
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Phone</label>
                <input type="text" name="phone" required>
            </div>
            <button type="submit" class="rectangle auth-btn">Create Account</button>
        </form>
    </div>
</div>

<script>
  const modal = document.getElementById("signinModal");
  const signinBtn = document.getElementById("openModal"); // Targeted by ID
  const closeBtn = document.querySelector(".close");

  if (signinBtn) {
      signinBtn.onclick = (e) => {
          e.preventDefault();
          modal.style.display = "block";
      }
  }

  closeBtn.onclick = () => modal.style.display = "none";

  window.onclick = (event) => {
      if (event.target == modal) modal.style.display = "none";
  }

  function openTab(evt, tabName) {
      const contents = document.querySelectorAll(".tab-content");
      contents.forEach(content => content.classList.remove("show"));
      const buttons = document.querySelectorAll(".tab-btn");
      buttons.forEach(btn => btn.classList.remove("active"));
      document.getElementById(tabName).classList.add("show");
      evt.currentTarget.classList.add("active");
  }
document.getElementById('loadMoreBtn')?.addEventListener('click', function() {
    const btn = this;
    const offset = parseInt(btn.getAttribute('data-offset'));
    const container = document.getElementById('product-container');

    // NEW: Get the current category ID from the URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentCat = urlParams.get('cat') || ''; // Returns empty string if no category

    btn.innerText = "Loading...";
    btn.disabled = true;

    // UPDATE: Include the cat parameter in the fetch URL
    fetch(`backend/fetch_products.php?offset=${offset}&cat=${currentCat}`)
        .then(response => response.text())
        .then(html => {
            if (html.trim() === "") {
                btn.innerText = "No more products";
                btn.style.display = "none";
            } else {
                container.insertAdjacentHTML('beforeend', html);
                btn.setAttribute('data-offset', offset + 6);
                btn.innerText = "Load More";
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error("Error fetching products:", err);
            btn.innerText = "Try Again";
            btn.disabled = false;
        });
});

  // USE THIS VERSION - It works for original AND newly loaded products
  document.addEventListener('click', function(e) {
      // 1. Check if the element clicked (or its parent) has the 'add-to-cart-btn' class
      const btn = e.target.closest('.add-to-cart-btn');
      
      if (btn) {
          // Prevent default behavior if it's a link
          e.preventDefault();

          const productId = btn.getAttribute('data-id');

          // Visual feedback
          const originalText = btn.innerText;
          btn.innerText = "Adding...";
          btn.disabled = true;

          const formData = new FormData();
          formData.append('product_id', productId);

          fetch('backend/add_to_cart.php', {
              method: 'POST',
              body: formData
          })
          .then(response => response.text())
          .then(cartCount => {
              const countDisplay = document.getElementById('cart-count');
              if (countDisplay) countDisplay.innerText = cartCount;
              
              btn.innerText = "Added! ✓";
              btn.style.backgroundColor = "#2ecc71";
              
              setTimeout(() => {
                  btn.innerText = "Add to Cart";
                  btn.style.backgroundColor = "";
                  btn.disabled = false;
              }, 1500);
          })
          .catch(err => {
              console.error("Cart Error:", err);
              btn.innerText = "Error!";
              btn.disabled = false;
          });
      }
  });
</script>
</body>
</html>