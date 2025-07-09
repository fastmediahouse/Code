add_shortcode('solwee_mosaic_ticker', function () {
    $creative = wp_remote_post("https://api.solwee.com/api/v2/search/images/creative", [
        'timeout' => 10,
        'headers' => ['Content-Type' => 'application/json', 'X-WebID' => '57'],
        'body' => json_encode(['limit' => 24, 'sortingTypeID' => 5])
    ]);

    $editorial = wp_remote_post("https://api.solwee.com/api/v2/search/images/editorial", [
        'timeout' => 10,
        'headers' => ['Content-Type' => 'application/json', 'X-WebID' => '57'],
        'body' => json_encode(['limit' => 24, 'sortingTypeID' => 5])
    ]);

    $creative_data = json_decode(wp_remote_retrieve_body($creative), true);
    $editorial_data = json_decode(wp_remote_retrieve_body($editorial), true);
    $images = array_merge($creative_data['results'] ?? [], $editorial_data['results'] ?? []);
    shuffle($images);

    ob_start();
    ?>
    <style>
    .mosaic-ticker-wrapper {
        padding: 20px 0;
        margin: 30px 0;
        background: #f9f9f9;
        border-radius: 16px;
        width: 100%;
    }

    .mosaic-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    @media (min-width: 1024px) {
        .mosaic-grid {
            grid-template-columns: repeat(8, 1fr);
        }
    }

    @media (max-width: 767px) {
        .mosaic-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .mosaic-grid a,
    .mosaic-grid .see-more-cell {
        display: block;
        width: 100%;
        height: 200px;
        border-radius: 16px;
        overflow: hidden;
    }

    .mosaic-grid img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 16px;
        opacity: 0;
        filter: blur(4px);
        transition: transform 0.3s ease, filter 0.4s ease, opacity 0.5s ease;
        will-change: transform;
    }

    .mosaic-grid img.loaded {
        filter: blur(0);
        opacity: 1;
    }

    .mosaic-grid img:hover {
        transform: scale(1.05) translateY(-8px);
    }

    .mosaic-grid .see-more-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: row;
        background: transparent;
        color: #333;
        text-align: center;
        font-size: 18px;
        font-weight: 600;
        border: 2px solid #ccc;
        border-radius: 30px;
        box-sizing: border-box;
        transition: all 0.3s ease;
        position: relative;
    }

    .mosaic-grid .see-more-cell:hover {
        border-color: #999;
        background: #f1f1f1;
        color: #000;
    }

    .mosaic-grid .see-more-cell span {
        display: inline-block;
        padding-right: 10px;
    }

    .mosaic-grid .see-more-cell::after {
        content: "→";
        font-size: 20px;
        display: inline-block;
        margin-left: 5px;
        transition: margin 0.2s ease;
    }

    .mosaic-grid .see-more-cell:hover::after {
        margin-left: 10px;
    }
    </style>

    <div class="mosaic-ticker-wrapper">
        <div class="mosaic-grid" id="hybridMosaicGrid">
            <?php
            $count = 0;
            foreach ($images as $img) {
                $thumb = $img['thumb260Url'] ?? $img['thumbUrl'] ?? $img['previewPath'] ?? '';
                if (!$thumb || $count >= 23) continue;
                ?>
                <a href="/image-detail/?productID=<?= esc_attr($img['productID']) ?>">
                    <img 
                        src="<?= esc_url($thumb) ?>" 
                        data-productid="<?= esc_attr($img['productID']) ?>" 
                        onload="this.classList.add('loaded');" />
                </a>
                <?php $count++; 
            }
            ?>
            <a class="see-more-cell" href="/search-results/"><span>See More</span></a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.getElementById('hybridMosaicGrid');
        const images = Array.from(grid.querySelectorAll('img'));
        const interval = 3000;
        const updateCount = 3;

        function getUniqueTargets(count) {
            return [...images].sort(() => 0.5 - Math.random()).slice(0, count);
        }

        function getNewImages(count = 5) {
            const source = Math.random() < 0.5 ? 'editorial' : 'creative';
            return fetch('https://api.solwee.com/api/v2/search/images/' + source, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WebID': '57'
                },
                body: JSON.stringify({
                    limit: count,
                    sortingTypeID: 5,
                    offset: Math.floor(Math.random() * 300)
                })
            })
            .then(res => res.json())
            .then(data => data.results || [])
            .catch(() => []);
        }

        setInterval(() => {
            const targets = getUniqueTargets(updateCount);
            getNewImages(updateCount).then(newImages => {
                targets.forEach((target, idx) => {
                    const newImg = newImages[idx];
                    if (!newImg) return;
                    const newSrc = newImg.thumb260Url || newImg.thumbUrl || newImg.previewPath;
                    if (!newSrc) return;

                    const temp = new Image();
                    temp.onload = () => {
                        target.classList.remove('loaded');
                        target.src = newSrc;
                        target.dataset.productid = newImg.productID;
                        setTimeout(() => target.classList.add('loaded'), 50);
                    };
                    temp.src = newSrc;
                });
            });
        }, interval);
    });
    </script>
    <?php
    return ob_get_clean();
});
