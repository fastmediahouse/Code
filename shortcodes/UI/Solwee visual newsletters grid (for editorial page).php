add_shortcode('solwee_visual_ticker_grid', function () {
    $entries = get_option('solwee_ticker_titles', []);
    if (empty($entries)) return '<p>No entries found.</p>';

    $tiles = [];

    foreach ($entries as $entry) {
        $title = trim($entry['title'] ?? '');
        $category = trim($entry['category'] ?? '');
        $image = esc_url($entry['image'] ?? '');

        if (!$title) continue;

        $search_url = esc_url("https://fastmediahouse.com/search-results/?q=" . urlencode($title));

        if (!$image) {
            $image = 'https://via.placeholder.com/600x400/cccccc/000000?text=No+Image';
        }

        $tiles[] = "
            <div class='ticker-tile'>
                <a href='{$search_url}'>
                    <div class='tile-image' style='background-image: url({$image});'>
                        <div class='tile-overlay'>
                            <div class='tile-title'>" . esc_html($title) . "</div>
                            <div class='tile-button'>See More →</div>
                        </div>
                    </div>
                </a>
            </div>
        ";
    }

    ob_start();
    ?>
    <style>
    .ticker-grid-wrapper {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 10px;
        margin-top: 10px;
    }

    @media (min-width: 992px) {
        .ticker-grid-wrapper {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .ticker-tile {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .tile-image {
        background-size: cover;
        background-position: center;
        width: 100%;
        height: 220px;
        position: relative;
    }
    .tile-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.5);
        color: white;
        padding: 15px;
        text-align: left;
    }
    .tile-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
    }
    .tile-button {
        display: inline-block;
        padding: 6px 12px;
        border: 1px solid white;
        border-radius: 4px;
        font-size: 14px;
        background: transparent;
    }
    </style>

    <div class="ticker-grid-wrapper">
        <?php echo implode("\n", $tiles); ?>
    </div>
    <?php
    return ob_get_clean();
});
