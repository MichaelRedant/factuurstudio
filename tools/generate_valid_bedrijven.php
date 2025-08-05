<?php
// Vereist SOAP-client in je PHP-installatie
ini_set('display_errors', 1);
error_reporting(E_ALL);

function check_vat_vies($btw_nummer)
{
    $countryCode = substr($btw_nummer, 0, 2); // 'BE'
    $vatNumber = preg_replace('/[^0-9]/', '', substr($btw_nummer, 2)); // '0400252142'

    try {
        $client = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");

        $params = [
            'countryCode' => $countryCode,
            'vatNumber' => $vatNumber
        ];

        $result = $client->checkVat($params);

        return [
            'valid' => $result->valid,
            'name' => $result->name,
            'address' => $result->address,
            'countryCode' => $result->countryCode,
            'vatNumber' => $result->vatNumber,
        ];
    } catch (SoapFault $e) {
        return [
            'valid' => false,
            'error' => $e->getMessage()
        ];
    }
}

// ✅ Voeg hier 25 echte Belgische btw-nummers toe
$bedrijven = [
    'BE0473416418', // Telenet BVBA
    'BE0400378485', // Colruyt Group
    'BE0402206045', // Delhaize Le Lion/De Leeuw
    'BE0214596464', // Bpost
    'BE0836585210', // MediaMarkt
    'BE0448826918', // Carrefour Belgium
    'BE0869763267', // Infrabel
    'BE0202239951', // Proximus
    'BE0203430576', // NMBS
    'BE0244142664', // VRT
    'BE0462920226', // KBC Bank
    'BE0403200393', // ING België
    'BE0403199702', // BNP Paribas Fortis
    'BE0629761216', // De Lijn
    'BE0474776396', // Vanden Borre
    'BE0824148721', // Bol.com België
    'BE0425258688', // Ikea België
    'BE0431110956', // UZ Leuven
    'BE0404484654', // Ethias
    'BE0403471401', // Touring
    'BE0426396954', // Standaard Boekhandel
    'BE0448746645', // DreamLand
    'BE0464949902', // Thomas Cook Retail Belgium
    'BE0403170701', // Electrabel
    'BE0681759451', // ✅ Xinu BVBA (voorbeeld)
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
        echo "❌ ongeldig\n";
    }

    // Even wachten om throttling te vermijden
    sleep(1);
}

// Save as JSON
file_put_contents(__DIR__ . '../bedrijven_valid.json', json_encode($valid_bedrijven, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\n✔️ Klaar! " . count($valid_bedrijven) . " bedrijven opgeslagen in bedrijven_valid.json\n";
