add_shortcode('fastmedia_hub_tabs', function () {
    // Get current page URL to determine active tab
    $current_url = $_SERVER['REQUEST_URI'];
    
    ob_start();
    ?>
    <div class="fastmedia-topnav-layout">
        <!-- MINIMAL NAVIGATION WITH ICON + TEXT LINKS -->
        <div class="fm-minimal-tabs" id="fm-nav-tabs">
            <a href="/my-assets/" class="fm-tab-link <?php echo (strpos($current_url, '/my-assets/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-image"></i> My Assets
            </a>
            <a href="/brand/" class="fm-tab-link <?php echo (strpos($current_url, '/brand/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-palette"></i> Brand
            </a>
            <a href="/uploaded/" class="fm-tab-link <?php echo (strpos($current_url, '/uploaded/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cloud-upload-alt"></i> Uploaded
            </a>
            <a href="/licensed/" class="fm-tab-link <?php echo (strpos($current_url, '/licensed/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i> Purchased
            </a>
            <a href="/upload/" class="fm-tab-link <?php echo (strpos($current_url, '/upload/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cloud-upload"></i> Upload
            </a>
            <a href="/projects/" class="fm-tab-link <?php echo (strpos($current_url, '/projects/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i> Projects
            </a>
            <a href="/groups/" class="fm-tab-link <?php echo (strpos($current_url, '/groups/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Teams
            </a>
            <a href="/messages/" class="fm-tab-link <?php echo (strpos($current_url, '/messages/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="/admin/" class="fm-tab-link <?php echo (strpos($current_url, '/admin/') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i> Admin
            </a>
        </div>
    </div>
    <style>
    .fastmedia-topnav-layout {
        padding: 0;
        background: transparent;
    }
    
    .fm-minimal-tabs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(115px, 1fr));
        gap: 12px;
        padding: 0;
        width: 100%;
    }
    
    .fm-tab-link {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        font-weight: 400;
        text-decoration: none;
        color: #666;
        white-space: nowrap;
        position: relative;
        justify-content: flex-start;
    }
    
    .fm-tab-link i {
        font-size: 14px;
        color: #666;
        transition: color 0.2s ease;
        flex-shrink: 0;
    }
    
    .fm-tab-link:hover {
        color: #333;
    }
    
    .fm-tab-link:hover i {
        color: #333;
    }
    
    /* Active state with color coding */
    .fm-tab-link.active {
        color: #22c55e; /* Green color for active - matches Asset Type button */
        font-weight: 500;
    }
    
    .fm-tab-link.active i {
        color: #22c55e;
    }
    
    /* Active underline */
    .fm-tab-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #22c55e;
    }
    
    /* Desktop: Try to fit in single row when possible */
    @media (min-width: 1200px) {
        .fm-minimal-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            row-gap: 8px;
        }
        .fm-tab-link {
            flex: 0 0 auto;
        }
    }
    
    /* Medium screens: Grid layout */
    @media (min-width: 769px) and (max-width: 1199px) {
        .fm-minimal-tabs {
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 10px;
        }
    }
    
    /* Tablet and Mobile: Horizontal scroll */
    @media (max-width: 768px) {
        .fm-minimal-tabs {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            gap: 12px;
            padding: 8px 12px;
        }
        
        .fm-minimal-tabs::-webkit-scrollbar {
            display: none;
        }
        
        .fm-tab-link {
            flex-shrink: 0;
            font-size: 12px;
            padding: 5px 8px;
        }
        
        .fm-tab-link i {
            font-size: 14px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
});
