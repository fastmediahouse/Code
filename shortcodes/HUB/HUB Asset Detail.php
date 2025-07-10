add_shortcode('fastmedia_asset_detail', function () {
  if (!is_user_logged_in()) return '<p>Please <a href="/signin/">sign in</a> to view this page.</p>';

  $user_id = get_current_user_id();
  $attachment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  if (!$attachment_id || get_post_field('post_author', $attachment_id) != $user_id) return '<p>Asset not found.</p>';

  $image_url = wp_get_attachment_url($attachment_id);
  $meta = wp_get_attachment_metadata($attachment_id);
  $selected_labels = get_field('fastmedia_asset_labels', $attachment_id) ?: [];
  $is_brand_approved = get_field('fastmedia_brand_approved', $attachment_id);
  $activity_log = get_post_meta($attachment_id, 'fastmedia_activity_log');
  $label_map = [
    'ST' => 'Stock Image', 'UP' => 'User Upload', 'BR' => 'Brand Approved',
    'LO' => 'Logo', 'FI' => 'Final Approved', 'PH' => 'Photography',
    'VI' => 'Video', 'VC' => 'Vector', 'AI' => 'AI Generated'
  ];

  // 🔍 Detect and permanently set fixed labels
  $source = get_post_meta($attachment_id, 'source', true);
  if ($source === 'solwee' && !in_array('ST', $selected_labels)) {
    $selected_labels[] = 'ST';
    update_field('fastmedia_asset_labels', $selected_labels, $attachment_id);
  }
  if ($source !== 'solwee' && !in_array('UP', $selected_labels)) {
    $selected_labels[] = 'UP';
    update_field('fastmedia_asset_labels', $selected_labels, $attachment_id);
  }

  ob_start();
?>
<style>
  .label-icon-grid { display: flex; gap: 6px; flex-wrap: wrap; margin: 10px 0; }
  .label-icon { font-size: 12px; font-weight: bold; padding: 4px 8px; border-radius: 4px; color: white; cursor: pointer; }
  .label-ST { background: #0073aa; } .label-UP { background: #00a65a; } .label-BR { background: #000; }
  .label-LO { background: #ff7700; } .label-FI { background: #e6b800; } .label-PH { background: #008080; }
  .label-VI { background: #7a4dc9; } .label-VC { background: #c62828; } .label-AI { background: #444; }

  .dropdown-labels { position: relative; }
  .dropdown-labels button {
    padding: 6px 12px; border: 1px solid #ccc; background: #fff; border-radius: 4px;
    cursor: pointer; color: black; font-weight: 600;
  }
  .dropdown-labels .dropdown-content {
    display: none; position: absolute; top: 100%; left: 0;
    background: #fff; border: 1px solid #ccc; padding: 10px;
    z-index: 10; border-radius: 6px; min-width: 200px; color: black;
  }
  .dropdown-labels:hover .dropdown-content { display: block; }
  .dropdown-content label {
    display: flex; align-items: center; font-size: 14px; gap: 6px;
    margin-bottom: 6px; color: black;
  }

  .tool-tile-grid { display: grid; grid-template-columns: repeat(2, minmax(100px, 1fr)); gap: 10px; margin-top: 10px; margin-bottom: 5px; }
  .tool-tile-grid a {
    color: green; font-weight: bold; text-decoration: none;
    background: #f5f5f5; padding: 8px 10px; border-radius: 6px; text-align: center;
  }

  .readonly input, .readonly textarea, .readonly select { background: #f5f5f5; pointer-events: none; }

  .acf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
  .acf-grid label { font-weight: 600; font-size: 13px; margin-bottom: 4px; display: block; }
  .acf-grid input, .acf-grid textarea, .acf-grid select {
    width: 100%; padding: 6px 8px; border: 1px solid #ccc;
    border-radius: 4px; font-size: 14px; background: #f9f9f9;
  }

  #edit-toggle-btn { margin-top: 10px; padding: 6px 14px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; color: #000; font-weight: 600; }
  #save-acf-btn { margin-top: 15px; padding: 8px 20px; background: #0073aa; color: #fff; border: none; border-radius: 4px; display: none; }

  /* Fixed: Image number font size to match "Editing Tools" */
  .asset-detail-container .image-number { font-size: 24px !important; font-weight: bold !important; }
  .download-section h4 { font-size: 16px !important; margin-bottom: 10px !important; }

  .download-buttons { display: flex !important; flex-wrap: wrap !important; gap: 10px !important; margin-bottom: 15px !important; }
  .download-button {
    background: #0073aa !important; color: white !important; padding: 8px 12px !important; text-decoration: none !important;
    border-radius: 5px !important; font-weight: bold !important; display: inline-block !important;
  }

  .asset-detail-container .acf-grid button {
    font-size: 14px !important; background: #f5f5f5 !important; padding: 6px 12px !important; border-radius: 4px !important;
    border: 1px solid #ccc !important; font-weight: normal !important;
  }

  /* Fixed: Activity Log button minimized to simple title */
  .asset-detail-container .activity-log-title {
    font-size: 14px !important; font-weight: bold !important; margin-bottom: 8px !important; 
    color: #333 !important; cursor: pointer !important; text-decoration: underline !important;
  }
  .asset-detail-container .activity-log-title:hover {
    color: #0073aa !important;
  }

  .activity-log-list { font-size: 13px !important; padding-left: 18px !important; color: #444 !important; }
  .no-activity { font-size: 13px !important; color: #888 !important; margin-top: 5px !important; }

  /* Fixed: Added cursor pointer for action icons */
  .action-icon { 
    cursor: pointer !important; 
    padding: 4px 8px !important; 
    border-radius: 4px !important; 
    background: #f5f5f5 !important; 
    border: 1px solid #ddd !important;
  }
  .action-icon:hover { 
    background: #e0e0e0 !important; 
  }
</style>

<div class="asset-detail-container" style="display:flex; gap:40px; flex-wrap:wrap;">
  <div style="flex:1; min-width:300px;">
    <form method="post">
      <p><strong>Image Number:</strong> <span class="image-number"><?= esc_html(get_the_title($attachment_id)) ?></span></p>
      <img id="asset-img" src="<?= esc_url($image_url) ?>" style="width:100%; border-radius:10px;" alt="Preview" crossorigin="anonymous">

      <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div class="label-icon-grid">
          <?php foreach ($selected_labels as $code): ?>
            <div class="label-icon label-<?= esc_attr($code) ?>" title="<?= esc_attr($label_map[$code]) ?>"><?= esc_html($code) ?></div>
          <?php endforeach; ?>
        </div>

        <div class="dropdown-labels">
          <button type="button">Labels</button>
          <div class="dropdown-content">
            <?php foreach ($label_map as $code => $desc):
              $checked = in_array($code, $selected_labels) ? 'checked' : '';
              $disabled = in_array($code, ['ST', 'UP']) ? 'disabled' : '';
            ?>
              <label title="<?= esc_attr($desc) ?>">
                <input type="checkbox" name="acf_edit_labels[]" value="<?= esc_attr($code) ?>" <?= $checked ?> <?= $disabled ?>> <?= esc_html($code) ?>
              </label>
            <?php endforeach; ?>
            <button type="submit" name="save_acf_fields" style="margin-top:8px;">💾 Save</button>
          </div>
        </div>

        <?php if (!in_array('BR', $selected_labels)): ?>
          <button name="suggest_brand" value="1" style="padding:6px 10px; background:#000; color:white; border-radius:4px;">Suggest for Brand</button>
        <?php else: ?>
          <span style="padding:6px 10px; background:#999; color:white; border-radius:4px;">
            <?= $is_brand_approved ? '✅ Brand Approved' : '⏳ Pending Review' ?>
          </span>
        <?php endif; ?>

        <div class="action-icon" onclick="prompt('Share this link:', window.location.href)" title="Share this image">🔗</div>
        <div class="action-icon" onclick="launchAnnotation()" title="Annotate image">🖍️</div>
        <div class="action-icon" onclick="trashAsset(<?= $attachment_id ?>)" title="Delete this image">🗑️</div>
      </div>

      <?= fastmedia_project_toggle_ui($attachment_id) ?>

      <p style="margin-top:15px;"><strong>File Type:</strong> <?= esc_html($meta['mime_type'] ?? get_post_mime_type($attachment_id)) ?></p>
      <p><strong>Dimensions:</strong> <?= esc_html($meta['width'] ?? '') ?> × <?= esc_html($meta['height'] ?? '') ?> px</p>
      <p><strong>Date Uploaded:</strong> <?= esc_html(get_the_date('', $attachment_id)) ?></p>

      <div class="download-section">
        <h4>📥 Download Highres</h4>
        <div class="download-buttons">
          <?php
            $full_url = wp_get_attachment_url($attachment_id);
            $full_size = file_exists(get_attached_file($attachment_id)) ? filesize(get_attached_file($attachment_id)) / 1024 / 1024 : 0;
            $img_info = getimagesize(get_attached_file($attachment_id));
            echo '<a href="' . esc_url($full_url) . '" class="download-button" download>FULL — ' . $img_info[0] . '×' . $img_info[1] . ' px (' . round($full_size, 2) . ' MB)</a>';
          ?>
          <a href="#" class="download-button" onclick="alert('Resizing coming soon')">LARGE</a>
          <a href="#" class="download-button" onclick="alert('Resizing coming soon')">MEDIUM</a>
          <a href="#" class="download-button" onclick="alert('Resizing coming soon')">SMALL</a>
        </div>

        <div class="activity-log-container">
          <div class="activity-log-title" onclick="toggleActivityLog()">Activity Log</div>
          <div id="activity-log-content" style="display:none;">
            <?php if (!empty($activity_log)): ?>
              <ul class="activity-log-list">
                <?php foreach (array_reverse($activity_log) as $entry): ?>
                  <li><?= esc_html($entry) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <div class="no-activity">No activity yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div style="flex:1; min-width:300px;">
    <h3 style="margin-top:0px;">🖊 Editing Tools</h3>
    <div class="tool-tile-grid">
      <a href="https://pixlr.com/e/" target="_blank">Pixlr Editor</a>
      <a href="https://www.canva.com/" target="_blank">Canva</a>
      <a href="https://photopea.com/" target="_blank">Photopea</a>
      <a href="https://express.adobe.com/tools/image-resize" target="_blank">Adobe Express</a>
      <a href="https://www.photoshop.com/tools" target="_blank">Photoshop Web</a>
    </div>
    <p style="font-size:13px;color:#666;margin-top:5px;">Edit externally. Save and re-upload as new version.</p>

    <h3 style="margin-top:20px;">📋 Asset Metadata <button id="edit-toggle-btn">✏️ Edit Metadata</button></h3>
    <form method="post" id="acf-form" class="readonly">
      <details>
        <summary>📝 Content Metadata</summary>
        <div class="acf-grid">
          <?php
          $acf_content = ['title'=>'Title','caption'=>'Caption','tags'=>'Tags','creator'=>'Creator','location'=>'Location','notes'=>'Notes','collection'=>'Collection'];
          foreach ($acf_content as $field => $label):
            $val = get_field($field, $attachment_id);
            echo "<p><label>{$label}<input type='text' name='acf_edit[$field]' value='" . esc_attr($val) . "' title='Enter {$label}'></label></p>";
          endforeach;
          ?>
        </div>
      </details>

      <details>
        <summary>🔧 Technical Metadata</summary>
        <div class="acf-grid">
          <?php
          $acf_tech = ['imagereference'=>'Image Ref','secondary_id'=>'Secondary ID','ref_code'=>'Ref Code','capture_date'=>'Capture Date','camera_make'=>'Camera Make','camera_model'=>'Camera Model','software'=>'Software','color_space'=>'Color Space','file_size'=>'File Size','image_dimensions'=>'Image Dimensions','license_type'=>'License Type','license_summary'=>'License Summary','copyright'=>'Copyright','filename'=>'Filename','file_type'=>'File Type','edit_history'=>'Edit History'];
          foreach ($acf_tech as $field => $label):
            $val = get_field($field, $attachment_id);
            echo "<p><label>{$label}<input type='text' name='acf_edit[$field]' value='" . esc_attr($val) . "' title='Enter {$label}'></label></p>";
          endforeach;
          ?>
        </div>
      </details>

      <button type="submit" name="save_acf_fields" id="save-acf-btn">💾 Save Changes</button>
    </form>
  </div>
</div>

<script>
document.getElementById('edit-toggle-btn')?.addEventListener('click', function () {
  const form = document.getElementById('acf-form');
  const saveBtn = document.getElementById('save-acf-btn');
  form.classList.toggle('readonly');
  const editing = !form.classList.contains('readonly');
  saveBtn.style.display = editing ? 'inline-block' : 'none';
  this.textContent = editing ? '🔒 Cancel' : '✏️ Edit Metadata';
});

function toggleActivityLog() {
  const log = document.getElementById('activity-log-content');
  log.style.display = (log.style.display === 'none') ? 'block' : 'none';
}
</script>
<?php
  if (isset($_POST['save_acf_fields'])) {
    if (!empty($_POST['acf_edit'])) {
      foreach ($_POST['acf_edit'] as $field => $val) {
        update_field($field, sanitize_text_field($val), $attachment_id);
      }
    }
    if (isset($_POST['acf_edit_labels'])) {
      update_field('fastmedia_asset_labels', array_map('sanitize_text_field', $_POST['acf_edit_labels']), $attachment_id);
    } else {
      update_field('fastmedia_asset_labels', [], $attachment_id);
    }
    echo '<div style="margin-top:10px; color:green; font-weight:bold;">✅ Metadata saved successfully.</div>';
  }

  if (isset($_POST['suggest_brand'])) {
    if (!in_array('BR', $selected_labels)) {
      $selected_labels[] = 'BR';
      update_field('fastmedia_asset_labels', $selected_labels, $attachment_id);
      update_field('fastmedia_brand_approved', false, $attachment_id);
      echo '<div style="margin-top:10px; color:orange; font-weight:bold;">✅ Marked for brand approval.</div>';
    }
  }
  return ob_get_clean();
});
