<?php

class FactuurDataGenerator
{
    private static array $FR_MAP = [
        // Marketing
        'Social media campagne' => 'Campagne sur les réseaux sociaux',
        'SEO optimalisatie' => 'Optimisation SEO',
        'Nieuwsbriefontwerp' => 'Conception de newsletter',
        'Retargetingcampagne' => 'Campagne de retargeting',
        'Contentcreatie' => 'Création de contenu',
        'Performance marketing rapport' => 'Rapport de performance marketing',
        'E-mailcampagne' => 'Campagne e-mail',
        'Google Ads Setup' => 'Configuration Google Ads',
        'Brandingstrategie' => 'Stratégie de branding',
        'Online community management' => 'Gestion de communauté en ligne',
        'Influencer samenwerking' => 'Collaboration avec influenceurs',
        'Video-advertentieproductie' => 'Production de publicité vidéo',
        // IT & Software
        'Consultancy-uren' => 'Heures de consultance',
        'API-koppeling met ERP' => 'Intégration API avec ERP',
        'Licentiekost softwarepakket' => 'Coût de licence logiciel',
        'CRM-integratie' => 'Intégration CRM',
        'Digitale advertentiecampagne' => 'Campagne publicitaire digitale',
        'Remote installatie' => 'Installation à distance',
        'Tijdregistratiesysteem' => 'Système d’enregistrement du temps',
        'IT-supportuur' => 'Heure de support IT',
        'Cloudopslagpakket' => 'Forfait de stockage cloud',
        'Systeemanalyse' => 'Analyse du système',
        'Functionaliteitsuitbreiding' => 'Extension de fonctionnalités',
        'Batchverwerking data' => 'Traitement de données par lots',
        // Design & Drukwerk
        'Logo-ontwerp' => 'Conception de logo',
        'Flyerontwerp' => 'Conception de flyer',
        'Design huisstijl' => 'Conception d\'identité visuelle',
        'Visitekaartjes 500st' => 'Cartes de visite 500 pcs',
        'Drukwerk brochures' => 'Impression de brochures',
        'Posterdesign' => 'Conception d\'affiche',
        'Bannerontwerp' => 'Conception de bannière',
        'Social media templates' => 'Modèles pour réseaux sociaux',
        'Productverpakking ontwerp' => 'Conception d\'emballage produit',
        'Grafisch ontwerpuur' => 'Heure de conception graphique',
        'DTP-opmaak' => 'Mise en page (PAO)',
        'Boek lay-out voor drukwerk' => 'Mise en page de livre pour impression',
        // Training & Opleiding
        'Opleiding op locatie' => 'Formation sur site',
        'Workshoppakket' => 'Pack atelier',
        'Gebruikerstraining' => 'Formation utilisateur',
        'Handleidingontwikkeling' => 'Développement de manuel',
        'Webinar sessie' => 'Session de webinaire',
        'Train-the-trainer programma' => 'Programme train the trainer',
        'E-learningmodule' => 'Module e-learning',
        'Technische workshop' => 'Atelier technique',
        'Interne opleiding softwaregebruik' => 'Formation interne à l’utilisation du logiciel',
        // Security & Audit
        'Beveiligingsaudit' => 'Audit de sécurité',
        'Camerabeveiliging' => 'Sécurité par caméras',
        'Emailverificatiesysteem' => 'Système de vérification e-mail',
        'Auditingdiensten' => 'Services d\'audit',
        'Penetratietest' => 'Test de pénétration',
        'Firewall-configuratie' => 'Configuration de pare-feu',
        'GDPR-compliancecheck' => 'Contrôle de conformité RGPD',
        'Netwerksegmentatie-analyse' => 'Analyse de segmentation réseau',
        'Beveiligingsadvies op locatie' => 'Conseil sécurité sur site',
        // Financiën & Boekhouding
        'Analyseboekhoudsysteem' => 'Analyse du système comptable',
        'Financiële rapportering' => 'Reporting financier',
        'FinanciǮle rapportering' => 'Reporting financier',
        'Opstartkost boekhoudproject' => 'Coût de démarrage projet comptable',
        'Facturatiesoftware' => 'Logiciel de facturation',
        'Jaarrekeningvoorbereiding' => 'Préparation des comptes annuels',
        'Budgetanalyse' => 'Analyse budgétaire',
        'BTW-aangifte ondersteuning' => 'Support à la déclaration TVA',
        'Debiteurenbeheer' => 'Gestion des débiteurs',
        'Kostenstructuurherziening' => 'Révision de la structure des coûts',
        // Consultancy & Strategie
        'Businessstrategie sessie' => 'Session de stratégie d’entreprise',
        'SWOT-analyse workshop' => 'Atelier analyse SWOT',
        'Concurrentieanalyse' => 'Analyse de la concurrence',
        'Marktonderzoek' => 'Étude de marché',
        'Kostenoptimalisatieadvies' => 'Conseil en optimisation des coûts',
        'Innovatieworkshop' => 'Atelier innovation',
        'Go-to-marketplan' => 'Plan go-to-market',
        'Prijsstrategie sessie' => 'Session de stratégie de prix',
        // Legal & Compliance
        'Contractscreening' => 'Relecture de contrats',
        'Opstellen algemene voorwaarden' => 'Rédaction de conditions générales',
        'Privacybeleid op maat' => 'Politique de confidentialité sur mesure',
        'Legal audit' => 'Audit juridique',
        'Compliance-rapport' => 'Rapport de conformité',
        'Begeleiding GDPR-implementatie' => 'Accompagnement mise en œuvre RGPD',
        'Herstructureringsadvies' => 'Conseil en restructuration',
        'Licentiecontrole' => 'Contrôle de licences',
        // Retail & Verkoop
        'Kassa POS-systeem' => 'Système de caisse POS',
        'Barcode scanners' => 'Scanners de codes-barres',
        'Productdisplays' => 'Présentoirs produits',
        'Papieren draagtassen' => 'Sacs en papier',
        'Reklabels' => 'Étiquettes de rayonnage',
        'Retailverpakkingen' => 'Emballages de vente au détail',
        'Betaalterminals' => 'Terminaux de paiement',
        'Voorraadkast groot' => 'Grande armoire de stockage',
        'Kledingrekken' => 'Portants à vêtements',
        'Winkelinterieurkit' => 'Kit d’aménagement de magasin',
        // Bouw & Installatie
        'Cementzakken 25kg' => 'Sacs de ciment 25 kg',
        'PVC-buizen 2m' => 'Tuyaux PVC 2 m',
        'Dakisolatiepanelen' => 'Panneaux d’isolation de toiture',
        'Wandtegels keramisch' => 'Carreaux muraux céramiques',
        'Loodgieterkit' => 'Kit de plomberie',
        'Bevestigingsmaterialen' => 'Matériel de fixation',
        'Elektriciteitskabels 100m' => 'Câbles électriques 100 m',
        'Kraanonderdelen' => 'Pièces de robinetterie',
        'Gevelstenen rood' => 'Briques de façade rouges',
        'Schroefpakketten' => 'Lots de vis',
        // Kantoor & Meubilair
        'Bureau met ladeblok' => 'Bureau avec caisson',
        'Ergonomische bureaustoel' => 'Chaise de bureau ergonomique',
        'Vergadertafel hout' => 'Table de réunion en bois',
        'Kladblokken A4' => 'Blocs-notes A4',
        'Printerpapier doos' => 'Boîte de papier d’imprimante',
        'Whiteboard magnetisch' => 'Tableau blanc magnétique',
        'Documentkasten met slot' => 'Armoires à documents verrouillables',
        'Laptophouder' => 'Support pour ordinateur portable',
        'Kabelgoten set' => 'Kit de goulottes de câbles',
        'LED bureaulamp' => 'Lampe de bureau LED',
        // Productie & Distributie
        'Verpakkingsfolie 100m' => 'Film d’emballage 100 m',
        'Transportpallets' => 'Palettes de transport',
        'Productlabels' => 'Étiquettes produit',
        'Dozen 40x30x30cm' => 'Boîtes 40x30x30 cm',
        'Tape dispensers' => 'Dérouleurs de ruban adhésif',
        'Stapelbakken industrieel' => 'Bacs empilables industriels',
        'Magazijnstelling' => 'Rayonnage d’entrepôt',
        'Palletwikkelaar' => 'Filmeuse à palettes',
        'Karton versnijdmachine' => 'Massicot à carton',
        'Foliehechters' => 'Soudeuses de film',
    ];
    public static function generate(string $factuurnummer, string $type = 'verkoop', string $datum = '', ?array $custom_klant = null, ?string $branche = null, string $lang = 'nl'): array
    {
        $bedrijven = PDFGenerator::get_all_bedrijven();

        if (count($bedrijven) < 2) {
            $klant = PDFGenerator::fallback_klant();
            $leverancier = PDFGenerator::fallback_klant();
        } else {
            do {
                $klant = $bedrijven[array_rand($bedrijven)];
                $leverancier = $bedrijven[array_rand($bedrijven)];
            } while (($klant['btw'] ?? '') === ($leverancier['btw'] ?? ''));
        }

        // Custom gegevens instellen (afhankelijk van type)
        if (!empty($custom_klant) && !empty($custom_klant['naam']) && !empty($custom_klant['btw'])) {
            $custom = [
                'naam'  => $custom_klant['naam'],
                'adres' => $custom_klant['adres'] ?? '',
                'btw'   => $custom_klant['btw'],
                'branche' => $custom_klant['branche'] ?? null,
            ];

            if ($type === 'verkoop') {
                // Bij verkoopfactuur: gekozen gegevens zijn de leverancier
                $leverancier = $custom;
            } elseif ($type === 'aankoop') {
                // Bij aankoopfactuur: gekozen gegevens zijn de klant
                $klant = $custom;
            }
        }

        $factuurdatum = !empty($datum) ? $datum : date('Y-m-d');

        // Genereer producten op basis van branche (leverancier bepaalt aanbod)
        $selectedBranch = null;
        $allowedBranches = self::get_company_branches($leverancier);

        if (!empty($branche) && in_array($branche, $allowedBranches, true)) {
            $selectedBranch = $branche;
        } elseif (!empty($allowedBranches)) {
            $selectedBranch = $allowedBranches[array_rand($allowedBranches)];
        }

        if (!empty($selectedBranch)) {
            $regels = self::get_branch_products($selectedBranch, rand(2, 6), $lang);
        } else {
            $regels = PDFGenerator::get_random_products(rand(2, 6));
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

    private static function get_branch_products(?string $branche, int $aantal = 3, string $lang = 'nl'): array
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
            if ($lang === 'fr') {
                if (isset($product['omschrijving_fr']) && is_string($product['omschrijving_fr'])) {
                    $omschrijving = $product['omschrijving_fr'];
                } elseif (isset($product['omschrijving']['fr'])) {
                    $omschrijving = $product['omschrijving']['fr'];
                } elseif (isset(self::$FR_MAP[$omschrijving])) {
                    $omschrijving = self::$FR_MAP[$omschrijving];
                }
            }
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

    private static function get_company_branches(array $bedrijf): array
    {
        // Ondersteunt zowel 'branches' (array) als legacy 'branche' (string)
        if (!empty($bedrijf['branches']) && is_array($bedrijf['branches'])) {
            return array_values(array_filter(array_map('strval', $bedrijf['branches'])));
        }
        if (!empty($bedrijf['branche']) && is_string($bedrijf['branche'])) {
            return [$bedrijf['branche']];
        }
        return [];
    }
}
