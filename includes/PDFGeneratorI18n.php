<?php

require_once plugin_dir_path(__FILE__) . '../libs/fpdf/fpdf.php';

class PDFGeneratorI18n
{
    private static function e($str)
    {
        $converted = iconv('UTF-8', 'windows-1252//TRANSLIT', $str);
        return $converted !== false ? $converted : $str;
    }

    private static function t(string $key, string $lang = 'nl'): string
    {
        $lang = strtolower($lang) === 'fr' ? 'fr' : 'nl';
        $map = [
            'invoice'        => ['nl' => 'Factuur',                 'fr' => 'Facture'],
            'date'           => ['nl' => 'Datum',                   'fr' => 'Date'],
            'type'           => ['nl' => 'Type',                    'fr' => 'Type'],
            'customer'       => ['nl' => 'Klant',                   'fr' => 'Client'],
            'supplier'       => ['nl' => 'Leverancier',             'fr' => 'Fournisseur'],
            'description'    => ['nl' => 'Omschrijving',            'fr' => 'Description'],
            'quantity'       => ['nl' => 'Aantal',                  'fr' => 'Quantité'],
            'price'          => ['nl' => 'Prijs',                   'fr' => 'Prix'],
            'line_subtotal'  => ['nl' => 'Subtotaal',               'fr' => 'Sous-total'],
            'vat_rate'       => ['nl' => 'BTW %',                   'fr' => 'TVA %'],
            'vat'            => ['nl' => 'BTW',                     'fr' => 'TVA'],
            'subtotal_excl'  => ['nl' => 'Subtotaal (excl. btw)',   'fr' => 'Sous-total (HTVA)'],
            'total_incl'     => ['nl' => 'Totaal incl. btw',        'fr' => 'Total (TVAC)'],
            'footer'         => ['nl' => 'Gegenereerd door Factuur Studio – testversie',
                                  'fr' => 'Généré par Factuur Studio – version de test'],
            'type_verkoop'   => ['nl' => 'Verkoop',                 'fr' => 'Vente'],
            'type_aankoop'   => ['nl' => 'Aankoop',                 'fr' => 'Achat'],
        ];

        return $map[$key][$lang] ?? $key;
    }

    public static function generate_pdf($factuurnummer, $type, $klant, $leverancier, $regels, $btw, $totaal, $datum, $lang = 'nl')
    {
        $timestamp = $datum ? strtotime($datum) : false;
        if ($timestamp === false) {
            $timestamp = time();
        }
        $datum = date('d/m/Y', $timestamp);

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, self::e(self::t('invoice', $lang) . ": $factuurnummer"), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, self::e(self::t('date', $lang) . ": $datum"), 0, 1);
        $type_label = $type;
        if ($type === 'verkoop') { $type_label = self::t('type_verkoop', $lang); }
        if ($type === 'aankoop') { $type_label = self::t('type_aankoop', $lang); }
        $pdf->Cell(0, 8, self::e(self::t('type', $lang) . ": $type_label"), 0, 1);
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(95, 8, self::e(self::t('customer', $lang)), 0, 0);
        $pdf->Cell(0, 8, self::e(self::t('supplier', $lang)), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(95, 6, self::e($klant['naam']), 0, 0);
        $pdf->Cell(0, 6, self::e($leverancier['naam']), 0, 1);

        $pdf->Cell(95, 6, self::e($klant['adres']), 0, 0);
        $pdf->Cell(0, 6, self::e($leverancier['adres']), 0, 1);

        $pdf->Cell(95, 6, self::e(self::t('vat', $lang) . ": " . $klant['btw']), 0, 0);
        $pdf->Cell(0, 6, self::e(self::t('vat', $lang) . ": " . $leverancier['btw']), 0, 1);
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 8, self::e(self::t('description', $lang)), 1);
        $pdf->Cell(20, 8, self::e(self::t('quantity', $lang)), 1);
        $pdf->Cell(30, 8, self::e(self::t('price', $lang)), 1);
        $pdf->Cell(30, 8, self::e(self::t('line_subtotal', $lang)), 1);
        $pdf->Cell(20, 8, self::e(self::t('vat_rate', $lang)), 1);
        $pdf->Cell(30, 8, self::e(self::t('vat', $lang)), 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($regels as $regel) {
            $pdf->Cell(60, 8, self::e($regel['omschrijving']), 1);
            $pdf->Cell(20, 8, $regel['aantal'], 1);
            $pdf->Cell(30, 8, self::e("€ " . number_format($regel['prijs'], 2, ',', ' ')), 1);
            $pdf->Cell(30, 8, self::e("€ " . number_format($regel['subtotaal'], 2, ',', ' ')), 1);
            $pdf->Cell(20, 8, self::e($regel['vat_rate'] . '%'), 1);
            $pdf->Cell(30, 8, self::e("€ " . number_format($regel['vat_amount'], 2, ',', ' ')), 1);
            $pdf->Ln();
        }

        $pdf->Ln(5);
        $subtotaal = array_sum(array_column($regels, 'subtotaal'));
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 8, self::e(self::t('subtotal_excl', $lang)), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, self::e("€ " . number_format($subtotaal, 2, ',', ' ')), 0, 1);

        foreach ($btw as $rate => $amount) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(60, 8, self::e(self::t('vat', $lang) . " ({$rate}%)"), 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 8, self::e("€ " . number_format($amount, 2, ',', ' ')), 0, 1);
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(60, 8, self::e(self::t('total_incl', $lang)), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, self::e("€ " . number_format($totaal, 2, ',', ' ')), 0, 1);

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 8, self::e(self::t('footer', $lang)), 0, 1);

        $upload_dir = wp_upload_dir();
        $base_path = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/' . $type . '/';
        wp_mkdir_p($base_path);

        $output_path = $base_path . $factuurnummer . '.pdf';
        $pdf->Output('F', $output_path);

        return true;
    }
}

