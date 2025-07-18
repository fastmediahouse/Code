// ✅ MAGIC SEARCH BAR SHORTCODE – Projects Integration with Full Filters and Correct Conditional Routing

add_shortcode('magic_searchbar', function() {
    $query         = sanitize_text_field($_GET['q'] ?? '');
    $category      = sanitize_text_field($_GET['category'] ?? '');
    $source        = sanitize_text_field($_GET['source'] ?? 'ALL');
    $project       = sanitize_text_field($_GET['project'] ?? '');
    $orientation   = sanitize_text_field($_GET['orientation'] ?? '');
    $archiveID     = sanitize_text_field($_GET['archiveID'] ?? '');
    $sort          = sanitize_text_field($_GET['sort'] ?? '');
    $modelReleased = sanitize_text_field($_GET['modelReleased'] ?? '');
    $colorMode     = sanitize_text_field($_GET['colorMode'] ?? '');
    $dateFrom      = sanitize_text_field($_GET['dateFrom'] ?? '');
    $dateTo        = sanitize_text_field($_GET['dateTo'] ?? '');

    $webID = '57';
    $collections = get_transient('solwee_collections');
    if (!$collections) {
        $response = wp_remote_get('https://api.solwee.com/api/v2/list/collections', [
            'headers' => ['Content-Type' => 'application/json', 'X-WebID' => $webID],
            'timeout' => 10,
        ]);
        if (!is_wp_error($response)) {
            $collections = json_decode(wp_remote_retrieve_body($response), true);
            if (is_array($collections)) {
                set_transient('solwee_collections', $collections, DAY_IN_SECONDS);
            }
        }
    }

    $current_url = esc_url($_SERVER['REQUEST_URI']);
    $is_local = (strpos($current_url, '/my-assets/') !== false ||
                 strpos($current_url, '/brand/') !== false ||
                 strpos($current_url, '/uploaded/') !== false ||
                 strpos($current_url, '/licensed/') !== false ||
                 strpos($current_url, '/project-view/') !== false);

    $action_url = $is_local ? $current_url : '/magic-search-results/';

    ob_start();
    ?>
    <form method="GET" action="<?= esc_url($action_url) ?>" id="magic-search-form"
        style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; align-items:center; justify-content:center;">

        <input type="text" name="q" placeholder="Search Creative, Editorial, or Uploaded..." value="<?= esc_attr($query) ?>"
            style="flex-grow:1; min-width:260px; padding:12px 14px; font-size:16px; border:2px solid #ccc; border-radius:6px;" />

        <input type="hidden" name="source" id="magic-source-field" value="<?= esc_attr($source) ?>" />

        <button type="submit" style="padding:12px 20px; font-size:16px; background:#111; color:#fff; border:none; border-radius:6px; cursor:pointer;">
            Search
        </button>

        <button type="button" onclick="document.getElementById('magic-filters').classList.toggle('visible')" style="padding:12px 14px; font-size:15px; border:1px solid #ccc; border-radius:6px; background:#fff; color:#111; cursor:pointer;">
            Filters
        </button>

        <button type="button" onclick="document.getElementById('magic-sources').classList.toggle('visible')" style="padding:12px 14px; font-size:15px; border-radius:6px; border:none; background:#007BFF; color:#fff; cursor:pointer;">
            Asset Type
        </button>

        <div id="magic-sources" class="magic-sources" style="width:100%; text-align:center; margin-top:15px; display:none;">
            <?php
            $sources = [
                'ALL'       => ['label' => 'All', 'desc' => 'Search all sources together'],
                'STOCK'     => ['label' => 'Stock', 'desc' => 'Commercial and editorial stock photos'],
                'PROJECTS'  => ['label' => 'Projects', 'desc' => 'Images in your selected or active project'],
                'LI'        => ['label' => 'Licensed', 'desc' => 'Images you have already licensed'],
                'UPLOADED'  => ['label' => 'Uploaded', 'desc' => 'Assets you or your team uploaded'],
                'CP'        => ['label' => 'Comps (Preview)', 'desc' => 'Watermarked comps downloaded for layout use'],
                'MYASSETS'  => ['label' => 'My Assets', 'desc' => 'All of your assets across uploads and licensed'],
                'BRAND'     => ['label' => 'Brand', 'desc' => 'Brand kit or identity-specific images']
            ];
            foreach ($sources as $key => $meta) {
                $is_active = strpos($source, $key) !== false;
                echo "<button type='button' class='magic-source-btn' data-value='{$key}' title='{$meta['desc']}' style='margin:4px;padding:6px 12px;font-weight:500;border-radius:5px;border:1px solid #ccc;background:" . ($is_active ? '#111' : '#fff') . ";color:" . ($is_active ? '#fff' : '#111') . ";'>{$meta['label']}</button> ";
            }
            ?>
        </div>

        <?php if (strpos($source, 'PROJECTS') !== false) :
            $user_id = get_current_user_id();
            $user_projects = get_user_meta($user_id, 'fastmedia_user_projects', true);
            $user_projects = is_array($user_projects) ? $user_projects : ['Default'];
        ?>
        <select name="project" id="project-selector" style="min-width:220px; padding:8px 12px; font-size:14px; border-radius:4px;">
            <?php foreach ($user_projects as $proj): ?>
                <option value="<?= esc_attr($proj) ?>" <?= selected($proj, $project, false) ?>>
                    <?= esc_html($proj) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <div id="magic-filters" class="magic-filters <?= (!empty($orientation) || !empty($archiveID) || !empty($sort) || !empty($modelReleased) || !empty($colorMode) || !empty($dateFrom) || !empty($dateTo) || !empty($category)) ? 'visible' : '' ?>" style="display:none; width:100%; padding:10px 15px; border:1px solid #ccc; border-radius:6px; background:#f9f9f9; margin-top:10px;">
            <div style="display:flex; flex-wrap:wrap; gap:15px; justify-content:center;">
                <label for="category" style="font-size:14px; align-self:center;">Content type:</label>
                <select name="category" id="category" style="padding:8px 12px; font-size:14px; border:1px solid #ccc; border-radius:4px;">
                    <option value="">All</option>
                    <option value="creative" <?= selected($category, 'creative', false) ?>>Creative</option>
                    <option value="editorial" <?= selected($category, 'editorial', false) ?>>Editorial</option>
                </select>

                <select name="orientation" class="magic-autosubmit" style="padding:8px; min-width:140px;">
                    <option value="">Orientation</option>
                    <option value="horizontal" <?= selected($orientation, 'horizontal', false) ?>>Horizontal</option>
                    <option value="vertical" <?= selected($orientation, 'vertical', false) ?>>Vertical</option>
                    <option value="square" <?= selected($orientation, 'square', false) ?>>Square</option>
                </select>

                <select name="modelReleased" class="magic-autosubmit" style="padding:8px; min-width:140px;">
                    <option value="">Model Released</option>
                    <option value="true" <?= selected($modelReleased, 'true', false) ?>>Yes</option>
                    <option value="false" <?= selected($modelReleased, 'false', false) ?>>No</option>
                </select>

                <select name="archiveID" class="magic-autosubmit" style="padding:8px; min-width:160px;">
                    <option value="">Collection</option>
                    <?php if (is_array($collections)) foreach ($collections as $col): ?>
                        <option value="<?= esc_attr($col['id'] ?? $col['collectionID']) ?>" <?= selected($archiveID, ($col['id'] ?? $col['collectionID']), false) ?>>
                            <?= esc_html($col['name'] ?? $col['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort" class="magic-autosubmit" style="padding:8px; min-width:140px;">
                    <option value="">Sort by</option>
                    <option value="newest" <?= selected($sort, 'newest', false) ?>>Newest</option>
                    <option value="oldest" <?= selected($sort, 'oldest', false) ?>>Oldest</option>
                    <option value="relevant" <?= selected($sort, 'relevant', false) ?>>Most Relevant</option>
                </select>

                <select name="colorMode" class="magic-autosubmit" style="padding:8px 12px; font-size:14px; border-radius:4px;">
                    <option value="">Color Mode</option>
                    <option value="color" <?= selected($colorMode, 'color', false) ?>>Color</option>
                    <option value="bw" <?= selected($colorMode, 'bw', false) ?>>Black & White</option>
                </select>

                <select name="dateFrom" class="magic-autosubmit" style="padding:8px 12px; font-size:14px; border-radius:4px;">
                    <option value="">From Year</option>
                    <?php for ($y = date('Y'); $y >= 1990; $y--) : ?>
                        <option value="<?= $y ?>" <?= selected($dateFrom, (string)$y, false) ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>

                <select name="dateTo" class="magic-autosubmit" style="padding:8px 12px; font-size:14px; border-radius:4px;">
                    <option value="">To Year</option>
                    <?php for ($y = date('Y'); $y >= 1990; $y--) : ?>
                        <option value="<?= $y ?>" <?= selected($dateTo, (string)$y, false) ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </form>

    <style>
    #magic-sources.visible { display: block !important; }
    .magic-filters.visible { display: block !important; }
    .magic-source-btn.active { background: #111 !important; color: #fff !important; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.magic-source-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const field = document.getElementById('magic-source-field');
                let current = field.value.split(',').filter(v => v);
                const val = this.getAttribute('data-value');
                if (val === 'ALL') {
                    field.value = 'ALL';
                } else {
                    if (current.includes(val)) {
                        current = current.filter(v => v !== val);
                    } else {
                        current.push(val);
                    }
                    field.value = current.join(',');
                }
                document.querySelectorAll('.magic-source-btn').forEach(b => b.classList.remove('active'));
                field.value.split(',').forEach(v => {
                    const match = document.querySelector(`.magic-source-btn[data-value='${v}']`);
                    if (match) match.classList.add('active');
                });
            });
        });

        document.querySelectorAll('.magic-autosubmit').forEach(select => {
            select.addEventListener('change', function () {
                document.getElementById('magic-search-form').submit();
            });
        });

        if (window.jQuery && jQuery().select2) {
            jQuery('#project-selector').select2({
                placeholder: 'Select a project',
                allowClear: true,
                width: 'resolve'
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
});
