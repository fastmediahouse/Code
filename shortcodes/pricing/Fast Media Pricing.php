add_shortcode('solwee_price_calculator', function () {
    ob_start();
    ?>
    <style>
    .price-calc-container { max-width: 800px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #f9f9f9; }
    .price-calc-container h2 { margin-top: 0; }
    .price-calc-row { margin-bottom: 15px; }
    .price-calc-label { display: block; font-weight: 600; margin-bottom: 5px; }
    select { padding: 6px; width: 100%; }
    input[type="checkbox"] { vertical-align: middle; margin-right: 6px; }
    .price-output { background: #fff; padding: 15px; border-radius: 8px; font-size: 18px; margin-top: 20px; }
    .modifiers-wrap { margin-top: 10px; }
    .modifiers-wrap label { display: block; margin-top: 6px; }
    .price-note { font-size: 14px; color: #555; margin-top: 10px; font-style: italic; display: none; }
    .clear-btn { margin-top: 10px; background: #eee; border: 1px solid #ccc; padding: 6px 12px; border-radius: 6px; cursor: pointer; display: inline-block; }
    </style>

    <div class="price-calc-container">
        <h2>Image Licensing Calculator</h2>

        <div class="price-calc-row">
            <label class="price-calc-label">Category</label>
            <select id="cat-select">
                <option value="">-- Select Category --</option>
                <option value="pricing_brochures">Brochures</option>
                <option value="pricing_books">Books</option>
                <option value="pricing_newspapers">Newspapers</option>
                <option value="pricing_magazines">Magazines</option>
                <option value="pricing_retail">Retail</option>
                <option value="pricing_online">Online</option>
                <option value="pricing_advertising">Advertising</option>
                <option value="pricing_outdoor">Outdoor</option>
                <option value="pricing_tv">TV</option>
                <option value="pricing_press_release">Press Release</option>
            </select>
        </div>

        <div class="price-calc-row">
            <label class="price-calc-label">Usage Type / Size</label>
            <select id="usage-select"></select>
        </div>

        <div class="price-calc-row">
            <label class="price-calc-label">Print Run / Duration</label>
            <select id="print-select"></select>
        </div>

        <div class="price-calc-row modifiers-wrap">
            <label class="price-calc-label">Universal Modifiers</label>
            <label><input type="checkbox" value="2" class="mod"> Cover +100%</label>
            <label><input type="checkbox" value="1.5" class="mod"> Rear +50%</label>
            <label><input type="checkbox" value="0.5" class="mod"> Re-use from earlier license –50%</label>
            <label><input type="checkbox" value="1.5" class="mod"> Extra licensing period +50%</label>
        </div>

        <div class="price-calc-row modifiers-wrap">
            <label class="price-calc-label">Territory Modifiers</label>
            <label><input type="checkbox" value="1.1" class="region"> +1 Country (10%)</label>
            <label><input type="checkbox" value="1.25" class="region"> Benelux or Scandinavia (25%)</label>
            <label><input type="checkbox" value="1.5" class="region"> Europe / LatAm / Asia (50%)</label>
            <label><input type="checkbox" value="2" class="region"> World / Works (100%)</label>
        </div>

        <div id="category-modifiers" class="price-calc-row modifiers-wrap"></div>

        <div>
            <span class="clear-btn" id="clear-modifiers">Clear All Modifiers</span>
        </div>

        <div class="price-output" id="final-price">Select options to calculate price</div>
        <div id="category-note" class="price-note"></div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const catSelect = document.getElementById('cat-select');
        const usageSelect = document.getElementById('usage-select');
        const printSelect = document.getElementById('print-select');
        const output = document.getElementById('final-price');
        const noteBox = document.getElementById('category-note');
        const categoryModifiers = document.getElementById('category-modifiers');
        const clearBtn = document.getElementById('clear-modifiers');
        let PRICING = {};

        const categoryNotes = {
            'pricing_brochures': 'Duration: 3 Months.',
            'pricing_books': 'Includes booklets and programs. Includes Maps. Duration: 5 years.',
            'pricing_newspapers': 'Duration: 1 year. Use in social media: See Social media pricing.',
            'pricing_magazines': 'Magazines, Industry and special interest. Duration: 3 years.',
            'pricing_retail': '3-year rights. For example: Poster, Textile deco, Puzzle, Game, Sticker.',
            'pricing_online': 'Online magazines and publications.',
            'pricing_advertising': '',
            'pricing_outdoor': 'Outdoor / POS / Abri. City poster: 10 days. Wall: 1 month. POS: 3 months.',
            'pricing_tv': '',
            'pricing_press_release': ''
        };

        const extraModifiers = {
            'pricing_brochures': [{ label: 'Travel –50%', value: 0.5 }],
            'pricing_books': [
                { label: 'Reprint as eBook +50%', value: 1.5 },
                { label: 'Combined license –50%', value: 0.5 },
                { label: 'Back cover +80%', value: 1.8 },
                { label: 'Small image as main +100%', value: 2 }
            ],
            'pricing_magazines': [{ label: 'Small image as main +100%', value: 2 }],
            'pricing_online': [
                { label: 'Blogs / Intranet –20%', value: 0.8 },
                { label: 'Renewal +50%', value: 1.5 },
                { label: 'Multi-domain: 2–5 sites +25%', value: 1.25 },
                { label: '6–10 sites +35%', value: 1.35 },
                { label: '11–20 sites +50%', value: 1.5 }
            ],
            'pricing_advertising': [
                { label: '4–7 media +25%', value: 1.25 },
                { label: '9–12 media +35%', value: 1.35 },
                { label: '13–22 media +50%', value: 1.5 },
                { label: 'Back cover +50%', value: 1.5 }
            ],
            'pricing_outdoor': [
                { label: '2–4 months +50%', value: 1.5 },
                { label: '5–9 months +100%', value: 2 },
                { label: 'Over 10 months +200%', value: 3 },
                { label: 'Projections –50%', value: 0.5 },
                { label: 'Editorial –70%', value: 0.3 }
            ],
            'pricing_retail': [{ label: 'Extra image –50%', value: 0.5 }]
        };

        function updateCategoryNote(slug) {
            const note = categoryNotes[slug] || '';
            noteBox.innerHTML = note ? `<i>${note}</i>` : '';
            noteBox.style.display = note ? 'block' : 'none';
        }

        function updateCategoryModifiers(slug) {
            const modifiers = extraModifiers[slug] || [];
            categoryModifiers.innerHTML = '';
            if (!modifiers.length) {
                categoryModifiers.style.display = 'none';
                return;
            }
            categoryModifiers.style.display = 'block';
            const label = document.createElement('label');
            label.className = 'price-calc-label';
            label.textContent = 'Category-Specific Modifiers';
            categoryModifiers.appendChild(label);
            modifiers.forEach(mod => {
                const line = document.createElement('label');
                line.innerHTML = `<input type="checkbox" value="${mod.value}" class="mod"> ${mod.label}`;
                categoryModifiers.appendChild(line);
            });
        }

        function populateUsage() {
            usageSelect.innerHTML = '';
            printSelect.innerHTML = '';
            const usages = Object.keys(PRICING);
            usages.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u;
                opt.textContent = u;
                usageSelect.appendChild(opt);
            });
            if (usages.length) populatePrint(usages[0]);
        }

        function populatePrint(usage) {
            printSelect.innerHTML = '';
            const entries = PRICING[usage] || [];
            entries.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.price;
                opt.textContent = e.print_run;
                printSelect.appendChild(opt);
            });
            updatePrice();
        }

        function updatePrice() {
            let base = parseFloat(printSelect.value);
            if (isNaN(base)) return output.innerHTML = '<b>📩 Please contact us for a quote</b>';
            let total = base;
            document.querySelectorAll('.mod:checked').forEach(cb => total *= parseFloat(cb.value));
            document.querySelectorAll('.region:checked').forEach(cb => total *= parseFloat(cb.value));
            output.innerHTML = `<b>Estimated Price: €${total.toFixed(2)}</b>`;
        }

        catSelect.onchange = () => {
            const slug = catSelect.value;
            if (!slug) return;
            updateCategoryNote(slug);
            updateCategoryModifiers(slug);
            fetch(`/wp-admin/admin-ajax.php?action=solwee_get_pricing&file=${slug}`)
                .then(res => res.json())
                .then(data => {
                    PRICING = data;
                    populateUsage();
                })
                .catch(err => console.error('Failed to load pricing data:', err));
        };

        usageSelect.onchange = () => populatePrint(usageSelect.value);
        printSelect.onchange = updatePrice;
        document.addEventListener('change', function (e) {
            if (e.target.matches('.mod, .region')) updatePrice();
        });

        clearBtn.addEventListener('click', function () {
            document.querySelectorAll('.mod:checked, .region:checked').forEach(cb => cb.checked = false);
            updatePrice();
        });
    });
    </script>
    <?php
    return ob_get_clean();
});

add_action('wp_ajax_solwee_get_pricing', function () {
    $file = sanitize_file_name($_GET['file'] ?? '');
    $base = ABSPATH . 'wp-content/uploads/';
    $path = realpath($base . $file . '.php');
    if (!$path || strpos($path, $base) !== 0 || !file_exists($path)) {
        wp_send_json([]);
    }
    $data = include $path;
    wp_send_json($data);
});

add_action('wp_ajax_nopriv_solwee_get_pricing', function () {
    do_action('wp_ajax_solwee_get_pricing');
});
