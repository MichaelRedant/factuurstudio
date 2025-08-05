<?php
$verkoop_url = admin_url('admin-post.php?action=octopus_download_zip&type=verkoop');
$aankoop_url = admin_url('admin-post.php?action=octopus_download_zip&type=aankoop');
?>

<div class="zip-download">
    <h3>📦 ZIP-download van facturen</h3>
    <a href="<?php echo esc_url($verkoop_url); ?>" class="button button-primary">📦 Download Verkoopfacturen (ZIP)</a>
    <a href="<?php echo esc_url($aankoop_url); ?>" class="button">📦 Download Aankoopfacturen (ZIP)</a>
</div>
