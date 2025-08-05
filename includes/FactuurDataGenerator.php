<?php

class FactuurDataGenerator
{
    public static function generate(string $factuurnummer, string $type = 'verkoop', string $datum = '', ?array $custom_klant = null, ?string $branche = null): array
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

        // Custom klant of leverancier instellen
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

        $factuurdatum = !empty($datum) ? $datum : date('Y-m-d');

        // 🔥 Genereer producten op basis van branche (optioneel)
        $randomAantal = rand(2, 5);
        $regels = self::get_branch_products($branche, $randomAantal);
        if (empty($regels)) {
            // Branche detectie (voor verkoop → leverancier, voor aankoop → klant)
$branche = null;
if ($type === 'verkoop' && !empty($custom_klant['branche'])) {
    $branche = $custom_klant['branche'];
} elseif ($type === 'aankoop' && !empty($custom_klant['branche'])) {
    $branche = $custom_klant['branche'];
}

$regels = $branche
    ? PDFGenerator::get_products_by_branch($branche, rand(2, 6))
    : PDFGenerator::get_random_products(rand(2, 6));
        }

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

    // ✅ NIEUW: Producten per branche
    private static function get_branch_products(?string $branche, int $aantal = 3): array
    {
        if (empty($branche)) return [];

        $path = plugin_dir_path(__FILE__) . '/../data/branches.json';
        if (!file_exists($path)) return [];

        $json = file_get_contents($path);
        $branches = json_decode($json, true);
        if (!isset($branches[$branche]) || !is_array($branches[$branche])) {
            return [];
        }

        $producten = $branches[$branche];
        shuffle($producten);

        $regels = [];
        foreach (array_slice($producten, 0, $aantal) as $omschrijving) {
            $aantal_stuks = rand(1, 10);
            $prijs = rand(1000, 50000) / 100;

            $regels[] = [
                'omschrijving' => $omschrijving,
                'aantal' => $aantal_stuks,
                'prijs' => $prijs,
                'subtotaal' => $aantal_stuks * $prijs
            ];
        }

        return $regels;
    }
}
