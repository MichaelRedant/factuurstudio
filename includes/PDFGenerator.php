<?php

require_once plugin_dir_path(__FILE__) . '../libs/fpdf/fpdf.php';

class PDFGenerator
{
    // Converteer UTF-8 naar Windows-1252 (voor FPDF)
    private static function e($str)
    {
        $converted = iconv('UTF-8', 'windows-1252//TRANSLIT', $str);
        return $converted !== false ? $converted : $str;
    }

    public static function get_all_bedrijven()
    {
        $pad = plugin_dir_path(__FILE__) . '../data/bedrijven_valid.json';

        if (!file_exists($pad)) {
            error_log("⚠️ bedrijven_valid.json niet gevonden op pad: $pad");
            return [];
        }

        $json = file_get_contents($pad);
        $bedrijven = json_decode($json, true);

        if (!is_array($bedrijven) || count($bedrijven) === 0) {
            error_log("⚠️ bedrijven_valid.json is leeg of ongeldig JSON.");
            return [];
        }

        return $bedrijven;
    }

    public static function fallback_klant()
    {
        return [
            'naam' => 'Testbedrijf NV',
            'btw' => 'BE0000000000',
            'adres' => 'Onbekende straat 1, 1000 Brussel'
        ];
    }

    public static function get_random_products($count)
    {
        $producten = [
            "Consultancy-uren", "Hostingpakket Basic", "Website onderhoud", "SEO optimalisatie", "Social media campagne",
            "Productfotografie", "UX-audit", "Contentcreatie", "E-mailcampagne", "Ontwikkeling webshopmodule",
            "IT-supportuur", "Licentiekost softwarepakket", "Digitale advertentiecampagne", "Copywriting landingpage", "Logo-ontwerp",
            "Visitekaartjes 500st", "Analyseboekhoudsysteem", "CRM-integratie", "Opleiding op locatie", "Videomontage",
            "Performance marketing rapport", "SSL-certificaat", "Cloudopslagpakket", "Beveiligingsaudit", "Google Ads Setup",
            "Retargetingcampagne", "Nieuwsbriefontwerp", "Mobiele optimalisatie", "Design huisstijl", "Drukwerk brochures",
            "Flyerontwerp", "Remote installatie", "API-koppeling met ERP", "Gebruikershandleiding", "Batchverwerking data",
            "Systeemanalyse", "Functionaliteitsuitbreiding", "Workshoppakket", "Tijdregistratiesysteem",
            "Gebruikerstraining", "Auditingdiensten", "Camerabeveiliging", "Emailverificatiesysteem", "DNS-configuratie",
            "Facturatiesoftware", "Social media branding", "After-sales support", "Financiële rapportering", "Opstartkost project",
            "Documenttemplate-ontwerp"
        ];

        shuffle($producten);
        $regels = [];

        $max = min($count, count($producten));
        for ($i = 0; $i < $max; $i++) {
            $omschrijving = $producten[$i];
            $aantal = rand(1, 10);
            $prijs = rand(1000, 50000) / 100;
            $regels[] = [
                'omschrijving' => $omschrijving,
                'aantal' => $aantal,
                'prijs' => $prijs,
                'subtotaal' => $aantal * $prijs
            ];
        }

        return $regels;
    }

    public static function get_products_by_branch($branch, $count = 3)
{
        $pad = plugin_dir_path(__FILE__) . '../data/branches.json';
        if (!file_exists($pad)) return self::get_random_products($count);

    $json = file_get_contents($pad);
        $branches = json_decode($json, true);

    if (!is_array($branches) || !isset($branches[$branch])) {
            return self::get_random_products($count);
        }

     $producten = $branches[$branch];
        shuffle($producten);
        $regels = [];

        for ($i = 0; $i < min($count, count($producten)); $i++) {
            $product = $producten[$i];
            $omschrijving = is_array($product) ? $product['omschrijving'] : $product;
            $aantal = rand(1, 10);
            $prijs = rand(1000, 50000) / 100;
            $regels[] = [
                'omschrijving' => $omschrijving,
                'aantal' => $aantal,
                'prijs' => $prijs,
                'subtotaal' => $aantal * $prijs
            ];
        }

    return $regels;
    }


    // ✅ ENKEL ontvangen data gebruiken, niets random meer
    public static function generate_pdf($factuurnummer, $type, $klant, $leverancier, $regels, $btw, $totaal, $datum)
{
    $timestamp = $datum ? strtotime($datum) : false;
        if ($timestamp === false) {
            $timestamp = time();
        }
        $datum = date('d/m/Y', $timestamp);

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, self::e("Factuur: $factuurnummer"), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, self::e("Datum: $datum"), 0, 1);
        $pdf->Cell(0, 8, self::e("Type: $type"), 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(95, 8, self::e("Klant"), 0, 0);
        $pdf->Cell(0, 8, self::e("Leverancier"), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(95, 6, self::e($klant['naam']), 0, 0);
        $pdf->Cell(0, 6, self::e($leverancier['naam']), 0, 1);

        $pdf->Cell(95, 6, self::e($klant['adres']), 0, 0);
        $pdf->Cell(0, 6, self::e($leverancier['adres']), 0, 1);

        $pdf->Cell(95, 6, self::e("BTW: " . $klant['btw']), 0, 0);
        $pdf->Cell(0, 6, self::e("BTW: " . $leverancier['btw']), 0, 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 8, self::e("Omschrijving"), 1);
        $pdf->Cell(20, 8, self::e("Aantal"), 1);
        $pdf->Cell(30, 8, self::e("Prijs"), 1);
        $pdf->Cell(30, 8, self::e("Subtotaal"), 1);
        $pdf->Cell(20, 8, self::e("BTW %"), 1);
        $pdf->Cell(30, 8, self::e("BTW"), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($regels as $regel) {
            $pdf->Cell(60, 8, self::e($regel['omschrijving']), 1);
            $pdf->Cell(20, 8, $regel['aantal'], 1);
            $pdf->Cell(30, 8, self::e("€" . number_format($regel['prijs'], 2, ',', ' ')), 1);
            $pdf->Cell(30, 8, self::e("€" . number_format($regel['subtotaal'], 2, ',', ' ')), 1);
            $pdf->Cell(20, 8, self::e($regel['vat_rate'] . '%'), 1);
            $pdf->Cell(30, 8, self::e("€" . number_format($regel['vat_amount'], 2, ',', ' ')), 1);
            $pdf->Ln();
        }

        $pdf->Ln(5);
$subtotaal = array_sum(array_column($regels, 'subtotaal'));
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 8, self::e("Subtotaal (excl. btw)"), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, self::e("€" . number_format($subtotaal, 2, ',', ' ')), 0, 1);

        foreach ($btw as $rate => $amount) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(60, 8, self::e("BTW ({$rate}%)"), 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 8, self::e("€" . number_format($amount, 2, ',', ' ')), 0, 1);
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 8, self::e("Totaal incl. btw"), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, self::e("€" . number_format($totaal, 2, ',', ' ')), 0, 1);

        $pdf->Ln(10);

        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 8, self::e("Gegenereerd door Factuur Studio – testversie"), 0, 1);

        $upload_dir = wp_upload_dir();
        $base_path = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/' . $type . '/';
        wp_mkdir_p($base_path);

        $output_path = $base_path . $factuurnummer . '.pdf';
        $pdf->Output('F', $output_path);

        return true;
    }
}