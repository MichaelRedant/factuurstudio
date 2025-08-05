<?php
require_once dirname(__DIR__) . '/includes/Exporter.php';

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('⛔ Geen toegang');
}

$type = sanitize_text_field($_GET['type'] ?? '');
if (!in_array($type, ['verkoop', 'aankoop'])) {
    wp_die('⛔ Ongeldig type.');
}

$upload_dir = wp_upload_dir();
$batch_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
$zip_file = trailingslashit($upload_dir['basedir']) . "octopus-invoices/facturen-batch-{$type}.zip";

// ZIP aanmaken indien nodig
if (!file_exists($zip_file)) {
    Exporter::zip_last_batch($batch_dir, $zip_file);
}

if (file_exists($zip_file)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
    exit;
} else {
    wp_die('❌ ZIP-bestand niet gevonden.');
}
