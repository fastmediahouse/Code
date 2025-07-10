// ✅ HUB Asset Detail View – Updated with Font Fixes, Icon Cursors, Collapsible Log
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
  .image-number, h3 { font-size: 18px; }
  .action-icon { cursor: pointer; font-size: 18px; margin-left: 10px; }
  .activity-log-button { font-size: 16px; font-weight: bold; cursor: pointer; color: #0073aa; text-decoration: underline; background: none; border: none; padding: 0; }
  .activity-log-list { font-size: 13px; margin-top: 5px; padding-left: 20px; }
</style>
<div class="asset-detail-container" style="display:flex; gap:40px; flex-wrap:wrap;">
  <div style="flex:1; min-width:300px;">
    <form method="post">
      <p><strong>Image Number:</strong> <span class="image-number"><?= esc_html(get_the_title($attachment_id)) ?></span></p>
      <img id="asset-img" src="<?= esc_url($image_url) ?>" style="width:100%; border-radius:10px;" alt="Preview" crossorigin="anonymous">
      <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <div class="action-icon" onclick="prompt('Share this link:', window.location.href)">🔗</div>
        <div class="action-icon" onclick="launchAnnotation()">🖍️</div>
        <div class="action-icon" onclick="if(confirm('Are you sure?')) alert('Delete logic pending');">🗑️</div>
      </div>

      <div style="margin-top:15px;">
        <button type="button" class="activity-log-button" onclick="toggleActivityLog()">Activity Log</button>
        <div id="activity-log-content" style="display:none;">
          <?php if (!empty($activity_log)): ?>
            <ul class="activity-log-list">
              <?php foreach (array_reverse($activity_log) as $entry): ?>
                <li><?= esc_html($entry) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="activity-log-list">No activity yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
function toggleActivityLog() {
  const log = document.getElementById('activity-log-content');
  log.style.display = (log.style.display === 'none') ? 'block' : 'none';
}
</script>
<?php
  return ob_get_clean();
});
