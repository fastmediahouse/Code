add_shortcode('fastmedia_price_calculator', function () {
    ob_start();
    ?>
    <style>
        .fm-price-calc-wrapper {
            width: 100%;
            margin: 0 auto;
            padding: 60px 20px;
            background: #fafafa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
        }
        
        .fm-price-calc-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            min-height: 100vh;
        }
        
        /* Header Section */
        .fm-price-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .fm-price-header h1 {
            font-size: 48px;
            font-weight: 700;
            color: #000;
            margin: 0 0 20px 0;
            letter-spacing: -1px;
        }
        
        .fm-price-header p {
            font-size: 20px;
            color: #666;
            margin: 0;
            font-weight: 400;
        }
        
        /* Main Content Grid */
        .fm-price-content {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-bottom: 60px;
            align-items: stretch;
        }
        
        @media (max-width: 1024px) {
            .fm-price-content {
                grid-template-columns: 1fr;
            }
        }
        
        /* Card Styles */
        .fm-price-card {
            background: white;
            border-radius: 4px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            min-height: 250px;
            display: flex;
            flex-direction: column;
        }
        
        .fm-price-card::after {
            content: 'Click to select';
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 11px;
            color: #333;
            background: #e0e0e0;
            padding: 4px 12px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0;
            transition: opacity 0.3s ease;
            font-weight: 500;
        }
        
        .fm-price-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
            border-color: #e0e0e0;
        }
        
        .fm-price-card:hover::after {
            opacity: 1;
        }
        
        .fm-price-card.active {
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            background: #f8f9fa;
            color: #333;
            border: 2px solid #28a745;
        }
        
        .fm-price-card.active::after {
            content: 'Selected ✓';
            color: white;
            background: #28a745;
            opacity: 1;
        }
        
        .fm-price-card.active .fm-card-title,
        .fm-price-card.active .fm-card-icon {
            color: #28a745;
        }
        
        .fm-card-icon {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }
        
        .fm-card-title {
            font-size: 24px;
            font-weight: 600;
            color: #000;
            margin: 0 0 12px 0;
        }
        
        .fm-card-subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
            line-height: 1.6;
        }
        
        .fm-price-card.active .fm-card-subtitle {
            color: #666;
        }
        
        /* Usage Options */
        .fm-usage-grid {
            display: none;
            margin-top: 30px;
            gap: 15px;
        }
        
        .fm-usage-grid.active {
            display: grid;
        }
        
        .fm-usage-option {
            padding: 20px;
            background: #f8f8f8;
            border-radius: 4px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .fm-price-card.active .fm-usage-option {
            background: white;
            color: #333;
        }
        
        .fm-usage-option:hover {
            border-color: #28a745;
            background: #f0f0f0;
        }
        
        .fm-price-card.active .fm-usage-option:hover {
            border-color: #28a745;
            background: #f9f9f9;
        }
        
        .fm-usage-option.selected {
            border-color: #28a745;
            background: #28a745;
            color: white;
        }
        
        .fm-price-card.active .fm-usage-option.selected {
            background: #28a745;
            color: white;
        }
        
        .fm-usage-name {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .fm-usage-details {
            font-size: 12px;
            opacity: 0.8;
        }
        
        /* Size Selector */
        .fm-size-selector {
            background: white;
            border-radius: 4px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            grid-column: 1 / -1;
        }
        
        .fm-size-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 20px 0;
        }
        
        .fm-size-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .fm-size-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .fm-size-option {
            padding: 30px;
            background: #f8f8f8;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 2px solid transparent;
        }
        
        .fm-size-option:hover {
            border-color: #28a745;
            background: #f0f0f0;
        }
        
        .fm-size-option.selected {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .fm-size-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }
        
        .fm-size-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .fm-size-desc {
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.4;
        }
        
        /* Price Summary */
        .fm-price-summary {
            background: #28a745;
            color: white;
            border-radius: 4px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
            z-index: 100;
            min-height: 250px;
            display: flex;
            flex-direction: column;
        }
        
        .fm-price-summary:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        
        .fm-price-summary:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        
        .fm-summary-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
            margin-bottom: 15px;
        }
        
        .fm-price-display {
            font-size: 48px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .fm-price-period {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 20px;
        }
        
        .fm-selection-summary {
            padding: 15px;
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
            display: none;
        }
        
        .fm-selection-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        
        .fm-selection-label {
            opacity: 0.8;
        }
        
        .fm-selection-value {
            font-weight: 600;
        }
        
        .fm-action-buttons {
            display: grid;
            gap: 10px;
        }
        
        .fm-btn {
            width: 100%;
            padding: 14px 20px;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .fm-btn-primary {
            background: white;
            color: #28a745;
        }
        
        .fm-btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,255,255,0.2);
        }
        
        .fm-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid rgba(255,255,255,0.5);
        }
        
        .fm-btn-secondary:hover:not(:disabled) {
            border-color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .fm-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        
        /* Info Section */
        .fm-info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }
        
        .fm-info-card {
            background: white;
            border-radius: 4px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .fm-info-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .fm-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .fm-info-list li {
            padding: 10px 0;
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        
        /* Loading and Success States */
        .fm-loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: fm-spin 0.8s linear infinite;
        }
        
        @keyframes fm-spin {
            to { transform: rotate(360deg); }
        }
        
        .fm-toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 20px 30px;
            background: #28a745;
            color: white;
            border-radius: 4px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 9999;
        }
        
        .fm-toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>

    <div class="fm-price-calc-wrapper">
        <div class="fm-price-calc-container">
            <!-- Header -->
            <div class="fm-price-header">
                <h1>Simple, Transparent Pricing</h1>
                <p>Select your usage type and size. Get instant pricing.</p>
            </div>
            
            <!-- Main Content -->
            <div class="fm-price-content">
                <!-- Editorial Card -->
                <div class="fm-price-card" id="fm-editorial-card" onclick="fmSelectType('editorial')">
                    <span class="fm-card-icon">📰</span>
                    <h3 class="fm-card-title">Editorial</h3>
                    <p class="fm-card-subtitle">News, education, and non-commercial content</p>
                    
                    <div class="fm-usage-grid" id="fm-editorial-options">
                        <div class="fm-usage-option" data-value="books" onclick="fmSelectUsage(event, 'editorial', 'books')">
                            <div class="fm-usage-name">📚 Books</div>
                            <div class="fm-usage-details">Max 25,000 copies • 5 years</div>
                        </div>
                        <div class="fm-usage-option" data-value="magazines" onclick="fmSelectUsage(event, 'editorial', 'magazines')">
                            <div class="fm-usage-name">📖 Magazines</div>
                            <div class="fm-usage-details">Max 50,000 copies • 1 year</div>
                        </div>
                        <div class="fm-usage-option" data-value="newspapers" onclick="fmSelectUsage(event, 'editorial', 'newspapers')">
                            <div class="fm-usage-name">📰 Newspapers</div>
                            <div class="fm-usage-details">Max 100,000 copies • 1 year</div>
                        </div>
                        <div class="fm-usage-option" data-value="online" onclick="fmSelectUsage(event, 'editorial', 'online')">
                            <div class="fm-usage-name">💻 Online</div>
                            <div class="fm-usage-details">Web & social media • 1 year</div>
                        </div>
                        <div class="fm-usage-option" data-value="broadcast" onclick="fmSelectUsage(event, 'editorial', 'broadcast')">
                            <div class="fm-usage-name">📺 Broadcast</div>
                            <div class="fm-usage-details">TV & streaming • 1 year</div>
                        </div>
                    </div>
                </div>
                
                <!-- Commercial Card -->
                <div class="fm-price-card" id="fm-commercial-card" onclick="fmSelectType('commercial')">
                    <span class="fm-card-icon">💼</span>
                    <h3 class="fm-card-title">Commercial</h3>
                    <p class="fm-card-subtitle">Advertising, marketing, and business use</p>
                    
                    <div class="fm-usage-grid" id="fm-commercial-options">
                        <div class="fm-usage-option" data-value="commercial_print" onclick="fmSelectUsage(event, 'commercial', 'commercial_print')">
                            <div class="fm-usage-name">📄 Commercial Print</div>
                            <div class="fm-usage-details">Advertising, Outdoor, Brochures, PR • 1 year</div>
                        </div>
                        <div class="fm-usage-option" data-value="online" onclick="fmSelectUsage(event, 'commercial', 'online')">
                            <div class="fm-usage-name">💻 Online</div>
                            <div class="fm-usage-details">Web & social media usage • 1 year</div>
                        </div>
                        <div class="fm-usage-option" data-value="tv" onclick="fmSelectUsage(event, 'commercial', 'tv')">
                            <div class="fm-usage-name">📺 TV/Broadcast</div>
                            <div class="fm-usage-details">For TV or video content • 1 year</div>
                        </div>
                    </div>
                </div>
                
                <!-- Price Summary -->
                <div class="fm-price-summary">
                    <div class="fm-summary-title">Your License</div>
                    <div class="fm-price-display" id="fm-price-amount">€0</div>
                    <div class="fm-price-period">Valid for 3 years</div>
                    
                    <div class="fm-selection-summary" id="fm-selection-summary">
                        <div class="fm-selection-item">
                            <span class="fm-selection-label">Type:</span>
                            <span class="fm-selection-value" id="fm-selected-type">-</span>
                        </div>
                        <div class="fm-selection-item">
                            <span class="fm-selection-label">Usage:</span>
                            <span class="fm-selection-value" id="fm-selected-usage">-</span>
                        </div>
                        <div class="fm-selection-item">
                            <span class="fm-selection-label">Size:</span>
                            <span class="fm-selection-value" id="fm-selected-size">-</span>
                        </div>
                    </div>
                    
                    <div class="fm-action-buttons">
                        <button class="fm-btn fm-btn-primary" id="fm-buy-now" onclick="fmBuyNow()" disabled>
                            Buy Now →
                        </button>
                        <button class="fm-btn fm-btn-secondary" id="fm-add-to-cart" onclick="fmAddToCart()" disabled>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Size Selector -->
            <div class="fm-size-selector" id="fm-size-selector" style="display: none;">
                <h3 class="fm-size-title">Select Image Size</h3>
                <div class="fm-size-grid">
                    <div class="fm-size-option" data-value="small" onclick="fmSelectSize('small')">
                        <span class="fm-size-icon">🔸</span>
                        <div class="fm-size-name">Small</div>
                        <div class="fm-size-desc">Up to 1/4 page<br>~800px<br>Web thumbnails</div>
                    </div>
                    <div class="fm-size-option" data-value="medium" onclick="fmSelectSize('medium')">
                        <span class="fm-size-icon">🔶</span>
                        <div class="fm-size-name">Medium</div>
                        <div class="fm-size-desc">Up to 1/2 page<br>~1500px<br>Half-page prints</div>
                    </div>
                    <div class="fm-size-option" data-value="large" onclick="fmSelectSize('large')">
                        <span class="fm-size-icon">🔷</span>
                        <div class="fm-size-name">Large</div>
                        <div class="fm-size-desc">Full page/cover<br>2000px+<br>Posters & covers</div>
                    </div>
                </div>
            </div>
            
            <!-- Info Section -->
            <div class="fm-info-section">
                <div class="fm-info-card">
                    <h3 class="fm-info-title">⚠️ Special Cases</h3>
                    <ul class="fm-info-list">
                        <li>🛍️ Retail products (puzzles, packaging, textiles)</li>
                        <li>🖼️ Outdoor formats over 3m²</li>
                        <li>📺 TV/broadcast or cinema trailers</li>
                        <li>📰 Circulation over 100,000 copies</li>
                        <li>🧾 Multi-use, syndication, or re-use</li>
                    </ul>
                </div>
                
                <div class="fm-info-card">
                    <h3 class="fm-info-title">💡 License Terms</h3>
                    <ul class="fm-info-list">
                        <li>✓ Prices include 3 years of usage</li>
                        <li>✓ Renewal available at +50%</li>
                        <li>✓ Per image, per use licensing</li>
                        <li>✓ Usage limits enforced by terms</li>
                        <li>✓ Size = output use, not file size</li>
                    </ul>
                </div>
                
                <div class="fm-info-card">
                    <h3 class="fm-info-title">🚀 How It Works</h3>
                    <ul class="fm-info-list">
                        <li>1. Choose Editorial or Commercial</li>
                        <li>2. Select your specific usage</li>
                        <li>3. Pick your output size</li>
                        <li>4. Get instant pricing</li>
                        <li>5. Add to cart or buy now</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Notification -->
    <div class="fm-toast" id="fm-toast">
        <span id="fm-toast-message">Added to cart!</span>
    </div>

    <script>
        let fmState = {
            type: null,
            usage: null,
            size: null,
            basePrice: 0,
            finalPrice: 0
        };
        
        // Exact pricing table
        const fmPricing = {
            editorial: {
                books: { small: 35, medium: 60, large: 100 },
                magazines: { small: 25, medium: 50, large: 75 },
                newspapers: { small: 20, medium: 40, large: 60 },
                online: { small: 15, medium: 30, large: 50 },
                broadcast: { small: 50, medium: 100, large: 150 }
            },
            commercial: {
                commercial_print: { small: 50, medium: 100, large: 180 },
                online: { small: 50, medium: 100, large: 180 },
                tv: { small: 100, medium: 200, large: 300 }
            }
        };
        
        function fmSelectType(type) {
            // Reset previous selection
            document.querySelectorAll('.fm-price-card').forEach(card => {
                card.classList.remove('active');
            });
            document.querySelectorAll('.fm-usage-grid').forEach(grid => {
                grid.classList.remove('active');
            });
            document.querySelectorAll('.fm-usage-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Set new selection
            document.getElementById('fm-' + type + '-card').classList.add('active');
            document.getElementById('fm-' + type + '-options').classList.add('active');
            
            // Update state
            fmState.type = type;
            fmState.usage = null;
            fmState.size = null;
            
            // Hide size selector until usage is selected
            document.getElementById('fm-size-selector').style.display = 'none';
            
            fmUpdateDisplay();
        }
        
        function fmSelectUsage(event, type, usage) {
            event.stopPropagation();
            
            // Reset previous selection
            document.querySelectorAll('#fm-' + type + '-options .fm-usage-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Set new selection
            const option = event.currentTarget;
            option.classList.add('selected');
            
            // Update state
            fmState.usage = usage;
            
            // Show size selector
            document.getElementById('fm-size-selector').style.display = 'block';
            document.getElementById('fm-size-selector').scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            fmUpdateDisplay();
        }
        
        function fmSelectSize(size) {
            // Reset previous selection
            document.querySelectorAll('.fm-size-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Set new selection
            const option = document.querySelector('.fm-size-option[data-value="' + size + '"]');
            option.classList.add('selected');
            
            // Update state
            fmState.size = size;
            
            // Get price from pricing table
            if (fmState.type && fmState.usage && fmState.size) {
                fmState.finalPrice = fmPricing[fmState.type][fmState.usage][fmState.size];
            }
            
            fmUpdateDisplay();
        }
        
        function fmUpdateDisplay() {
            const priceElement = document.getElementById('fm-price-amount');
            const summaryElement = document.getElementById('fm-selection-summary');
            const buyButton = document.getElementById('fm-buy-now');
            const cartButton = document.getElementById('fm-add-to-cart');
            
            if (fmState.type && fmState.usage && fmState.size) {
                // Update price
                priceElement.textContent = '€' + fmState.finalPrice.toFixed(2);
                
                // Update summary
                summaryElement.style.display = 'block';
                document.getElementById('fm-selected-type').textContent = fmState.type.charAt(0).toUpperCase() + fmState.type.slice(1);
                document.getElementById('fm-selected-usage').textContent = fmState.usage.charAt(0).toUpperCase() + fmState.usage.slice(1);
                document.getElementById('fm-selected-size').textContent = fmState.size.charAt(0).toUpperCase() + fmState.size.slice(1);
                
                // Enable buttons
                buyButton.disabled = false;
                cartButton.disabled = false;
            } else {
                priceElement.textContent = '€0';
                summaryElement.style.display = 'none';
                buyButton.disabled = true;
                cartButton.disabled = true;
            }
        }
        
        function fmShowToast(message) {
            const toast = document.getElementById('fm-toast');
            document.getElementById('fm-toast-message').textContent = message;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        
        function fmAddToCart() {
            if (!fmState.type || !fmState.usage || !fmState.size || fmState.finalPrice <= 0) return;
            
            // Get image ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            const imageId = urlParams.get('productID') || urlParams.get('id') || '';
            
            // Build URL like the legacy button
            const cartUrl = new URL('<?php echo wc_get_cart_url(); ?>');
            cartUrl.searchParams.set('add-to-cart', '7447');
            cartUrl.searchParams.set('solwee_price', fmState.finalPrice);
            cartUrl.searchParams.set('solwee_imageID', imageId);
            cartUrl.searchParams.set('category', fmState.type.charAt(0).toUpperCase() + fmState.type.slice(1));
            cartUrl.searchParams.set('usage', fmState.usage.charAt(0).toUpperCase() + fmState.usage.slice(1));
            cartUrl.searchParams.set('size', fmState.size.charAt(0).toUpperCase() + fmState.size.slice(1));
            cartUrl.searchParams.set('duration', '3 years');
            
            // Optional: Add default values for compatibility
            cartUrl.searchParams.set('solwee_usageID', '');
            cartUrl.searchParams.set('solwee_magazineID', '');
            cartUrl.searchParams.set('region', '');
            cartUrl.searchParams.set('modifiers', '');
            
            // Redirect to cart with parameters
            window.location.href = cartUrl.toString();
        }
        
        function fmBuyNow() {
            if (!fmState.type || !fmState.usage || !fmState.size || fmState.finalPrice <= 0) return;
            
            // Get image ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            const imageId = urlParams.get('productID') || urlParams.get('id') || '';
            
            // Build checkout URL like the legacy button
            const checkoutUrl = new URL('<?php echo wc_get_checkout_url(); ?>');
            const cartUrl = new URL('<?php echo wc_get_cart_url(); ?>');
            
            // First add to cart with parameters
            cartUrl.searchParams.set('add-to-cart', '7447');
            cartUrl.searchParams.set('solwee_price', fmState.finalPrice);
            cartUrl.searchParams.set('solwee_imageID', imageId);
            cartUrl.searchParams.set('category', fmState.type.charAt(0).toUpperCase() + fmState.type.slice(1));
            cartUrl.searchParams.set('usage', fmState.usage.charAt(0).toUpperCase() + fmState.usage.slice(1));
            cartUrl.searchParams.set('size', fmState.size.charAt(0).toUpperCase() + fmState.size.slice(1));
            cartUrl.searchParams.set('duration', '3 years');
            
            // Add redirect to checkout
            cartUrl.searchParams.set('redirect_to_checkout', '1');
            
            // Redirect
            window.location.href = cartUrl.toString();
        }
    </script>
    <?php
    return ob_get_clean();
});

// Process cart data from URL parameters (like legacy button)
add_filter('woocommerce_add_cart_item_data', 'fm_add_cart_item_data_from_url', 10, 3);
function fm_add_cart_item_data_from_url($cart_item_data, $product_id, $variation_id) {
    if ($product_id == 7447 && isset($_GET['solwee_price'])) {
        // Get all parameters from URL
        $cart_item_data['custom_price'] = floatval($_GET['solwee_price']);
        $cart_item_data['solwee_license'] = array(
            'solwee_imageID' => sanitize_text_field($_GET['solwee_imageID'] ?? ''),
            'solwee_usageID' => sanitize_text_field($_GET['solwee_usageID'] ?? ''),
            'solwee_magazineID' => sanitize_text_field($_GET['solwee_magazineID'] ?? ''),
            'category' => sanitize_text_field($_GET['category'] ?? ''),
            'usage' => sanitize_text_field($_GET['usage'] ?? ''),
            'duration' => sanitize_text_field($_GET['duration'] ?? '3 years'),
            'region' => sanitize_text_field($_GET['region'] ?? ''),
            'modifiers' => sanitize_text_field($_GET['modifiers'] ?? ''),
            'size' => sanitize_text_field($_GET['size'] ?? ''),
            'price' => floatval($_GET['solwee_price'] ?? 0)
        );
    }
    return $cart_item_data;
}

// Modify cart item price
add_action('woocommerce_before_calculate_totals', 'fm_set_custom_price', 99);
function fm_set_custom_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }
    
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['custom_price'])) {
            $cart_item['data']->set_price($cart_item['custom_price']);
        }
    }
}

// Display custom product name in cart
add_filter('woocommerce_cart_item_name', 'fm_custom_cart_item_name', 10, 3);
function fm_custom_cart_item_name($product_name, $cart_item, $cart_item_key) {
    // For checkout page, handle thumbnail display
    if (is_checkout() && !empty($cart_item['solwee_license']['solwee_imageID'])) {
        $productID = sanitize_text_field($cart_item['solwee_license']['solwee_imageID']);
        $proxy = esc_url('https://fastmediahouse.com/?solwee_image_proxy=' . $productID);
        $image_html = '<div><img src="' . $proxy . '" alt="Preview" style="width: 60px; height: auto; border-radius: 6px; margin: 6px 0;"></div>';
        return $image_html . $product_name;
    }
    
    // Create custom name if we have license data
    if (!empty($cart_item['solwee_license']['solwee_imageID'])) {
        $license = $cart_item['solwee_license'];
        return sprintf(
            'Image #%s - %s %s (%s)',
            $license['solwee_imageID'],
            $license['category'],
            $license['usage'],
            $license['size'] ?? ''
        );
    }
    
    return $product_name;
}

// Save license data to order
add_action('woocommerce_checkout_create_order_line_item', 'fm_save_license_data_to_order', 10, 4);
function fm_save_license_data_to_order($item, $cart_item_key, $values, $order) {
    if (isset($values['solwee_license'])) {
        $item->add_meta_data('solwee_license', $values['solwee_license']);
    }
}

// Handle redirect to checkout if requested
add_action('template_redirect', 'fm_handle_checkout_redirect');
function fm_handle_checkout_redirect() {
    if (isset($_GET['redirect_to_checkout']) && $_GET['redirect_to_checkout'] == '1') {
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}
