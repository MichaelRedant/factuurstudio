<?php
require_once plugin_dir_path(__DIR__) . '/../i18n.php';
$upload_dir = wp_upload_dir();
$pdfs = array_merge(
    glob($upload_dir['basedir'] . '/octopus-invoices/verkoop/*.pdf') ?: [],
    glob($upload_dir['basedir'] . '/octopus-invoices/aankoop/*.pdf') ?: []
);

if (empty($pdfs)) {
    echo '<p><em>' . esc_html(octo_t('Geen facturen gevonden om te verwijderen.','Aucune facture à supprimer.')) . '</em></p>';
    return;
}
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('<?php echo esc_js(octo_t('Ben je zeker dat je deze facturen wil verwijderen?','Êtes-vous sûr de vouloir supprimer ces factures ?')); ?>');">
    <?php wp_nonce_field('octopus_delete_selected_files'); ?>
    <input type="hidden" name="action" value="octopus_delete_selected_files">

    <h3><?php echo esc_html(octo_t('Selecteer facturen om te verwijderen','Sélectionnez des factures à supprimer')); ?></h3>

    <ul>
        <?php foreach ($pdfs as $path): 
            $filename = basename($path); ?>
            <li>
                <label>
                    <input type="checkbox" name="delete_pdfs[]" value="<?php echo esc_attr($filename); ?>">
                    <?php echo esc_html($filename); ?>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Honeypot -->
    <input type="text" name="website" style="display:none;" autocomplete="off">

    <p><button type="submit" class="button button-secondary"><?php echo esc_html(octo_t('Verwijder geselecteerde facturen','Supprimer les factures sélectionnées')); ?></button></p>
</form>

