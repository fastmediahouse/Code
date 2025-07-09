add_shortcode('fm_rotating_stacked_heading', function () {
    ob_start();
    ?>
    <div class="fm-slot-wrapper">
      <div class="fm-slot-container">
        <div class="fm-slot-track">
          <div>Discover</div>
          <div>License</div>
          <div>Manage</div>
          <div>Discuss</div>
          <div>Discover</div>
          <div>License</div>
          <div>Manage</div>
          <div>Discuss</div>
        </div>
      </div>
      <div class="fm-static-lines">
        Global<br>
        Content
      </div>
    </div>

    <style>
    .fm-slot-wrapper {
      font-family: "Roboto", sans-serif;
      font-weight: 800;
      font-size: 72px;
      color: #000;
      line-height: 1.15;
      text-align: left;
      margin-bottom: 20px;
    }

    .fm-slot-container {
      height: 1.2em;
      overflow: hidden;
      position: relative;
    }

    .fm-slot-track {
      display: flex;
      flex-direction: column;
      animation: scrollLoop 10s linear infinite;
    }

    .fm-slot-track > div {
      height: 1.2em;
      display: flex;
      align-items: center;
    }

    @keyframes scrollLoop {
      0%   { transform: translateY(0%); }
      100% { transform: translateY(-50%); }
    }

    @media (max-width: 768px) {
      .fm-slot-wrapper {
        font-size: 48px;
      }
    }
    </style>
    <?php
    return ob_get_clean();
});
