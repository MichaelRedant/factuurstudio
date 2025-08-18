<?php

require_once plugin_dir_path(__DIR__) . 'includes/PDFGenerator.php';
require_once plugin_dir_path(__DIR__) . 'includes/UBLGenerator.php';
require_once plugin_dir_path(__DIR__) . 'includes/FactuurDataGenerator.php';


class FactuurGenerator
{
    private $types = []; // ['verkoop', 'aankoop']
    private $aantal;
    private $anoniem;
    private $custom_gegevens = [];
    private $datum_van;
    private $datum_tot;
    private $branche;
    private $user_id;


    public function __construct($post_data)
    {
        $this->types = [];
        if (!empty($post_data['simulate_verkoop'])) $this->types[] = 'verkoop';
        if (!empty($post_data['simulate_aankoop'])) $this->types[] = 'aankoop';

        $this->aantal = intval($post_data['count'] ?? 1);
        $this->anoniem = !empty($post_data['anonymous']);

        $this->custom_gegevens = [
    'naam'    => sanitize_text_field($post_data['naam'] ?? ''),
    'adres'   => sanitize_text_field($post_data['adres'] ?? ''),
    'btw'     => strtoupper(str_replace(' ', '', $post_data['btw'] ?? '')),
    'branche' => sanitize_text_field($post_data['branche'] ?? ''),
];

        $this->datum_van = !empty($post_data['date_from']) ? strtotime($post_data['date_from']) : strtotime('-30 days');
        $this->datum_tot = !empty($post_data['date_to']) ? strtotime($post_data['date_to']) : time();
        $this->branche = sanitize_text_field($post_data['branche'] ?? '');
        $this->user_id = sanitize_text_field($post_data['user_id'] ?? '');
        if (empty($this->user_id)) {
            $this->user_id = wp_generate_uuid4();
        }

    }

    public function generate()
    {
        
        if (empty($this->types)) {
            return "⚠️ Geen factuurtype geselecteerd.";
        }
        $this->cleanup_old_files();

        foreach ($this->types as $type) {
            $result = $this->generate_for_type($type);
            if ($result !== true) {
                return $result;
            }
        }

        return true;
    }

    private function generate_for_type($type)
    {
        $upload_dir = wp_upload_dir();
        $base_path = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$this->user_id}/{$type}/";

        if (!wp_mkdir_p($base_path)) {
            return "❌ Kan map '$base_path' niet aanmaken.";
        }

        $prefix = strtoupper(substr($type, 0, 1)) . date('Ymd');

        for ($i = 1; $i <= $this->aantal; $i++) {
            $unique_id = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)); // bijv. 5 tekens zoals 'A9C3F'
$factuurnummer = $prefix . '-' . $unique_id;

            $factuurdatum = $this->genereer_datum_in_range();

            $data = FactuurDataGenerator::generate(
    $factuurnummer,
    $type,
    $factuurdatum,
    $this->anoniem ? null : $this->custom_gegevens,
    $this->branche
);

            $pdf_path = $base_path . $data['factuurnummer'] . '.pdf';

            // PDF
            PDFGenerator::generate_pdf(
                $data['factuurnummer'],
                $data['type'],
                $data['klant'],
                $data['leverancier'],
                $data['regels'],
                $data['btw'],
                $data['totaal'],
                $data['datum']
            );

            // UBL
            $ubl = UBLGenerator::generate_invoice_xml(
                $data['factuurnummer'],
                $data['klant'],
                $data['leverancier'],
                $data['regels'],
                $data['btw'],
                $data['totaal'],
                $data['datum'],
                $pdf_path
            );

            file_put_contents($base_path . $data['factuurnummer'] . '.xml', $ubl);
            // Metadata opslaan voor factuuroverzicht
$meta = [
    'klant' => $data['klant']['naam'],
    'leverancier' => $data['leverancier']['naam'],
    'datum' => $data['datum'],
];
            file_put_contents($base_path . $data['factuurnummer'] . '.json', json_encode($meta));
        }

        return true;
    }

    private function genereer_datum_in_range()
    {
        return date('Y-m-d', rand($this->datum_van, $this->datum_tot));
    }

    private function cleanup_old_files() {
        $upload_dir = wp_upload_dir();

    foreach (['verkoop', 'aankoop'] as $type) {
        $dir = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$this->user_id}/{$type}/";
        if (!file_exists($dir)) continue;

        foreach (glob($dir . '*.{pdf,xml,json}', GLOB_BRACE) as $file) {
            @unlink($file);
        }
    }
}

}
