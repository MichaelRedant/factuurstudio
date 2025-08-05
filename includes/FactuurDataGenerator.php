<?php

class FactuurDataGenerator
{
    public static function generate(string $factuurnummer, string $type = 'verkoop', string $datum = '', ?array $custom_klant = null): array
    {
        $bedrijven = PDFGenerator::get_all_bedrijven();

        if (count($bedrijven) < 2) {
            $klant = PDFGenerator::fallback_klant();
            $leverancier = PDFGenerator::fallback_klant();
        } else {
            do {
                $klant = $bedrijven[array_rand($bedrijven)];
                $leverancier = $bedrijven[array_rand($bedrijven)];
            } while ($klant['btw'] === $leverancier['btw']);
        }

        // Correcte toewijzing van custom gegevens op basis van factuurtype
if (!empty($custom_klant['naam']) && !empty($custom_klant['btw'])) {
    $custom = [
        'naam'  => $custom_klant['naam'],
        'adres' => $custom_klant['adres'],
        'btw'   => $custom_klant['btw'],
    ];

    if ($type === 'verkoop') {
        $leverancier = $custom;
    } elseif ($type === 'aankoop') {
        $klant = $custom;
    }
}

        // Datum instellen
        $factuurdatum = !empty($datum) ? $datum : date('Y-m-d');

        // Genereer factuurlijnen
        $regels = PDFGenerator::get_random_products(rand(1, 5));
        $subtotaal = array_sum(array_column($regels, 'subtotaal'));
        $btw = round($subtotaal * 0.21, 2);
        $totaal = round($subtotaal + $btw, 2);


        return [
            'factuurnummer' => $factuurnummer,
            'type' => $type,
            'datum' => $factuurdatum,
            'klant' => $klant,
            'leverancier' => $leverancier,
            'regels' => $regels,
            'btw' => $btw,
            'totaal' => $totaal,
        ];
    }
}
