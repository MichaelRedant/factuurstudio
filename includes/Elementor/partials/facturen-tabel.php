<?php
require_once plugin_dir_path(__DIR__) . '/../i18n.php';
$upload_dir = wp_upload_dir();
$verkoop_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/verkoop/";
$aankoop_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/aankoop/";

$pdfs = array_merge(
    glob($verkoop_dir . '*.pdf') ?: [],
    glob($aankoop_dir . '*.pdf') ?: []
);

if (empty($pdfs)) {
    echo '<p><em>' . esc_html(octo_t('Er zijn nog geen facturen gegenereerd.','Aucune facture n\'a encore été générée.')) . '</em></p>';
    return;
}
?>

<table class="widefat striped" style="width: 100%; margin-bottom: 20px;">
    <thead>
        <tr>
            <th><?php echo esc_html(octo_t('Factuur','Facture')); ?></th>
            <th><?php echo esc_html(octo_t('Klant','Client')); ?></th>
            <th><?php echo esc_html(octo_t('Leverancier','Fournisseur')); ?></th>
            <th><?php echo esc_html(octo_t('Datum','Date')); ?></th>
        </tr>
    </thead>
    <tbody>
<?php
foreach ($pdfs as $pdf_path) {
    $filename = basename($pdf_path);
    $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

    $base = basename($filename, '.pdf');
    $type = strpos($pdf_path, '/verkoop/') !== false ? 'verkoop' : 'aankoop';
    $xml_path = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/{$base}.xml";

    $klant = $leverancier = $datum = octo_t('Onbekend','Inconnu');

    if (file_exists($xml_path)) {
        $xml = simplexml_load_file($xml_path);
        $issued = $xml->xpath('//cbc:IssueDate');
        $datum = !empty($issued[0]) ? (string) $issued[0] : octo_t('Onbekend','Inconnu');

        $supplier = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name');
        $customer = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyName/cbc:Name');

        $leverancier = !empty($supplier[0]) ? (string) $supplier[0] : octo_t('Onbekend','Inconnu');
        $klant       = !empty($customer[0]) ? (string) $customer[0] : octo_t('Onbekend','Inconnu');
    }

    echo '<tr>';
    echo '<td><a href="' . esc_url($pdf_url) . '" target="_blank">' . esc_html($filename) . '</a></td>';
    echo '<td>' . esc_html($klant) . '</td>';
    echo '<td>' . esc_html($leverancier) . '</td>';
    echo '<td>' . esc_html($datum) . '</td>';
    echo '</tr>';
}
?>
    </tbody>
    </table>

