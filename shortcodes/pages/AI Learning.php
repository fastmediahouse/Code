add_shortcode('ai_dataset_page', function () {
    ob_start();
    ?>
    <style>
    body { font-family: 'Helvetica Neue', sans-serif; line-height: 1.6; color: #222; }
    .section { padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
    .hero-flex { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 40px; background: #f4f8fc; padding: 60px 20px; }
    .hero-text { flex: 1 1 500px; }
    .hero-text h1 { font-size: 42px; margin-bottom: 20px; }
    .hero-text p { font-size: 18px; margin-bottom: 20px; }
    .cta-button { padding: 15px 30px; background: #000; color: #fff; text-decoration: none; font-weight: bold; border-radius: 4px; display: inline-block; }
    .hero-img { flex: 1 1 400px; }
    .hero-img img { width: 100%; border-radius: 10px; }

    .features, .steps { display: flex; flex-wrap: wrap; gap: 20px; text-align: center; justify-content: space-between; margin-bottom: 30px; }
    .feature-box, .step-box { flex: 1 1 calc(25% - 20px); background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 25px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); min-height: 260px; }
    .feature-box img { height: 40px; margin-bottom: 15px; filter: grayscale(100%); }
    .step-box strong { display: block; margin-bottom: 10px; font-size: 18px; }

    .two-col { display: flex; flex-wrap: wrap; gap: 40px; align-items: center; }
    .two-col .left, .two-col .right { flex: 1; }
    .two-col img { width: 100%; border-radius: 10px; }

    .dataset-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 30px; }
    .dataset-box { background: #f4f4f4; padding: 20px; border-radius: 8px; text-align: center; }
    .dataset-box img { width: 100%; border-radius: 6px; margin-top: 10px; }
    .dataset-box a { display: inline-block; margin-top: 10px; padding: 8px 20px; background: #000; color: #fff; text-decoration: none; font-weight: 500; border-radius: 4px; }

    .footer-cta {
      background: #000;
      color: #fff;
      padding: 30px 20px;
      text-align: center;
    }
    .footer-cta p {
      font-size: 16px;
      line-height: 1.6;
      margin: 10px 0 0;
    }
    </style>

    <section class="hero-flex">
      <div class="hero-text">
        <h1>Discover Smarter AI with Rights-Cleared Visual Datasets</h1>
        <p>Train your AI on trusted, diverse, and high-quality content. Fast Media helps power machine learning models with ethically sourced visual datasets, from people to industry, nature to news.</p>
        <p>With over 220 million content assets and nearly two decades of global licensing experience, we help teams build accurate, creative, and compliant AI systems. 3-day delivery available for most requests.</p>
        <a href="mailto:team@fastmediahouse.com" class="cta-button">Request a Dataset Sample</a>
      </div>
      <div class="hero-img">
        <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/130141693_l-scaled.jpg" alt="Futuristic AI concept">
      </div>
    </section>

    <section class="section">
      <h2 style="text-align:center; margin-bottom:30px;">Why Fast Media Data Works</h2>
      <div class="features">
        <div class="feature-box">
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/check-mark-icon-17.png" alt="Ethical Licensing">
          <strong>Ethical Licensing</strong>
          <p>Zero legal risk with model releases and clean metadata. We eliminate compliance risks with fully cleared assets.</p>
        </div>
        <div class="feature-box">
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/sliders-icon-27.png" alt="Tailored Curation">
          <strong>Tailored Curation</strong>
          <p>Only the visuals you need, nothing you don’t. Each dataset is custom filtered to match your task and model type.</p>
        </div>
        <div class="feature-box">
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/black-and-white-file-folder-icon-in-flat-style-vector.jpg" alt="Model Ready">
          <strong>Model‑Ready</strong>
          <p>Annotated, formatted, and optimized for machine learning. Datasets are tagged, deduplicated, and training ready.</p>
        </div>
        <div class="feature-box">
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/shield-png-icon-1.png" alt="Safe and Compliant">
          <strong>Safe & Compliant</strong>
          <p>Filtered for privacy, trademarks, brand elements, and sensitive content so your AI can be deployed with confidence.</p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="two-col">
        <div class="left">
          <h2>Our AI Dataset Services</h2>
          <ul>
            <li><strong>Custom Dataset Development:</strong> Curated visual collections tailored to your training goals.</li>
            <li><strong>Content Cleaning & Annotation:</strong> High-precision tagging, categorization, and exclusion of problematic content.</li>
            <li><strong>Mass Data Aggregation:</strong> Access a global network of over 660 million media assets.</li>
            <li><strong>Secure Cloud Delivery:</strong> Choose from presigned URLs, cloud syncing (e.g. AWS S3), or enterprise methods.</li>
          </ul>
        </div>
        <div class="right">
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/124692343_l-scaled.jpg" alt="AI dataset pipeline">
        </div>
      </div>
    </section>

    <section class="section">
      <h2 style="text-align:center; margin-bottom:30px;">Explore Available Datasets</h2>
      <div class="dataset-grid">
        <div class="dataset-box">
          <strong>People</strong>
          <p>Diversity, Emotion, Age Variability</p>
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/94746639_l-scaled.jpg" alt="People dataset">
          <a href="https://www.123rf.com/stock-photo/portrait_diverse_people.html" target="_blank">Search on 123RF</a>
        </div>
        <div class="dataset-box">
          <strong>Nature</strong>
          <p>Landscapes, Wildlife, Seasonal Conditions</p>
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/95835062_l-scaled.jpg" alt="Nature dataset">
          <a href="https://www.123rf.com/stock-photo/landscape_wildlife.html" target="_blank">Search on 123RF</a>
        </div>
        <div class="dataset-box">
          <strong>Culture</strong>
          <p>Architecture, Street Life, Events</p>
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/92672089_l-scaled.jpg" alt="Culture dataset">
          <a href="https://www.123rf.com/stock-photo/festival_culture_tradition.html" target="_blank">Search on 123RF</a>
        </div>
        <div class="dataset-box">
          <strong>Lifestyle</strong>
          <p>Shopping, Work, Family, Travel</p>
          <img src="https://fastmediahouse.com/wp-content/uploads/2025/06/82614142_l-scaled.jpg" alt="Lifestyle dataset">
          <a href="https://www.123rf.com/stock-photo/lifestyle_home_family.html" target="_blank">Search on 123RF</a>
        </div>
      </div>
    </section>

    <section class="section">
      <h2 style="text-align:center; margin-bottom:30px;">Getting Started is Simple</h2>
      <div class="steps">
        <div class="step-box"><strong>Step 1: Request Your Dataset</strong><p>Tell us what you’re building and what data you need.</p></div>
        <div class="step-box"><strong>Step 2: Define License Scope</strong><p>We’ll propose usage terms, timeline, and budget.</p></div>
        <div class="step-box"><strong>Step 3: Evaluate the Sample</strong><p>Receive your sample set within 3 working days.</p></div>
        <div class="step-box"><strong>Step 4: Final Delivery</strong><p>Get your full dataset securely via your preferred method.</p></div>
      </div>
    </section>

    <section class="section footer-cta">
      <p><strong>AI datasets are powered and delivered by 123RF, our long-term partner.</strong></p>
      <p>The Fast Media team is your local contact. We facilitate licensing, delivery, and direct access to 123RF’s engineering and data teams whenever needed.</p>
    </section>
    <?php
    return ob_get_clean();
});
