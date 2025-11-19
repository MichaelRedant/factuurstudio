<?php
require_once plugin_dir_path(__DIR__) . '/../i18n.php';
$verkoop_url = admin_url('admin-post.php?action=octopus_download_zip&type=verkoop');
$aankoop_url = admin_url('admin-post.php?action=octopus_download_zip&type=aankoop');
?>

<div class="zip-download">
    <h3><?php echo esc_html(octo_t('ZIP-download van facturen','Téléchargement ZIP des factures')); ?></h3>
    <a href="<?php echo esc_url($verkoop_url); ?>" class="button button-primary"><?php echo esc_html(octo_t('Download Verkoopfacturen (ZIP)','Télécharger factures de vente (ZIP)')); ?></a>
    <a href="<?php echo esc_url($aankoop_url); ?>" class="button"><?php echo esc_html(octo_t('Download Aankoopfacturen (ZIP)','Télécharger factures d\'achat (ZIP)')); ?></a>
</div>

