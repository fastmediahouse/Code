add_shortcode('fastmedia_hub_tabs', function () {
    ob_start();
    ?>
    <div class="fastmedia-topnav-layout">
        <!-- TOP BAR WITH TOGGLE ONLY -->
        <div class="fm-topbar">
            <button id="fmToggleTabs" class="fm-toggle-tabs" title="Toggle Menu">☰</button>
        </div>

        <!-- BOXED NAVIGATION WITH ICON + TEXT LINKS -->
        <div id="fmTabMenu" class="fm-boxed-tabs">
            <a href="/my-assets/" class="fm-tab-box"><i class="fas fa-image"></i> My Assets</a>
            <a href="/brand/" class="fm-tab-box"><i class="fas fa-palette"></i> Brand</a>
            <a href="/uploaded/" class="fm-tab-box"><i class="fas fa-cloud-upload-alt"></i> Uploaded</a>
            <a href="/licensed/" class="fm-tab-box"><i class="fas fa-file-alt"></i> Purchased</a>
            <a href="/upload/" class="fm-tab-box"><i class="fas fa-cloud-upload"></i> Upload</a>
            <a href="/projects/" class="fm-tab-box"><i class="fas fa-folder"></i> Projects</a>
            <a href="/groups/" class="fm-tab-box"><i class="fas fa-users"></i> Teams</a>
            <a href="/messages/" class="fm-tab-box"><i class="fas fa-comments"></i> Messages</a>
            <a href="/admin/" class="fm-tab-box"><i class="fas fa-cogs"></i> Admin</a>
        </div>
    </div>

    <style>
    .fastmedia-topnav-layout { padding: 0; background: #f9f9f9; }

    .fm-topbar { 
        display: flex; 
        justify-content: flex-end; 
        align-items: center; 
        padding: 10px 16px; 
        background: #fff; 
        border-bottom: 1px solid #ccc; 
    }

    .fm-toggle-tabs { 
        background: #000; 
        color: #fff; 
        border: none; 
        padding: 8px 12px; 
        font-size: 18px; 
        border-radius: 4px; 
        cursor: pointer; 
        display: none; 
    }

    .fm-boxed-tabs { 
        display: flex; 
        justify-content: space-between; 
        gap: 16px; 
        padding: 16px; 
        background: #fff; 
        overflow-x: auto; 
        -webkit-overflow-scrolling: touch; 
        scroll-snap-type: x mandatory;
    }

    .fm-tab-box { 
        display: flex; 
        align-items: center; 
        gap: 8px; 
        padding: 8px 14px; /* Reduced padding for better fit */
        background: #fff; 
        border-radius: 8px; 
        border: 1px solid #ccc; 
        cursor: pointer; 
        transition: all 0.2s ease; 
        font-size: 13px; /* Reduced font size */
        font-weight: 500; 
        text-decoration: none; 
        color: inherit;
    }

    .fm-tab-box i {
        font-size: 16px; /* Slightly smaller icon size */
        color: #000; /* Black icons */
    }

    .fm-tab-box:hover { 
        background: #f0f0f0; /* Light grey hover effect */
    }

    @media (max-width: 768px) {
        .fm-toggle-tabs { 
            display: block; 
        }
        .fm-boxed-tabs { 
            flex-wrap: nowrap; 
            display: none; 
        }
        .fm-boxed-tabs.show { 
            display: flex; 
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('fmToggleTabs');
        const tabMenu = document.getElementById('fmTabMenu');

        toggleBtn.addEventListener('click', () => {
            tabMenu.classList.toggle('show');
        });
    });
    </script>
    <?php
    return ob_get_clean();
});
