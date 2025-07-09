add_shortcode('fm_123rf_faux_search', function () {
    ob_start();
    ?>
    <div class="fm-123rf-page">

        <!-- 🔍 Faux Searchbar -->
        <form action="https://nl.123rf.com/search.php" method="get" target="_blank" class="fm-123rf-searchbar" onsubmit="alert('You will be redirected to our official 123RF partner site to complete your search.');">
            <input type="text" name="word" placeholder="Search affordable stock images…" required>
            <button type="submit">Search</button>
        </form>

        <!-- ✅ Left-aligned Note -->
        <div class="fm-123rf-note-wrapper">
            <p class="fm-123rf-redirect-note">
                Powered by our official 123RF partnership — results open in a new tab.
            </p>
        </div>

        <!-- 🟡 Heading -->
        <h2 class="fm-123rf-subtitle">Unlimited content for every budget</h2>

        <!-- 🖼️ 123RF Mosaic -->
        <div class="fm-123rf-mosaic">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/67065818_l-scaled.jpg" alt="">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/160365659_l-scaled.jpg" alt="">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/230851158_l-scaled.jpg" alt="">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/125091841_l-scaled.jpg" alt="">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/214088120_l-scaled.jpg" alt="">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/210729982_l-scaled.jpg" alt="">
            <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/239318646_l-scaled.jpg" alt="">
            <a class="fm-see-more-tile" href="https://nl.123rf.com/" target="_blank">See more →</a>
        </div>

        <!-- 🧩 4-Image Button Grid -->
        <div class="fm-123rf-bottom-grid">
            <div class="fm-123rf-tile" style="background-image: url('https://fastmediahouse.com/wp-content/uploads/2025/06/148934837_l-scaled.jpg');">
                <div class="fm-overlay-text">Music</div>
                <a class="fm-overlay-button" href="https://nl.123rf.com/stock-audio/" target="_blank">Explore</a>
            </div>
            <div class="fm-123rf-tile" style="background-image: url('https://fastmediahouse.com/wp-content/uploads/2025/06/178130295_l-scaled.jpg');">
                <div class="fm-overlay-text">Unlimited Photography</div>
                <a class="fm-overlay-button" href="https://nl.123rf.com/stock-foto/" target="_blank">Explore</a>
            </div>
            <div class="fm-123rf-tile" style="background-image: url('https://fastmediahouse.com/wp-content/uploads/2025/06/AI-video-generator.png');">
                <div class="fm-overlay-text">AI Video Generator</div>
                <a class="fm-overlay-button" href="https://www.blieve.ai/video-generator" target="_blank">Explore</a>
            </div>
            <div class="fm-123rf-tile" style="background-image: url('https://fastmediahouse.com/wp-content/uploads/2025/06/AI-Image-generator.png');">
                <div class="fm-overlay-text">AI Image Generator</div>
                <a class="fm-overlay-button" href="https://nl.123rf.com/ai-image-generator/" target="_blank">Explore</a>
            </div>
        </div>

    </div>

    <style>
    .fm-123rf-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        font-family: "Roboto", sans-serif;
    }

    .fm-123rf-searchbar {
        display: flex;
        max-width: 800px;
        margin: 0 auto;
        margin-bottom: 10px;
    }

    .fm-123rf-searchbar input {
        flex: 1;
        padding: 14px 20px;
        font-size: 18px;
        border: 2px solid #fcd100;
        border-right: none;
        border-radius: 8px 0 0 8px;
    }

    .fm-123rf-searchbar button {
        padding: 14px 30px;
        background-color: #fcd100;
        color: #000;
        font-weight: bold;
        border: 2px solid #fcd100;
        border-radius: 0 8px 8px 0;
        cursor: pointer;
        transition: 0.3s;
    }

    .fm-123rf-searchbar button:hover {
        background-color: #000;
        color: #fff;
    }

    .fm-123rf-note-wrapper {
        max-width: 800px;
        margin: 10px auto 0 auto;
        text-align: left;
    }

    .fm-123rf-redirect-note {
        font-size: 16px;
        color: #555;
        margin: 0;
        padding-left: 4px;
    }

    .fm-123rf-subtitle {
        font-size: 28px;
        color: #7A7A7A;
        font-weight: 600;
        text-align: left;
        margin-top: 20px;
    }

    .fm-123rf-mosaic {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        margin: 30px 0;
    }

    .fm-123rf-mosaic img {
        width: 100%;
        height: auto;
        object-fit: cover;
        border-radius: 6px;
    }

    .fm-see-more-tile {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fcd100;
        color: #000;
        font-weight: bold;
        text-decoration: none;
        font-size: 18px;
        border-radius: 6px;
    }

    .fm-123rf-bottom-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-top: 40px;
    }

    .fm-123rf-tile {
        position: relative;
        height: 250px;
        background-size: cover;
        background-position: center;
        border-radius: 10px;
        overflow: hidden;
    }

    .fm-overlay-text {
        position: absolute;
        top: 20px;
        left: 20px;
        font-size: 24px;
        font-weight: bold;
        color: white;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }

    .fm-overlay-button {
        position: absolute;
        bottom: 20px;
        left: 20px;
        padding: 10px 16px;
        background-color: white;
        color: #000;
        font-weight: bold;
        font-size: 14px;
        text-decoration: none;
        border-radius: 4px;
        transition: 0.3s;
    }

    .fm-overlay-button:hover {
        background-color: #fcd100;
    }

    @media (max-width: 768px) {
        .fm-123rf-mosaic,
        .fm-123rf-bottom-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .fm-123rf-subtitle {
            font-size: 22px;
        }
    }
    </style>
    <?php
    return ob_get_clean();
});
