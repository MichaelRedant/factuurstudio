<?php
/**
 * Plugin Name: Factuur Studio
 * Description: Genereer snel aankoop- of verkoopfacturen (PDF + UBL), met correcte btw-nummers en anonieme optie.
 * Version: 1.8
 * Author: Michaël Redant
 */

defined('ABSPATH') or die('No script kiddies please!');

require_once plugin_dir_path(__FILE__) . 'includes/FactuurGenerator.php';
require_once plugin_dir_path(__FILE__) . 'includes/UBLGenerator.php';
require_once plugin_dir_path(__FILE__) . 'includes/export-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/i18n.php';

// Admin menu
// Admin menu
add_action('admin_menu', function () {
    // Hoofdmenu met icon
    add_menu_page(
        'Factuur Studio',
        'Factuur Studio',
        'manage_options',
        'facturatiegenerator',
        'octopus_facturatie_settings_page',
        'dashicons-media-spreadsheet',
        26
    );

    // Submenu: hoofdfunctionaliteit
    add_submenu_page(
        'facturatiegenerator',
        'Factuur Studio',
        'Facturatiegenerator',
        'manage_options',
        'facturatiegenerator',
        'octopus_facturatie_settings_page'
    );

    // Submenu: E-mail log
     add_submenu_page(
        'facturatiegenerator',
        'E-mailverzendlog',
        'E-mailverzendlog',
        'manage_options',
        'facturatiegenerator_logs',
        function () {
            include plugin_dir_path(__FILE__) . 'admin/logs-page.php';
        }
    );

    // Submenu: Over deze plugin
    add_submenu_page(
        'facturatiegenerator',
        'Over deze plugin',
        'Over',
        'manage_options',
        'octopus_over_plugin',
        'octopus_over_plugin_page'
    );
});

// Elementor integratie
add_action('elementor/elements/categories_registered', function($elements_manager) {
    $elements_manager->add_category(
        'factuurstudio',
        [
            'title' => 'Factuur Studio',
            'icon' => 'fa fa-file-invoice'
        ]
    );
});

add_action('elementor/widgets/widgets_registered', function($widgets_manager) {
    $base = plugin_dir_path(__FILE__) . 'includes/Elementor/';

    require_once $base . 'OctopusGenerateWidget.php';
    require_once $base . 'OctopusFacturenWidget.php';
    require_once $base . 'OctopusVerstuurXmlWidget.php';
    require_once $base . 'OctopusVerwijderFacturenWidget.php';
    require_once $base . 'OctopusDownloadZipsWidget.php';

    $widgets_manager->register(new \OctopusGenerateWidget());
    $widgets_manager->register(new \OctopusFacturenWidget());
    $widgets_manager->register(new \OctopusVerstuurXmlWidget());
    $widgets_manager->register(new \OctopusVerwijderFacturenWidget());
    $widgets_manager->register(new \OctopusDownloadZipsWidget());
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'factuurstudio-css',
        plugin_dir_url(__FILE__) . 'assets/css/factuurstudio.css',
        [],
        '1.0.0'
    );
});

add_action('elementor/frontend/after_enqueue_styles', function () {
    wp_enqueue_style(
        'factuurstudio-css',
        plugin_dir_url(__FILE__) . 'assets/css/factuurstudio.css',
        [],
        '1.0.0'
    );
});

// Front-end FR output filter when URL contains /fr/
add_action('template_redirect', function () {
    if (!function_exists('octo_is_fr_locale') || !octo_is_fr_locale()) return;
    ob_start(function ($html) {
        $map = [
            'Genereer Facturen' => 'Générer des factures',
            'Simuleer hier verkoop- of aankoopfacturen' => 'Simulez ici des factures de vente ou d\'achat',
            'Verkoopfacturen' => 'Factures de vente',
            'Aankoopfacturen' => 'Factures d\'achat',
            'Aantal per type:' => 'Nombre par type :',
            'Datum bereik:' => 'Plage de dates :',
            'tot' => 'à',
            'Klant- of Leveranciersgegevens' => 'Données client ou fournisseur',
            'Branche:' => 'Branche :',
            '-- Kies een branche --' => '-- Choisissez une branche --',
            'Naam:' => 'Nom :',
            'Adres:' => 'Adresse :',
            'BTW-nummer:' => 'Numéro de TVA :',
            'Start genereren' => 'Démarrer la génération',
            'Genereer zonder klantgegevens' => 'Générer sans données client',
            'Soort facturen' => 'Type de factures',
            'Extra opties' => 'Options supplémentaires',
            'Bedrijfsnaam' => 'Nom de l\'entreprise',
            'Branche' => 'Branche',
            'Er zijn nog geen facturen gegenereerd.' => 'Aucune facture n\'a encore été générée.',
            'Factuur' => 'Facture',
            'Klant' => 'Client',
            'Leverancier' => 'Fournisseur',
            'Datum' => 'Date',
            'Onbekend' => 'Inconnu',
            'ZIP-download van facturen' => 'Téléchargement ZIP des factures',
            'Download Verkoopfacturen (ZIP)' => 'Télécharger factures de vente (ZIP)',
            'Download Aankoopfacturen (ZIP)' => 'Télécharger factures d\'achat (ZIP)',
            'Geen bestanden gevonden om te downloaden.' => 'Aucun fichier trouvé à télécharger.',
            'Selecteer facturen om XML te verzenden' => 'Sélectionnez des factures à envoyer en XML',
            'Ontvanger (bv. U12345678@in.octopus.be):' => 'Destinataire (ex. U12345678@in.octopus.be) :',
            'Verstuur geselecteerde XML-bestanden' => 'Envoyer les fichiers XML sélectionnés',
            'Geen facturen beschikbaar.' => 'Aucune facture disponible.',
            'Selecteer facturen om te verwijderen' => 'Sélectionnez des factures à supprimer',
            'Verwijder geselecteerde facturen' => 'Supprimer les factures sélectionnées',
            'Geen facturen gevonden om te verwijderen.' => 'Aucune facture à supprimer.',
            'Ben je zeker dat je deze facturen wil verwijderen?' => 'Êtes-vous sûr de vouloir supprimer ces factures ?',
        ];
        return str_replace(array_keys($map), array_values($map), $html);
    });
});



// Verwerk frontend formulier inzendingen
add_action('admin_post_octopus_generate_facturen_frontend', 'octopus_handle_generate_frontend');
add_action('admin_post_nopriv_octopus_generate_facturen_frontend', 'octopus_handle_generate_frontend');

function octopus_handle_generate_frontend() {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'octopus_generate_invoices')) {
        wp_die('Ongeldige aanvraag (nonce).');
    }

    // Zorg dat de backendklasse beschikbaar is
    require_once plugin_dir_path(__FILE__) . 'includes/FactuurGenerator.php';

    $generator = new FactuurGenerator($_POST);
    $result = $generator->generate();

    if ($result === true) {
        wp_redirect(add_query_arg('factuur_success', 1, wp_get_referer()));
    } else {
        wp_redirect(add_query_arg('factuur_error', urlencode($result), wp_get_referer()));
    }

    exit;
}

add_action('admin_post_octopus_delete_selected_files', 'octopus_handle_delete_files');
add_action('admin_post_nopriv_octopus_delete_selected_files', 'octopus_handle_delete_files');

function octopus_handle_delete_files() {
    if (!current_user_can('manage_options') || !check_admin_referer('octopus_delete_selected_files')) {
        wp_die('Geen toestemming of ongeldige aanvraag.');
    }

    $files = $_POST['selected_pdfs'] ?? $_POST['delete_pdfs'] ?? [];

    $upload_dir = wp_upload_dir();
    $deleted = 0;

    foreach ($files as $filename) {
        foreach (['verkoop', 'aankoop'] as $type) {
            $base = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
            foreach (['pdf', 'xml', 'json'] as $ext) {
                $path = $base . basename($filename, '.pdf') . '.' . $ext;
                if (file_exists($path)) {
                    unlink($path);
                    $deleted++;
                }
            }
        }
    }

    $redirect = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : wp_get_referer();
$redirect = add_query_arg('deleted', $deleted, $redirect);
wp_safe_redirect($redirect);
exit;

}

add_action('admin_post_octopus_mail_selected_xml', 'octopus_handle_mail_selected_xml');
add_action('admin_post_nopriv_octopus_mail_selected_xml', 'octopus_handle_mail_selected_xml');

function octopus_handle_mail_selected_xml() {
    if (!current_user_can('manage_options') || !check_admin_referer('octopus_mail_xml_by_selection')) {
        wp_die('Geen toegang of sessie verlopen.');
    }

    $files = $_POST['selected_pdfs'] ?? [];
    $email = sanitize_email($_POST['email_to_xml'] ?? '');
    $redirect = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : wp_get_referer();

    if (empty($files) || empty($email)) {
        wp_safe_redirect(add_query_arg('xml_error', 1, $redirect));
        exit;
    }

    $upload_dir = wp_upload_dir();
    $sent = 0;

    foreach ($files as $filename) {
        $type = strpos($filename, 'V') === 0 ? 'verkoop' : 'aankoop';
        $base = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
        $xml_path = $base . basename($filename, '.pdf') . '.xml';

        if (!file_exists($xml_path)) continue;

        $attachments = [$xml_path];
        $subject = "XML Factuur - {$filename}";
        $body = "In de bijlage vind je het XML-bestand van de factuur: {$filename}";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        if (wp_mail($email, $subject, $body, $headers, $attachments)) {
            $sent++;
        }
    }

    wp_safe_redirect(add_query_arg('xml_sent', $sent, $redirect));
    exit;
}

//ajax frontend
add_action('wp_ajax_octopus_delete_selected_files', 'octopus_handle_delete_files_ajax');
add_action('wp_ajax_nopriv_octopus_delete_selected_files', 'octopus_handle_delete_files_ajax');

function octopus_handle_delete_files_ajax() {
     if (isset($_POST['delete_security'])) {
        check_ajax_referer('octopus_delete_selected_files', 'delete_security');
    } else {
        if (isset($_POST['delete_security'])) {
        check_ajax_referer('octopus_delete_selected_files', 'delete_security');
    } else {
        check_ajax_referer('octopus_delete_selected_files', 'security');
    }
    }

    $files = $_POST['selected_pdfs'] ?? $_POST['delete_pdfs'] ?? [];
    $upload_dir = wp_upload_dir();
    $deleted = 0;

    foreach ($files as $filename) {
        foreach (['verkoop', 'aankoop'] as $type) {
            $base = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
            foreach (['pdf', 'xml', 'json'] as $ext) {
                $path = $base . basename($filename, '.pdf') . '.' . $ext;
                if (file_exists($path)) {
                    unlink($path);
                    $deleted++;
                }
            }
        }
    }

    wp_send_json_success(['deleted' => $deleted]);
}

add_action('wp_ajax_octopus_mail_selected_xml', 'octopus_handle_mail_selected_xml_ajax');
add_action('wp_ajax_nopriv_octopus_mail_selected_xml', 'octopus_handle_mail_selected_xml_ajax');

function octopus_handle_mail_selected_xml_ajax() {
    if (isset($_POST['mail_security'])) {
        check_ajax_referer('octopus_mail_xml_by_selection', 'mail_security');
    } else {
        if (isset($_POST['mail_security'])) {
        check_ajax_referer('octopus_mail_xml_by_selection', 'mail_security');
    } else {
        check_ajax_referer('octopus_mail_xml_by_selection', 'security');
    }
    }

    $files = $_POST['selected_pdfs'] ?? [];
    $email = sanitize_email($_POST['email_to_xml'] ?? '');

    if (empty($files) || empty($email)) {
        wp_send_json_error(['message' => 'Geen bestanden of e-mailadres opgegeven.']);
    }

    require_once plugin_dir_path(__FILE__) . 'includes/logger.php'; // ✅ zorg dat logger geladen is
    $upload_dir = wp_upload_dir();
    $sent = 0;

    foreach ($files as $filename) {
        $type = strpos($filename, 'V') === 0 ? 'verkoop' : 'aankoop';
        $base = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
        $xml_path = $base . basename($filename, '.pdf') . '.xml';

        if (!file_exists($xml_path)) continue;

        $attachments = [$xml_path];
        $subject = "XML Factuur - {$filename}";
        $body = "In de bijlage vind je het XML-bestand van de factuur: {$filename}";
        $headers = ['Content-Type: text/plain; charset=UTF-8'];

        $result = wp_mail($email, $subject, $body, $headers, $attachments);

        // ✅ LOG DE VERZENDING
        OctopusEmailLogger::log([
            'status' => $result ? '✅ SUCCES' : '❌ FAIL',
            'to'     => $email,
            'files'  => [basename($xml_path)],
            'type'   => $type,
            'mode'   => 'xml_selections_ajax'
        ]);

        if ($result) $sent++;
    }

    wp_send_json_success(['sent' => $sent]);
}


add_action('wp_ajax_octopus_download_zip_ajax', 'octopus_download_zip_ajax');
add_action('wp_ajax_nopriv_octopus_download_zip_ajax', 'octopus_download_zip_ajax');

function octopus_download_zip_ajax() {
    $type = $_GET['type'] ?? '';
    if (!in_array($type, ['verkoop', 'aankoop'])) {
        wp_die('Ongeldig type');
    }

    $upload_dir = wp_upload_dir();
    $base_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
    $files = glob($base_dir . '*.{pdf,xml}', GLOB_BRACE);

    if (empty($files)) {
        wp_die('Geen bestanden beschikbaar.');
    }

    $zip = new ZipArchive();
    $tmp_file = tempnam(sys_get_temp_dir(), 'octopus_' . $type . '_');

    if ($zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
        wp_die('Kan ZIP niet openen.');
    }

    foreach ($files as $file) {
        $zip->addFile($file, basename($file));
    }

    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $type . '_facturen.zip"');
    header('Content-Length: ' . filesize($tmp_file));
    readfile($tmp_file);
    unlink($tmp_file);
    exit;
}

add_action('wp_ajax_octopus_delete_selected_files_ajax', 'octopus_delete_selected_files_ajax');
add_action('wp_ajax_nopriv_octopus_delete_selected_files_ajax', 'octopus_delete_selected_files_ajax');

function octopus_delete_selected_files_ajax() {
    if (!check_ajax_referer('octopus_delete_selected_files', '_wpnonce', false)) {
        wp_send_json_error(['message' => 'Ongeldige nonce']);
    }

    $files = $_POST['selected_pdfs'] ?? $_POST['delete_pdfs'] ?? [];
    $upload_dir = wp_upload_dir();
    $deleted = 0;

    foreach ($files as $filename) {
        foreach (['verkoop', 'aankoop'] as $type) {
            $base = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
            foreach (['pdf', 'xml', 'json'] as $ext) {
                $path = $base . basename($filename, '.pdf') . '.' . $ext;
                if (file_exists($path)) {
                    unlink($path);
                    $deleted++;
                }
            }
        }
    }

    wp_send_json_success(['deleted' => $deleted]);
}



function octopus_facturatie_settings_page() {
    include plugin_dir_path(__FILE__) . 'templates/settings-page.php';
}

function octopus_email_log_page()
{
    $upload_dir = wp_upload_dir();
    $log_path = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/logs/email-log.php';

    echo '<div class="wrap">';
    echo '<h1>📧 E-mailverzendlog</h1>';

    if (!file_exists($log_path)) {
        echo '<p>Er is nog geen logbestand aangemaakt.</p></div>';
        return;
    }

    $entries = include $log_path;

    if (!is_array($entries) || empty($entries)) {
        echo '<p>Logbestand is leeg.</p></div>';
        return;
    }

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Datum & Tijd</th><th>Status</th><th>Ontvanger</th><th>Bestand(en)</th></tr></thead><tbody>';

    foreach (array_reverse($entries) as $entry) {
        $files = !empty($entry['files']) ? implode('<br>', array_map('esc_html', $entry['files'])) : '';
        $status = $entry['status'] ?? '';
        echo '<tr>';
        echo '<td>' . esc_html($entry['timestamp'] ?? '') . '</td>';
        echo '<td>' . ($status === '✅ SUCCES' ? '<span style="color:green;">' . esc_html($status) . '</span>' : '<span style="color:red;">' . esc_html($status) . '</span>') . '</td>';
        echo '<td>' . esc_html($entry['to'] ?? '') . '</td>';
        echo '<td><small>' . $files . '</small></td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
}

function octopus_over_plugin_page() {
    ?>
    <div class="wrap">
        <h1>ℹ️ Over Factuur Studio</h1>
        <p><strong>Pluginnaam:</strong> Factuur Studio</p>
        <p><strong>Versie:</strong> 1.3</p>
        <p><strong>Ontwikkelaar:</strong> Michaël Redant</p>
        <p><strong>Website:</strong> <a href="https://www.xinudesign.be" target="_blank">www.xinudesign.be</a></p>
        <p><strong>Contact:</strong> <a href="mailto:michael@xinudesign.be">michael@xinudesign.be</a></p>
        <hr>
        <p>Deze plugin is ontwikkeld om snel en eenvoudig aankoop- of verkoopfacturen te genereren in PDF en UBL-formaat, inclusief automatische validatie van btw-nummers via VIES.</p>
        <p>Gebruiksvriendelijk en ideaal voor demo’s of integraties met boekhoudsoftware zoals Octopus.</p>
    </div>
    <?php
}
