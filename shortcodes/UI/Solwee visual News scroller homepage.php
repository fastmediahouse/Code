add_shortcode('solwee_news_grid', function () {
    $entries = get_option('solwee_ticker_titles', []);
    if (empty($entries)) return '<p>❌ No newsletters found.</p>';

    $tiles = [];

    foreach ($entries as $entry) {
        $title = trim($entry['title'] ?? '');
        $image = trim($entry['image'] ?? '');

        if (!$title) continue;

        if (empty($image) || !filter_var($image, FILTER_VALIDATE_URL)) {
            $image = 'https://via.placeholder.com/600x400/cccccc/000000?text=No+Image';
        }

        // ✅ Update: Search by title instead of loading feature-read page
        $url = esc_url('/search-results/?q=' . urlencode($title));

        $tiles[] = "
            <div class='scroll-ticker-tile'>
                <a href='{$url}'>
                    <div class='scroll-tile-image' style='background-image: url({$image});'>
                        <div class='scroll-tile-overlay'>
                            <div class='scroll-tile-title'>" . esc_html($title) . "</div>
                            <div class='scroll-tile-button'>See More →</div>
                        </div>
                    </div>
                </a>
            </div>
        ";
    }

    ob_start();
    ?>
    <style>
    .solwee-news-scroller-wrapper {
        position: relative;
        background: #f9f9f9;
        padding: 20px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        max-width: 1300px;
        margin: 0 auto;
        width: 100%;
    }

    .solwee-news-scroller-track {
        display: flex;
        overflow-x: auto;
        gap: 20px;
        scroll-behavior: smooth;
        padding: 0 10px;
    }

    .solwee-news-scroller-track::-webkit-scrollbar {
        display: none;
    }

    .scroll-ticker-tile {
        min-width: 280px;
        max-width: 300px;
        flex-shrink: 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        background: #fff;
    }

    .scroll-tile-image {
        background-size: cover;
        background-position: center;
        height: 200px;
        position: relative;
    }

    .scroll-tile-overlay {
        position: absolute;
        bottom: 0;
        width: 100%;
        background: rgba(0,0,0,0.5);
        color: #fff;
        padding: 12px;
    }

    .scroll-tile-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 6px;
    }

    .scroll-tile-button {
        display: inline-block;
        font-size: 13px;
        padding: 5px 10px;
        border: 1px solid #fff;
        border-radius: 4px;
    }

    .arrow-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .arrow-left { left: 10px; }
    .arrow-right { right: 10px; }

    @media (max-width: 768px) {
        .solwee-news-scroller-wrapper {
            padding: 10px 0;
        }

        .solwee-news-scroller-track {
            gap: 12px;
            padding: 0 8px;
        }

        .scroll-ticker-tile {
            min-width: 220px;
        }

        .scroll-tile-image {
            height: 160px;
        }

        .scroll-tile-title {
            font-size: 14px;
        }

        .arrow-button {
            width: 30px;
            height: 30px;
            top: auto;
            bottom: 10px;
            transform: none;
            opacity: 0.8;
        }

        .arrow-left {
            left: 8px;
        }

        .arrow-right {
            right: 8px;
        }
    }
    </style>

    <div class="solwee-news-scroller-wrapper">
        <div class="arrow-button arrow-left" onclick="scrollNewsGrid('left')">‹</div>
        <div class="arrow-button arrow-right" onclick="scrollNewsGrid('right')">›</div>
        <div class="solwee-news-scroller-track" id="newsScrollerTrack">
            <?php echo implode("\n", $tiles); ?>
        </div>
    </div>

    <script>
    function scrollNewsGrid(direction) {
        const container = document.getElementById('newsScrollerTrack');
        const scrollAmount = 340;
        container.scrollBy({
            left: direction === 'left' ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        });
    }
    </script>
    <?php
    return ob_get_clean();
});
