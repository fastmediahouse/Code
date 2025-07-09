// ✅ SHORTCODE: [fastmedia_music_page] – Jimmy Turner Music Services Page
add_shortcode('fastmedia_music_page', function () {
    ob_start();
    ?>

    <div style="background:#fff; color:#000; padding:40px 20px; font-family:sans-serif;">

        <h1 style="font-size:38px; margin-bottom:10px;">🎵 Jimmy Turner – Soundtrack Your Story</h1>
        <p style="font-size:18px; max-width:800px; line-height:1.6;">Jimmy Turner is Fast Media’s dedicated music label, powered by our creative collaboration with Tin Drum Music. From original scoring to audio branding, our studio delivers broadcast-quality music crafted to elevate your story, identity, and audience connection.</p>

        <div style="margin-top:40px; max-width:1100px; display:grid; grid-template-columns: 1fr 1fr; gap:40px; align-items:start;">
            <div>
                <h2 style="font-size:24px; margin-bottom:15px;">🎼 Original Composition</h2>
                <p style="line-height:1.6;">Our composers craft tailored music across genres — from ambient scores to vibrant beats. Whether you’re producing a film, campaign, or podcast, we deliver a sound that is unmistakably yours.</p>
                <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/90779082_l-scaled.jpg" style="width:100%; border-radius:6px; margin-top:20px;">
            </div>
            <div>
                <h2 style="font-size:24px; margin-bottom:15px;">🔊 Sonic Branding</h2>
                <p style="line-height:1.6;">We develop your brand's unique sonic fingerprint — short motifs, intros, jingles, and voice treatments that make your audio presence instantly recognisable.</p>
                <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/100580664_l-scaled.jpg" style="width:100%; border-radius:6px; margin-top:20px;">
            </div>
            <div>
                <h2 style="font-size:24px; margin-bottom:15px;">🎧 Music Supervision</h2>
                <p style="line-height:1.6;">From briefing and clearance to sync and licensing, our music supervision ensures every track aligns perfectly with your vision, tone, and rights strategy.</p>
                <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/119588911_l-scaled.jpg" style="width:100%; border-radius:6px; margin-top:20px;">
            </div>
            <div>
                <h2 style="font-size:24px; margin-bottom:15px;">🏬 In-store Sound</h2>
                <p style="line-height:1.6;">Enhance the atmosphere of your store, event or venue with curated playlists and custom soundscapes. We match sound to setting with precision.</p>
                <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/101500509_l-scaled.jpg" style="width:100%; border-radius:6px; margin-top:20px;">
            </div>
            <div>
                <h2 style="font-size:24px; margin-bottom:15px;">🎵 Stock Music & Sound</h2>
                <p style="line-height:1.6;">Need fast and flexible options? Access our curated catalogue of rights-cleared music and FX — ready to license, searchable, and cost-efficient.</p>
                <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/103426909_l-scaled.jpg" style="width:100%; border-radius:6px; margin-top:20px;">
            </div>
        </div>

        <div style="margin:60px 0 30px; text-align:center;">
            <a href="/contact/" style="background:#000; color:#fff; padding:14px 28px; font-weight:bold; font-size:16px; border-radius:6px; text-decoration:none;">Request a Demo</a>
        </div>

        <div style="margin-top:40px;">
            <h2 style="font-size:22px; margin-bottom:20px;">🎬 Music in Action</h2>
            <div style="display:flex; flex-wrap:wrap; gap:20px; justify-content:center;">
                <?php
                $images = [
                    '201118571_l-scaled.jpg', '64865720_l-scaled.jpg', '105007716_l-scaled.jpg', '143505416_l-scaled.jpg'
                ];
                foreach ($images as $img) {
                    echo '<div style="flex: 1 1 calc(25% - 20px); max-width: 250px;"><img src="https://fastmediahouse.com/wp-content/uploads/2025/06/' . $img . '" alt="Music visual" style="width:100%; height:auto; border-radius:8px;" /></div>';
                }
                ?>
            </div>
        </div>

    </div>

    <?php
    return ob_get_clean();
});
