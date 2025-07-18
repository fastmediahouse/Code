add_shortcode('magic_searchbar', function() {
    // Fetch and sanitize input
    $query = sanitize_text_field($_GET['q'] ?? '');
    $source = sanitize_text_field($_GET['source'] ?? 'ALL');
    $project = sanitize_text_field($_GET['project'] ?? '');
    $orientation = sanitize_text_field($_GET['orientation'] ?? '');
    $archiveID = sanitize_text_field($_GET['archiveID'] ?? '');
    $sort = sanitize_text_field($_GET['sort'] ?? '');
    $modelReleased = sanitize_text_field($_GET['modelReleased'] ?? '');
    $colorMode = sanitize_text_field($_GET['colorMode'] ?? '');
    $dateFrom = sanitize_text_field($_GET['dateFrom'] ?? '');
    $dateTo = sanitize_text_field($_GET['dateTo'] ?? '');
    $category = sanitize_text_field($_GET['category'] ?? '');

    // Page URL context-based behavior (default to page-specific source)
    $current_url = esc_url($_SERVER['REQUEST_URI']);
    if (empty($_GET['source'])) { // Only set default if no source is explicitly selected
        if (strpos($current_url, '/my-assets/') !== false) {
            $source = 'MYASSETS';
        } elseif (strpos($current_url, '/brand/') !== false) {
            $source = 'BRAND';
        } elseif (strpos($current_url, '/uploaded/') !== false) {
            $source = 'UPLOADED';
        } elseif (strpos($current_url, '/licensed/') !== false) {
            $source = 'LICENSED';
        } elseif (strpos($current_url, '/project-view/') !== false) {
            $source = 'PROJECTS';
        }
    }

    // Collections (placeholder - replace with your actual collections data)
    $collections = []; // Your collections array here

    ob_start();
    ?>
    <form method="GET" action="<?= esc_url($_SERVER['REQUEST_URI']) ?>" id="magic-search-form" style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; align-items:center; justify-content:flex-start;">
        <!-- Search bar -->
        <input type="text" name="q" placeholder="Search..." value="<?= esc_attr($query) ?>" style="flex-grow:1; min-width:260px; padding:12px 14px; font-size:16px; border:2px solid #ccc; border-radius:6px;" />

        <!-- Hidden source field -->
        <input type="hidden" name="source" id="magic-source-field" value="<?= esc_attr($source) ?>" />

        <!-- Search button -->
        <button type="submit" class="magic-button magic-button-primary">Search</button>

        <!-- Filters Button -->
        <button type="button" onclick="toggleFilters()" class="magic-button magic-button-secondary">
            <span class="button-icon">⚙️</span> Filters
        </button>

        <!-- Asset Type Button -->
        <button type="button" onclick="toggleAssetTypeMenu()" class="magic-button magic-button-accent">
            Asset Type
        </button>

        <!-- Info Button -->
        <button type="button" onclick="toggleSearchTips()" class="magic-button magic-button-info" title="Search Tips">
            ℹ️
        </button>
    </form>

    <!-- Asset Type Menu -->
    <div id="magic-sources" class="magic-sources" style="display:none;">
        <?php
        $sources = [
            'ALL'       => ['label' => 'All', 'desc' => 'Search all sources together'],
            'STOCK'     => ['label' => 'Stock', 'desc' => 'Commercial and editorial stock photos'],
            'PROJECTS'  => ['label' => 'Projects', 'desc' => 'Images in your selected or active project'],
            'LICENSED'  => ['label' => 'Licensed', 'desc' => 'Images you have already licensed'],
            'UPLOADED'  => ['label' => 'Uploaded', 'desc' => 'Assets you or your team uploaded'],
            'COMPS'     => ['label' => 'Comps (Preview)', 'desc' => 'Watermarked comps downloaded for layout use'],
            'MYASSETS'  => ['label' => 'My Assets', 'desc' => 'All of your assets across uploads and licensed'],
            'BRAND'     => ['label' => 'Brand', 'desc' => 'Brand kit or identity-specific images']
        ];
        
        $selected_sources = explode(',', $source);
        
        foreach ($sources as $key => $meta):
            $is_active = in_array($key, $selected_sources);
        ?>
            <button type="button" 
                    class="magic-source-btn <?= $is_active ? 'active' : '' ?>" 
                    data-value="<?= $key ?>" 
                    title="<?= esc_attr($meta['desc']) ?>" 
                    onclick="toggleAssetType('<?= $key ?>')">
                <?= esc_html($meta['label']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Filters Menu -->
    <div id="magic-filters" class="magic-filters" style="display:none;">
        <div class="filters-grid">
            <!-- Category Filter -->
            <select name="category" class="magic-filter-select">
                <option value="">Category</option>
                <option value="creative" <?= selected($category, 'creative', false) ?>>Creative</option>
                <option value="editorial" <?= selected($category, 'editorial', false) ?>>Editorial</option>
            </select>

            <!-- Orientation Filter -->
            <select name="orientation" class="magic-filter-select">
                <option value="">Orientation</option>
                <option value="horizontal" <?= selected($orientation, 'horizontal', false) ?>>Horizontal</option>
                <option value="vertical" <?= selected($orientation, 'vertical', false) ?>>Vertical</option>
                <option value="square" <?= selected($orientation, 'square', false) ?>>Square</option>
            </select>

            <!-- Model Released Filter -->
            <select name="modelReleased" class="magic-filter-select">
                <option value="">Model Released</option>
                <option value="true" <?= selected($modelReleased, 'true', false) ?>>Yes</option>
                <option value="false" <?= selected($modelReleased, 'false', false) ?>>No</option>
            </select>

            <!-- Archive/Collection Filter -->
            <select name="archiveID" class="magic-filter-select">
                <option value="">Collection</option>
                <?php if (is_array($collections)): ?>
                    <?php foreach ($collections as $col): ?>
                        <option value="<?= esc_attr($col['id'] ?? $col['collectionID']) ?>" 
                                <?= selected($archiveID, ($col['id'] ?? $col['collectionID']), false) ?>>
                            <?= esc_html($col['name'] ?? $col['label']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <!-- Sorting Filter -->
            <select name="sort" class="magic-filter-select">
                <option value="">Sort by</option>
                <option value="newest" <?= selected($sort, 'newest', false) ?>>Newest</option>
                <option value="oldest" <?= selected($sort, 'oldest', false) ?>>Oldest</option>
                <option value="relevant" <?= selected($sort, 'relevant', false) ?>>Most Relevant</option>
            </select>

            <!-- Color Mode Filter -->
            <select name="colorMode" class="magic-filter-select">
                <option value="">Color Mode</option>
                <option value="color" <?= selected($colorMode, 'color', false) ?>>Color</option>
                <option value="bw" <?= selected($colorMode, 'bw', false) ?>>Black & White</option>
            </select>

            <!-- Date Range Filters -->
            <select name="dateFrom" class="magic-filter-select">
                <option value="">From Year</option>
                <?php for ($y = date('Y'); $y >= 1990; $y--): ?>
                    <option value="<?= $y ?>" <?= selected($dateFrom, (string)$y, false) ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>

            <select name="dateTo" class="magic-filter-select">
                <option value="">To Year</option>
                <?php for ($y = date('Y'); $y >= 1990; $y--): ?>
                    <option value="<?= $y ?>" <?= selected($dateTo, (string)$y, false) ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- Search Tips Popup -->
    <div id="search-tips-popup" class="search-tips-popup" style="display:none;">
        <div class="popup-content">
            <h3>Search Tips</h3>
            <ul>
                <li><strong>AND</strong>: Combine terms (e.g., <code>car AND vehicle</code>)</li>
                <li><strong>OR</strong>: Either term (e.g., <code>car OR vehicle</code>)</li>
                <li><strong>NOT</strong> or <strong>-</strong>: Exclude terms (e.g., <code>car NOT auto</code> or <code>car -auto</code>)</li>
                <li><strong>"quotes"</strong>: Exact phrase (e.g., <code>"red car"</code>)</li>
            </ul>
            <button onclick="toggleSearchTips()" class="magic-button magic-button-primary">Close</button>
        </div>
    </div>

    <script>
        // Toggle Asset Type Menu visibility
        function toggleAssetTypeMenu() {
            const menu = document.getElementById('magic-sources');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        // Toggle Search Tips popup
        function toggleSearchTips() {
            const popup = document.getElementById('search-tips-popup');
            popup.style.display = popup.style.display === 'block' ? 'none' : 'block';
        }

        // Toggle Filters visibility
        function toggleFilters() {
            const filters = document.getElementById('magic-filters');
            filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
        }

        // Toggle asset type selection with multi-select support
        function toggleAssetType(value) {
            const field = document.getElementById('magic-source-field');
            let current = field.value.split(',').filter(v => v);
            
            if (value === 'ALL') {
                // Selecting ALL clears other selections
                field.value = 'ALL';
            } else {
                // Remove ALL if it's selected
                current = current.filter(v => v !== 'ALL');
                
                if (current.includes(value)) {
                    // Deselect if already selected
                    current = current.filter(v => v !== value);
                } else {
                    // Add to selected
                    current.push(value);
                }
                
                // If nothing is selected, default to ALL
                field.value = current.length > 0 ? current.join(',') : 'ALL';
            }
            
            // Update button states
            updateAssetTypeButtons();
        }
        
        // Update visual state of asset type buttons
        function updateAssetTypeButtons() {
            const field = document.getElementById('magic-source-field');
            const selected = field.value.split(',').filter(v => v);
            
            document.querySelectorAll('.magic-source-btn').forEach(btn => {
                const value = btn.getAttribute('data-value');
                if (selected.includes(value)) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
        
        // Initialize button states on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateAssetTypeButtons();
        });
    </script>

    <style>
        /* Base button styles with proper alignment */
        .magic-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 20px;
            height: 44px;
            font-size: 15px;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            gap: 6px;
        }
        
        .magic-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .magic-button:active {
            transform: translateY(0);
        }
        
        /* Button variants */
        .magic-button-primary {
            background: #111;
            color: #fff;
            min-width: 100px;
        }
        
        .magic-button-primary:hover {
            background: #333;
        }
        
        .magic-button-secondary {
            background: #fff;
            color: #111;
            border: 1px solid #ddd;
        }
        
        .magic-button-secondary:hover {
            background: #f8f8f8;
            border-color: #bbb;
        }
        
        .magic-button-accent {
            background: #28a745;
            color: #fff;
        }
        
        .magic-button-accent:hover {
            background: #218838;
        }
        
        .magic-button-info {
            width: 44px;
            padding: 0;
            background: #f1f1f1;
            color: #007BFF;
            font-size: 18px;
        }
        
        .magic-button-info:hover {
            background: #e8e8e8;
        }
        
        /* Asset type buttons */
        .magic-sources {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .magic-source-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            height: 36px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 20px;
            border: 1px solid #ddd;
            background: #fff;
            color: #333;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .magic-source-btn:hover {
            background: #f1f1f1;
            border-color: #999;
        }
        
        .magic-source-btn.active {
            background: #111;
            color: #fff;
            border-color: #111;
        }
        
        /* Filters section */
        .magic-filters {
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-top: 10px;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }
        
        .magic-filter-select {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }
        
        .magic-filter-select:hover {
            border-color: #999;
        }
        
        .magic-filter-select:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.1);
        }
        
        /* Search tips popup */
        .search-tips-popup {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .popup-content {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .popup-content h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 24px;
            color: #111;
        }
        
        .popup-content ul {
            margin: 0 0 20px 0;
            padding-left: 20px;
        }
        
        .popup-content li {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        
        .popup-content code {
            background: #f1f1f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 14px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #magic-search-form {
                gap: 10px;
            }
            
            .magic-button {
                height: 40px;
                font-size: 14px;
                padding: 0 16px;
            }
            
            .magic-button-info {
                width: 40px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
    return ob_get_clean();
});
