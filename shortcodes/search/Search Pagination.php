add_shortcode('solwee_pagination', function ($atts) {
    $page = isset($atts['page']) ? intval($atts['page']) : 1;
    $total = isset($atts['total']) ? intval($atts['total']) : 0;
    $limit = isset($atts['limit']) ? intval($atts['limit']) : 20;

    $total_pages = ceil($total / $limit);
    if ($total_pages < 2) return ''; // No need to show pagination

    $base_url = strtok($_SERVER["REQUEST_URI"], '?');
    $query_args = $_GET;
    $range = 2;
    $start = max(1, $page - $range);
    $end = min($total_pages, $page + $range);

    ob_start();
    ?>
    <style>
    .solwee-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        margin: 30px 0;
        flex-wrap: wrap;
        font-size: 14px;
    }

    .solwee-pagination a,
    .solwee-pagination span {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 6px;
        background: #f0f0f0;
        color: #333;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .solwee-pagination a:hover {
        background: #ddd;
    }

    .solwee-pagination .current-page {
        background: #333;
        color: #fff;
        font-weight: bold;
        cursor: default;
    }
    </style>

    <div class="solwee-pagination">
        <?php
        if ($page > 1) {
            $query_args['page_num'] = $page - 1;
            echo '<a href="' . esc_url($base_url . '?' . http_build_query($query_args)) . '">&laquo; Prev</a>';
        }

        if ($start > 1) {
            $query_args['page_num'] = 1;
            echo '<a href="' . esc_url($base_url . '?' . http_build_query($query_args)) . '">1</a>';
            if ($start > 2) echo '<span>...</span>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $query_args['page_num'] = $i;
            $link = $base_url . '?' . http_build_query($query_args);
            if ($i == $page) {
                echo '<span class="current-page">' . $i . '</span>';
            } else {
                echo '<a href="' . esc_url($link) . '">' . $i . '</a>';
            }
        }

        if ($end < $total_pages) {
            if ($end < $total_pages - 1) echo '<span>...</span>';
            $query_args['page_num'] = $total_pages;
            echo '<a href="' . esc_url($base_url . '?' . http_build_query($query_args)) . '">' . $total_pages . '</a>';
        }

        if ($page < $total_pages) {
            $query_args['page_num'] = $page + 1;
            echo '<a href="' . esc_url($base_url . '?' . http_build_query($query_args)) . '">Next &raquo;</a>';
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
});

