<?php

function octopus_extract_invoice_metadata($xml_path) {
    if (!file_exists($xml_path)) return ['klant' => 'Onbekend', 'leverancier' => 'Onbekend', 'datum' => 'Onbekend'];

    $xml = simplexml_load_file($xml_path);
    $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
    $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

    $klant = $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName');
    $leverancier = $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName');
    $datum = $xml->xpath('//cbc:IssueDate');

    return [
        'klant' => isset($klant[0]) ? (string)$klant[0] : 'Onbekend',
        'leverancier' => isset($leverancier[0]) ? (string)$leverancier[0] : 'Onbekend',
        'datum' => isset($datum[0]) ? (string)$datum[0] : 'Onbekend',
    ];
}

if (
    isset($_GET['download_zip']) &&
    current_user_can('manage_options') &&
    !headers_sent()
) {
    require_once plugin_dir_path(__FILE__) . '/../includes/Exporter.php';

    $upload_dir = wp_upload_dir();
    $type = sanitize_text_field($_GET['download_zip']);
    $batch_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/";
    $zip_file = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/facturen-batch.zip';

    if (Exporter::zip_last_batch($batch_dir, $zip_file)) {
        wp_safe_redirect($upload_dir['baseurl'] . '/octopus-invoices/facturen-batch.zip');
        exit;
    } else {
        wp_safe_redirect(admin_url('admin.php?page=facturatiegenerator&zip_error=1'));
        exit;
    }
}

// Verwerk formulier indien gepost
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('octopus_generate_invoices')) {
    $count = intval($_POST['count'] ?? 0);

    if ($count < 1 || $count > 100) {
        echo '<div class="notice notice-error"><p>Gelieve tussen 1 en 100 facturen te genereren.</p></div>';
    } elseif (empty($_POST['simulate_verkoop']) && empty($_POST['simulate_aankoop'])) {
        echo '<div class="notice notice-error"><p>Selecteer minstens één factuurtype (verkoop of aankoop).</p></div>';
    } else {
        require_once plugin_dir_path(__DIR__) . 'includes/FactuurGenerator.php';
        $generator = new FactuurGenerator([
    ...$_POST,
    'branche' => sanitize_text_field($_POST['branche'] ?? '')
]);
$result = $generator->generate();

        if ($result === true) {
            echo '<div class="notice notice-success"><p>Facturen succesvol gegenereerd!</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Fout bij het genereren van facturen: ' . esc_html($result) . '</p></div>';
        }
    }
}
function octopus_get_available_branches(): array {
    $json_path = plugin_dir_path(__FILE__) . '/../data/branches.json';
    if (!file_exists($json_path)) return [];

    $json = file_get_contents($json_path);
    $data = json_decode($json, true);

    return is_array($data) ? array_keys($data) : [];
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">🧾 Octopus Facturatiegenerator</h1>
<hr class="wp-header-end">
    <?php if (isset($_GET['zip_error'])): ?>
    <div class="notice notice-error"><p>❌ Fout bij het aanmaken van het ZIP-bestand. Controleer schrijfrechten of bestandslocaties.</p></div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): 
    $count = intval($_GET['deleted']);
?>
    <div class="notice notice-success is-dismissible"><p>🗑️ <?php echo $count; ?> factuurbestand(en) succesvol verwijderd.</p></div>
<?php endif; ?>
   
        <?php if (isset($_GET['generate_bedrijven']) && current_user_can('manage_options')): ?>
    <div class="notice notice-info"><p><strong>Bedrijvenlijst wordt gegenereerd...</strong></p>
    <pre style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;">
<?php
function check_vat_vies($btw_nummer)
{
    $countryCode = substr($btw_nummer, 0, 2);
    $vatNumber = preg_replace('/[^0-9]/', '', substr($btw_nummer, 2));

    try {
        $client = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
        $params = ['countryCode' => $countryCode, 'vatNumber' => $vatNumber];
        $result = $client->checkVat($params);

        return [
            'valid' => $result->valid,
            'name' => $result->name,
            'address' => $result->address,
            'countryCode' => $result->countryCode,
            'vatNumber' => $result->vatNumber,
        ];
    } catch (SoapFault $e) {
        return ['valid' => false, 'error' => $e->getMessage()];
    }
}

$bedrijven = [
    'BE0473416418', 'BE0400378485', 'BE0402206045', 'BE0214596464', 'BE0836585210',
    'BE0448826918', 'BE0869763267', 'BE0202239951', 'BE0203430576', 'BE0244142664',
    'BE0462920226', 'BE0403200393', 'BE0403199702', 'BE0629761216', 'BE0474776396',
    'BE0824148721', 'BE0425258688', 'BE0431110956', 'BE0404484654', 'BE0403471401',
    'BE0426396954', 'BE0448746645', 'BE0464949902', 'BE0403170701', 'BE0681759451'
];

$valid_bedrijven = [];

foreach ($bedrijven as $btw) {
    echo "Check $btw... ";
    $result = check_vat_vies($btw);
    if ($result['valid']) {
        echo "✅ geldig\n";
        $valid_bedrijven[] = [
            'naam' => $result['name'],
            'btw' => $result['countryCode'] . $result['vatNumber'],
            'adres' => $result['address']
        ];
    } else {
        echo "❌ ongeldig" . (isset($result['error']) ? " ({$result['error']})" : "") . "\n";
    }
    @ob_flush();
    @flush();
    sleep(1);
}

$pad = plugin_dir_path(__FILE__) . '/../data/bedrijven_valid.json';
file_put_contents($pad, json_encode($valid_bedrijven, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\n✔️ Klaar! " . count($valid_bedrijven) . " bedrijven opgeslagen in bedrijven_valid.json";
?>
    </pre>
    </div>
<?php endif; ?>


<!-- Genereer bedrijvenlijst -->
<a href="?page=facturatiegenerator&generate_bedrijven=1" class="button button-primary" style="margin-bottom: 20px;">
    🏢 Genereer bedrijven_valid.json via VIES
</a>


   <form method="post" style="max-width: 700px; margin-top: 20px;">
    <?php wp_nonce_field('octopus_generate_invoices'); ?>

    <h2 class="title">⚙️ Instellingen factuurgeneratie</h2>
    

    <table class="form-table">
        <tr>
            <th scope="row"><label for="simulate_types">Simuleer facturen</label></th>
            <td>
                <label><input type="checkbox" name="simulate_verkoop" value="1"> Verkoopfacturen</label><br>
                <label><input type="checkbox" name="simulate_aankoop" value="1"> Aankoopfacturen</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="count">Aantal per type</label></th>
            <td>
                <input type="number" name="count" id="count" value="1" min="1" max="100" class="small-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">Datum bereik</th>
            <td>
                <label for="date_from">Van:</label>
                <input type="date" name="date_from" id="date_from" />
                <label for="date_to" style="margin-left: 10px;">Tot:</label>
                <input type="date" name="date_to" id="date_to" />
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="2"><h3>📇 Klant- of Leveranciersgegevens</h3></th>
        </tr>
        <tr>
            <th scope="row"><label for="naam">Naam</label></th>
            <td><input type="text" name="naam" id="naam" class="regular-text" placeholder="bv. Octopus NV" /></td>
        </tr>
        <tr>
            <th scope="row"><label for="adres">Adres</label></th>
            <td><input type="text" name="adres" id="adres" class="regular-text" placeholder="Straat 1, 1000 Brussel" /></td>
        </tr>
        <tr>
            <th scope="row"><label for="btw">BTW-nummer</label></th>
            <td><input type="text" name="btw" id="btw" class="regular-text" placeholder="BE0123456789" /></td>
        </tr>
        <tr>
            <th scope="row"><label for="anonymous">Anoniem</label></th>
            <td>
                <label><input type="checkbox" name="anonymous" id="anonymous" /> Genereer zonder klantgegevens</label>
            </td>
        </tr>
        
    </table>

    <tr>
    <th scope="row"><label for="branche">Branche / Sector</label></th>
    <td>
        <select name="branche" id="branche" class="regular-text">
            <option value="">-- Willekeurig --</option>
            <?php foreach (octopus_get_available_branches() as $branch): ?>
                <option value="<?php echo esc_attr($branch); ?>">
                    <?php echo esc_html($branch); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Kies een sector om bijpassende producten of diensten te genereren.</p>
    </td>
</tr>

    <?php submit_button('📄 Genereer Facturen'); ?>
</form>

    <?php

$upload_dir = wp_upload_dir();
$last_type = !empty($_POST['simulate_aankoop']) ? 'aankoop' : 'verkoop';
$batch_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$last_type}/";
$zip_file = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/facturen-batch.zip';


?>

<!-- 📦 EXPORT & VERZENDING -->
<h2 class="title">📦 Exportopties</h2>

<p>
    <a href="<?php echo esc_url(admin_url('admin-post.php?action=octopus_download_zip&type=verkoop')); ?>" class="button button-primary">
        📦 Download verkoopfacturen (ZIP)
    </a>
    <a href="<?php echo esc_url(admin_url('admin-post.php?action=octopus_download_zip&type=aankoop')); ?>" class="button">
        📦 Download aankoopfacturen (ZIP)
    </a>
</p>

<hr class="wp-header-end" style="margin: 30px 0;">

<h2 class="title">📄 Facturenoverzicht en XML-verzending</h2>

<?php
$verkoop_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/verkoop/";
$aankoop_dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/aankoop/";

$pdfs = array_merge(
    glob($verkoop_dir . '*.pdf') ?: [],
    glob($aankoop_dir . '*.pdf') ?: []
);

if (empty($pdfs)) {
    echo '<p><em>Geen gegenereerde facturen gevonden. Genereer eerst facturen om ze hier te bekijken en te verzenden.</em></p>';
} else {
    ?>

    <table class="widefat striped">
        <thead>
    <tr>
        <th style="width:40px;"><input type="checkbox" id="check_all_xml" title="Alles selecteren" /></th>
        <th>Factuur (PDF)</th>
        <th>Klant</th>
        <th>Leverancier</th>
        <th>Datum</th>
    </tr>
</thead>
        <tbody>
            <?php
            foreach ($pdfs as $pdf_path) {
    $filename = basename($pdf_path);
    $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

    // XML-bestand zoeken (zelfde naam)
    $base = basename($filename, '.pdf');
    $type = strpos($pdf_path, '/verkoop/') !== false ? 'verkoop' : 'aankoop';
    $xml_path = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/{$base}.xml";

    // Init metadata
    $metadata = octopus_extract_invoice_metadata($xml_path);
$klant = esc_html($metadata['klant']);
$leverancier = esc_html($metadata['leverancier']);
$datum = esc_html($metadata['datum']);

    echo '<tr>';
    echo '<td><input type="checkbox" class="factuur-checkbox" value="' . esc_attr($filename) . '"></td>';
    echo '<td><a href="' . esc_url($pdf_url) . '" target="_blank">' . esc_html($filename) . '</a></td>';
    echo '<td>' . esc_html($klant) . '</td>';
    echo '<td>' . esc_html($leverancier) . '</td>';
    echo '<td>' . esc_html($datum) . '</td>';
    echo '</tr>';
}
            ?>
        </tbody>
        
    </table>
    <div id="pagination" style="margin-top: 10px;"></div>

    <!-- FORMULIER 1: Verstuur XML-bestanden -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="form_xml">
        <?php wp_nonce_field('octopus_mail_xml_by_selection'); ?>
        <input type="hidden" name="action" value="octopus_mail_selected_xml">

        <div id="selected_xml_container"></div>

        <p style="margin-top: 1em;">
            <label for="email_to_xml"><strong>Ontvanger e-mailadres (bv. U12345678@in.octopus.be):</strong></label><br>
            <input type="email" name="email_to_xml" id="email_to_xml" class="regular-text" required>
        </p>

        <!-- Honeypot veld -->
        <input type="text" name="website" style="display:none;" autocomplete="off">

        <p>
            <button type="submit" class="button button-primary">📤 Verstuur geselecteerde XML-bestanden</button>
        </p>
    </form>

    <!-- FORMULIER 2: Verwijder geselecteerde facturen -->
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Ben je zeker dat je deze facturen definitief wil verwijderen?');" id="form_delete">
        <?php wp_nonce_field('octopus_delete_selected_files'); ?>
        <input type="hidden" name="action" value="octopus_delete_selected_files">

        <div id="selected_delete_container"></div>

        <!-- Honeypot -->
        <input type="text" name="website" style="display:none;" autocomplete="off">

        <p style="margin-top: 1em;">
            <button type="submit" class="button button-secondary">🗑️ Verwijder geselecteerde facturen</button>
        </p>
    </form>

    <script>
        const checkboxes = document.querySelectorAll('.factuur-checkbox');
        const checkAll = document.getElementById('check_all_xml');
        const containerXML = document.getElementById('selected_xml_container');
        const containerDelete = document.getElementById('selected_delete_container');

        function updateSelections() {
            containerXML.innerHTML = '';
            containerDelete.innerHTML = '';
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    containerXML.innerHTML += `<input type="hidden" name="selected_pdfs[]" value="${cb.value}">`;
                    containerDelete.innerHTML += `<input type="hidden" name="delete_pdfs[]" value="${cb.value}">`;
                }
            });
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateSelections));
        checkAll?.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSelections();
        });

        // Init bij laden
        updateSelections();
        const rows = document.querySelectorAll("table.widefat tbody tr");
const rowsPerPage = 20;
let currentPage = 1;

function paginate(page) {
    const totalPages = Math.ceil(rows.length / rowsPerPage);
    currentPage = page;

    rows.forEach((row, i) => {
        row.style.display = (i >= (page - 1) * rowsPerPage && i < page * rowsPerPage) ? "" : "none";
    });

    const pag = document.getElementById("pagination");
    pag.innerHTML = "";

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement("button");
        btn.textContent = i;
        btn.className = "button" + (i === page ? " button-primary" : "");
        btn.onclick = () => paginate(i);
        pag.appendChild(btn);
    }
}

// Initieel uitvoeren
paginate(1);

    </script>

<?php } ?>



