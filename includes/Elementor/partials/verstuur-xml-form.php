<?php
$upload_dir = wp_upload_dir();
$verkoop_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/verkoop/";
$aankoop_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/aankoop/";

$pdfs = array_merge(
    glob($verkoop_dir . '*.pdf') ?: [],
    glob($aankoop_dir . '*.pdf') ?: []
);

if (empty($pdfs)) {
    echo '<p><em>Geen facturen beschikbaar.</em></p>';
    return;
}
?>

<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('octopus_mail_xml_by_selection'); ?>
    <input type="hidden" name="action" value="octopus_mail_selected_xml">

    <h3>📤 Selecteer facturen om XML te verzenden</h3>

    <ul>
        <?php foreach ($pdfs as $path): 
            $filename = basename($path); ?>
            <li>
                <label>
                    <input type="checkbox" name="selected_pdfs[]" value="<?php echo esc_attr($filename); ?>">
                    <?php echo esc_html($filename); ?>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>

    <p>
        <label for="email_to_xml">Ontvanger (bv. U12345678@in.octopus.be):</label><br>
        <input type="email" name="email_to_xml" id="email_to_xml" required class="regular-text" />
    </p>

    <!-- Honeypot -->
    <input type="text" name="website" style="display:none;" autocomplete="off">

    <p><button type="submit" class="button button-primary">📤 Verstuur geselecteerde XML-bestanden</button></p>
</form>
