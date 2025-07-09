add_shortcode('solwee_news_ticker', function () {
    $start_time = microtime(true);

    // Load cached HTML if available
    $cached = get_transient('solwee_news_ticker_html');
    if ($cached) return $cached;

    $newsletters = [];
    $raw_entries = get_option('solwee_ticker_titles', []);

    foreach ($raw_entries as $entry) {
        if (is_array($entry) && isset($entry['title'])) {
            $newsletters[] = [
                'title' => is_array($entry['title']) ? implode(' ', $entry['title']) : (string) $entry['title'],
                'category' => isset($entry['category']) ? (string) $entry['category'] : ''
            ];
        } elseif (is_string($entry)) {
            $newsletters[] = ['title' => $entry, 'category' => ''];
        }
    }

    if (empty($newsletters)) return '<p>❌ No ticker titles available.</p>';

    $categoryStyles = [
        'News'         => ['icon' => '📰'],
        'Sport'        => ['icon' => '🏅'],
        'Entertainment'=> ['icon' => '🎬'],
        'Travel'       => ['icon' => '✈️'],
        'Specialist'   => ['icon' => '📚'],
        'Creative'     => ['icon' => '🎨'],
    ];

    ob_start(); ?>
    <style>
    .solwee-ticker-container {
        overflow: hidden;
        background: #fff;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        padding: 4px 0;
        margin-bottom: 10px;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        position: relative;
    }

    .solwee-ticker-track {
        display: inline-block;
        white-space: nowrap;
        transform: translateX(100%);
        animation: ticker-scroll 80s linear infinite;
    }

    .solwee-ticker-item {
        display: inline-block;
        margin: 0 10px;
        font-size: 15px;
        font-weight: 400;
    }

    .solwee-ticker-separator {
        color: #999;
        margin: 0 5px;
    }

    .solwee-ticker-item a {
        text-decoration: none;
        color: #000;
    }

    .solwee-ticker-item a:hover {
        text-decoration: underline;
    }

    .solwee-ticker-container:hover .solwee-ticker-track {
        animation-play-state: paused;
    }

    @keyframes ticker-scroll {
        0%   { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }

    @media (max-width: 768px) {
        .solwee-ticker-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }

        .solwee-ticker-track {
            animation: none;
            transform: none;
            white-space: nowrap;
            display: flex;
            gap: 20px;
            min-width: max-content;
            padding: 0 10px;
        }

        .solwee-ticker-item,
        .solwee-ticker-separator {
            flex: 0 0 auto;
        }
    }
    </style>

    <div class="solwee-ticker-container">
        <div class="solwee-ticker-track">
            <?php foreach ($newsletters as $i => $n): 
                $title = is_array($n['title']) ? implode(' ', $n['title']) : (string) $n['title'];
                $category = isset($n['category']) ? (string) $n['category'] : '';
                $style = $categoryStyles[$category] ?? ['icon' => '🗞️'];
            ?>
                <div class="solwee-ticker-item">
                    <a href="<?php echo esc_url('/search-results/?q=' . urlencode($title)); ?>">
                        <?php echo esc_html($style['icon'] . ' ' . $title); ?>
                    </a>
                </div>
                <?php if ($i < count($newsletters) - 1): ?>
                    <span class="solwee-ticker-separator">|</span>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    $html = ob_get_clean();
    set_transient('solwee_news_ticker_html', $html, 3600); // 1 hour cache

    $duration = round(microtime(true) - $start_time, 4);
    echo "<!-- Ticker loaded in {$duration} seconds (cached next time) -->";
    return $html;
});
