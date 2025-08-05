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
        if (!empty($custom_klant) && !empty($custom_klant['naam']) && !empty($custom_klant['btw'])) {
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

        // Voeg btw per regel toe (standaard 21%)
        foreach ($regels as &$regel) {
            if (!isset($regel['vat_rate'])) {
                $regel['vat_rate'] = 21;
            }
            $regel['vat_amount'] = round($regel['subtotaal'] * ($regel['vat_rate'] / 100), 2);
        }
        unset($regel);

        $subtotaal = array_sum(array_column($regels, 'subtotaal'));
        $btw_totals = [];
        foreach ($regels as $regel) {
            $rate = $regel['vat_rate'];
            if (!isset($btw_totals[$rate])) {
                $btw_totals[$rate] = 0;
            }
            $btw_totals[$rate] += $regel['vat_amount'];
        }
        $totaal = round($subtotaal + array_sum($btw_totals), 2);

        return [
            'factuurnummer' => $factuurnummer,
            'type' => $type,
            'datum' => $factuurdatum,
            'klant' => $klant,
            'leverancier' => $leverancier,
            'regels' => $regels,
            'btw' => $btw_totals,
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
        foreach (array_slice($producten, 0, $aantal) as $product) {
            $omschrijving = $product['omschrijving'];
            $vat_rate = $product['vat_rate'] ?? 21;
            $aantal_stuks = rand(1, 10);
            $prijs = rand(1000, 50000) / 100;
            $sub = $aantal_stuks * $prijs;

            $regels[] = [
                'omschrijving' => $omschrijving,
                'aantal' => $aantal_stuks,
                'prijs' => $prijs,
                'subtotaal' => $sub,
                'vat_rate' => $vat_rate,
                'vat_amount' => round($sub * ($vat_rate / 100), 2)
            ];
        }

        return $regels;
    }
}
