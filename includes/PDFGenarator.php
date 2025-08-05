<?php

require_once plugin_dir_path(__DIR__) . 'libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGenerator
{
    public static function generate_pdf($factuurnummer, $type = 'verkoop', $regels = [], $btw = 0, $totaal = 0, $klant = [], $leverancier = [], $datum = null)
{
    $datum = date('d/m/Y', strtotime($datum ?? 'now'));
    $bedrijf = $leverancier['naam'] ?? 'Voorbeeld BV';
    $klantnaam = $klant['naam'] ?? 'Klant NV';

    $subtotaal = 0;
    $table_rows = '';

    foreach ($regels as $regel) {
        $prijs = $regel['prijs'];
        $aantal = $regel['aantal'];
        $lijn_subtotaal = $regel['subtotaal'];
        $lijn_btw = round($lijn_subtotaal * 0.21, 2);
        $subtotaal += $lijn_subtotaal;

        $table_rows .= '<tr>';
        $table_rows .= '<td>' . htmlspecialchars($regel['omschrijving']) . '</td>';
        $table_rows .= '<td>' . $aantal . '</td>';
        $table_rows .= '<td>€' . number_format($prijs, 2, ',', ' ') . '</td>';
        $table_rows .= '<td>€' . number_format($lijn_subtotaal, 2, ',', ' ') . '</td>';
        $table_rows .= '<td>€' . number_format($lijn_btw, 2, ',', ' ') . '</td>';
        $table_rows .= '</tr>';
    }

    $btw = round($subtotaal * 0.21, 2);
    $totaal = $subtotaal + $btw;

    $html = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factuur $factuurnummer</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .summary { margin-top: 20px; font-size: 13px; }
        .summary p { margin: 4px 0; }
        .footer { margin-top: 40px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <h1>Factuur: $factuurnummer</h1>
    <p><strong>Datum:</strong> $datum</p>
    <p><strong>Type:</strong> $type</p>
    <p><strong>Klant:</strong> $klantnaam</p>
    <p><strong>Leverancier:</strong> $bedrijf</p>

    <table>
        <thead>
            <tr><th>Omschrijving</th><th>Aantal</th><th>Prijs</th><th>Subtotaal</th><th>BTW</th></tr>
        </thead>
        <tbody>
            $table_rows
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Totaal excl. btw:</strong> €{number_format($subtotaal, 2, ',', ' ')}</p>
        <p><strong>BTW (21%):</strong> €{number_format($btw, 2, ',', ' ')}</p>
        <p><strong>Totaal inclusief:</strong> €{number_format($totaal, 2, ',', ' ')}</p>
    </div>

    <div class="footer">
        Gegeneerd door Octopus Facturatiegenerator – testversie
    </div>
</body>
</html>
HTML;

    $options = new \Dompdf\Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $upload_dir = wp_upload_dir();
    $base_path = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/' . $type . '/';
    wp_mkdir_p($base_path);

    $output_path = $base_path . $factuurnummer . '.pdf';
    file_put_contents($output_path, $dompdf->output());

    return true;
}

}
