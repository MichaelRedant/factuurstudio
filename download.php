<?php
// 🔒 Beveiliging & toegang
require_once('../../../wp-load.php');
if (!current_user_can('manage_options')) {
    wp_die('⛔ Geen toegang.');
}

$type = $_GET['type'] ?? 'verkoop';
$allowed = ['verkoop', 'aankoop'];
if (!in_array($type, $allowed)) {
    wp_die('⛔ Ongeldig type.');
}

$upload_dir = wp_upload_dir();
$batch_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
$zip_file = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/facturen-batch.zip';

require_once __DIR__ . '/includes/Exporter.php';

// 📂 Check of map bestaat en niet leeg is
if (!is_dir($batch_dir) || count(glob($batch_dir . '*.{pdf,xml}', GLOB_BRACE)) === 0) {
    wp_die('ℹ️ Er zijn nog geen gegenereerde facturen om te exporteren. Gelieve eerst facturen te genereren.');
}

// 🗜️ ZIP maken
if (!Exporter::zip_last_batch($batch_dir, $zip_file)) {
    wp_die('❌ Fout bij het aanmaken van het ZIP-bestand.');
}

// 📥 Download starten
if (file_exists($zip_file)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="facturen-batch.zip"');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
    exit;
} else {
    wp_die('❌ ZIP-bestand niet gevonden.');
}
