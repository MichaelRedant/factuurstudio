<?php
// /includes/export-handler.php
require_once plugin_dir_path(__FILE__) . '/logger.php';


add_action('admin_post_octopus_download_zip', function () {
    if (!current_user_can('manage_options')) {
        wp_die('⛔ Geen toegang');
    }

    $type = sanitize_text_field($_GET['type'] ?? 'verkoop');
    $allowed = ['verkoop', 'aankoop'];
    if (!in_array($type, $allowed)) {
        wp_die('⛔ Ongeldig type');
    }

    require_once plugin_dir_path(__FILE__) . '/Exporter.php';

    $upload_dir = wp_upload_dir();
    $batch_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
    $zip_file = trailingslashit($upload_dir['basedir']) . "octopus-invoices/facturen-batch-{$type}.zip";

    // Check of er iets is
    $files = glob($batch_dir . '*.{pdf,xml}', GLOB_BRACE);
    if (empty($files)) {
        wp_die('ℹ️ Geen bestanden gevonden. Genereer eerst facturen.');
    }

    // ZIP aanmaken
    if (!Exporter::zip_last_batch($batch_dir, $zip_file, true)) {
        wp_die('❌ Fout bij ZIP-aanmaak.');
    }

    // Forceer download
    if (file_exists($zip_file)) {
    header('Content-Type: application/zip');
    header('Content-Transfer-Encoding: Binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');
    header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
    header('Content-Length: ' . filesize($zip_file));
    ob_clean();
    flush();
    readfile($zip_file);
    exit;
} else {
        wp_die('❌ ZIP-bestand niet gevonden.');
    }
});


add_action('admin_post_octopus_mail_zip', function () {
    if (!current_user_can('manage_options')) {
        wp_die('⛔ Geen toegang.');
    }

    check_admin_referer('octopus_mail_zip');

    if (!empty($_POST['website'])) {
        wp_die('🕵️‍♂️ Spam gedetecteerd.');
    }

    $email = sanitize_email($_POST['email_to'] ?? '');
    $types = array_map('sanitize_text_field', $_POST['invoice_types'] ?? []);

    if (!is_email($email) || empty($types)) {
        wp_redirect(admin_url('admin.php?page=facturatiegenerator&mail_error=invalid_input'));
        exit;
    }

    require_once plugin_dir_path(__FILE__) . '/Exporter.php';
    $upload_dir = wp_upload_dir();
    $generated_links = [];
    $linked_files = [];

    foreach ($types as $type) {
        $batch_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
        $zip_file = trailingslashit($upload_dir['basedir']) . "octopus-invoices/facturen-{$type}.zip";

        $files = glob($batch_dir . '*.{pdf,xml}', GLOB_BRACE);
        if (empty($files)) continue;

        if (!Exporter::zip_last_batch($batch_dir, $zip_file)) continue;

        $url = $upload_dir['baseurl'] . "/octopus-invoices/facturen-{$type}.zip";
        $generated_links[] = "- " . ucfirst($type) . ": $url";
        $linked_files[] = basename($zip_file);
    }

    if (empty($generated_links)) {
        wp_redirect(admin_url('admin.php?page=facturatiegenerator&mail_error=no_valid_zip'));
        exit;
    }

    $body = "Beste,\n\nJe kan de gegenereerde facturen downloaden via onderstaande link(s):\n\n" .
        implode("\n", $generated_links) .
        "\n\nMet vriendelijke groet,\nOctopus Facturatiegenerator";

    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $sent = wp_mail($email, 'Octopus ZIP-facturen', $body, $headers);

    require_once plugin_dir_path(__FILE__) . '/logger.php';
    OctopusEmailLogger::log([
        'status' => $sent ? '✅ SUCCES' : '❌ FAIL',
        'to' => $email,
        'files' => $linked_files,
        'type' => implode(', ', $types),
        'mode' => 'zip_batch'
    ]);

    wp_redirect(admin_url('admin.php?page=facturatiegenerator&' . ($sent ? 'mail_ok=1' : 'mail_error=mail_failed') . '&email=' . urlencode($email)));
    exit;
});


add_action('admin_post_octopus_mail_selected_xml', function () {
    if (!current_user_can('manage_options')) {
        wp_die('⛔ Geen toegang.');
    }

    check_admin_referer('octopus_mail_xml_by_selection');
    if (!empty($_POST['website'])) {
        wp_die('🕵️‍♂️ Spam gedetecteerd.');
    }

    $email = sanitize_email($_POST['email_to_xml'] ?? '');
    $selected = $_POST['selected_pdfs'] ?? [];

    if (!is_email($email) || empty($selected)) {
        wp_redirect(admin_url('admin.php?page=facturatiegenerator&mail_error=invalid_selection'));
        exit;
    }

    require_once plugin_dir_path(__FILE__) . '/logger.php';
    $upload_dir = wp_upload_dir();
    $sent_count = 0;

    foreach ($selected as $pdf_file) {
        $factuurnummer = basename(sanitize_file_name($pdf_file), '.pdf');

        foreach (['verkoop', 'aankoop'] as $type) {
            $xml_path = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/{$factuurnummer}.xml";
            if (!file_exists($xml_path)) continue;

            $subject = "XML-factuur: {$factuurnummer}.xml";
            $body = "Beste,\n\nIn de bijlage vind je het XML-bestand van factuur {$factuurnummer} (type: {$type}).\n\nMet vriendelijke groet,\nOctopus Facturatiegenerator";
            $headers = ['Content-Type: text/plain; charset=UTF-8'];

            $sent = wp_mail($email, $subject, $body, $headers, [$xml_path]);

            OctopusEmailLogger::log([
                'status' => $sent ? '✅ SUCCES' : '❌ FAIL',
                'to' => $email,
                'files' => [basename($xml_path)],
                'type' => $type,
                'mode' => 'xml_selections'
            ]);

            if ($sent) $sent_count++;
            break; // Alleen eerste geldige match
        }
    }

    $redirect = $sent_count > 0
        ? 'mail_ok=1&email=' . urlencode($email) . '&count=' . $sent_count
        : 'mail_error=mail_failed';

    wp_redirect(admin_url('admin.php?page=facturatiegenerator&' . $redirect));
    exit;
});



add_action('admin_post_octopus_delete_selected_files', function () {
    if (!current_user_can('manage_options')) {
        wp_die('⛔ Geen toegang');
    }

    check_admin_referer('octopus_delete_selected_files');

    // Honeypot check
    if (!empty($_POST['website'])) {
        wp_die('🕵️‍♂️ Spam gedetecteerd.');
    }

    $pdfs = $_POST['delete_pdfs'] ?? [];
    if (empty($pdfs) || !is_array($pdfs)) {
        wp_redirect(admin_url('admin.php?page=facturatiegenerator&deleted=0'));
        exit;
    }

    $upload_dir = wp_upload_dir();
    $deleted = 0;

    foreach ($pdfs as $filename) {
        $filename = basename(sanitize_file_name($filename));

        foreach (['verkoop', 'aankoop'] as $type) {
            $dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
            $pdf_path = $dir . $filename;
            $xml_path = preg_replace('/\.pdf$/', '.xml', $pdf_path);

            if (file_exists($pdf_path)) {
                unlink($pdf_path);
                $deleted++;
            }
            if (file_exists($xml_path)) {
                unlink($xml_path);
            }
        }
    }

    wp_redirect(admin_url('admin.php?page=facturatiegenerator&deleted=' . $deleted));
    exit;
});
