/* ✅ Fix Elementor mobile overflow caused by full-width + padding */
html, body {
  max-width: 100vw;
  overflow-x: clip;
}

/* Force full-width sections to respect viewport width */
.elementor-section.elementor-section-full_width {
  width: 100% !important;
  box-sizing: border-box !important;
  padding-left: 0 !important;
  padding-right: 0 !important;
}

/* ✅ Add safe inner padding for classic containers */
@media (max-width: 767px) {
  .elementor-section.elementor-section-full_width > .elementor-container {
    padding-left: 15px;
    padding-right: 15px;
  }
}

/* ✅ Add safe inner padding for Flexbox containers via CSS vars */
@media (max-width: 767px) {
  .elementor-section.elementor-section-full_width > .e-con {
    --padding-left: 15px !important;
    --padding-right: 15px !important;
  }
}
