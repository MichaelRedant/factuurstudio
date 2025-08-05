<?php
$upload_dir = wp_upload_dir();
$pdfs = array_merge(
    glob($upload_dir['basedir'] . '/octopus-invoices/verkoop/*.pdf') ?: [],
    glob($upload_dir['basedir'] . '/octopus-invoices/aankoop/*.pdf') ?: []
);

if (empty($pdfs)) {
    echo '<p><em>Geen facturen gevonden om te verwijderen.</em></p>';
    return;
}
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Ben je zeker dat je deze facturen wil verwijderen?');">
    <?php wp_nonce_field('octopus_delete_selected_files'); ?>
    <input type="hidden" name="action" value="octopus_delete_selected_files">

    <h3>🗑️ Selecteer facturen om te verwijderen</h3>

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

    <p><button type="submit" class="button button-secondary">🗑️ Verwijder geselecteerde facturen</button></p>
</form>
