<?php

class UBLGenerator
{
    public static function generate_invoice_xml($factuurnummer, $klant, $leverancier, $regels, $btw, $totaal, $datum, $pdf_path = null)
    {
        $datum = date('Y-m-d', strtotime($datum));
        $duedate = date('Y-m-d', strtotime($datum . ' +30 days'));
        $currency = 'EUR';

        $subtotaal = array_sum(array_column($regels, 'subtotaal'));
        $vat_totals = [];
        foreach ($regels as $regel) {
            $rate = $regel['vat_rate'];
            if (!isset($vat_totals[$rate])) {
                $vat_totals[$rate] = ['taxable' => 0, 'tax' => 0];
            }
            $vat_totals[$rate]['taxable'] += $regel['subtotaal'];
            $vat_totals[$rate]['tax'] += $regel['vat_amount'];
        }
        $btw_total = array_sum(array_column($vat_totals, 'tax'));
        $subtotaal_fmt = number_format($subtotaal, 2, '.', '');
        $btw_fmt = number_format($btw_total, 2, '.', '');
        $totaal_fmt = number_format($totaal, 2, '.', '');

        // ✅ Start XML
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>
    <cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
    <cbc:ID>{$factuurnummer}</cbc:ID>
    <cbc:IssueDate>{$datum}</cbc:IssueDate>
    <cbc:DueDate>{$duedate}</cbc:DueDate>
    <cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
    <cbc:DocumentCurrencyCode>{$currency}</cbc:DocumentCurrencyCode>
    <cbc:BuyerReference>Simulatie</cbc:BuyerReference>
XML;

        // ✅ Embedded PDF toevoegen (Peppol-compliant & Octopus-thumbnail)
if ($pdf_path && file_exists($pdf_path)) {
    $pdf_data = base64_encode(file_get_contents($pdf_path));
    $filename = basename($pdf_path);

    $xml .= <<<XML
    <cac:AdditionalDocumentReference>
        <cbc:ID>PDF-1</cbc:ID>
        <cac:Attachment>
            <cbc:EmbeddedDocumentBinaryObject mimeCode="application/pdf" filename="{$filename}">{$pdf_data}</cbc:EmbeddedDocumentBinaryObject>
        </cac:Attachment>
    </cac:AdditionalDocumentReference>
XML;
}


        // ✅ Leverancier
        $xml .= <<<XML
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cbc:EndpointID schemeID="0106">{$leverancier['btw']}</cbc:EndpointID>
            <cac:PostalAddress>
                <cbc:StreetName>{$leverancier['adres']}</cbc:StreetName>
                <cbc:CityName>Brussel</cbc:CityName>
                <cbc:PostalZone>1000</cbc:PostalZone>
                <cbc:CountrySubentity>BE</cbc:CountrySubentity>
                <cac:Country><cbc:IdentificationCode>BE</cbc:IdentificationCode></cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>{$leverancier['btw']}</cbc:CompanyID>
                <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>{$leverancier['naam']}</cbc:RegistrationName>
                <cbc:CompanyID schemeID="0106">{$leverancier['btw']}</cbc:CompanyID>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
XML;

        // ✅ Klant
        $xml .= <<<XML
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cbc:EndpointID schemeID="9944">{$klant['btw']}</cbc:EndpointID>
            <cac:PostalAddress>
                <cbc:StreetName>{$klant['adres']}</cbc:StreetName>
                <cbc:CityName>Brugge</cbc:CityName>
                <cbc:PostalZone>8000</cbc:PostalZone>
                <cbc:CountrySubentity>BE</cbc:CountrySubentity>
                <cac:Country><cbc:IdentificationCode>BE</cbc:IdentificationCode></cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>{$klant['btw']}</cbc:CompanyID>
                <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>{$klant['naam']}</cbc:RegistrationName>
                <cbc:CompanyID schemeID="0106">{$klant['btw']}</cbc:CompanyID>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
XML;

        // ✅ Betaling
        $xml .= <<<XML
    <cac:PaymentMeans>
        <cbc:PaymentMeansCode>30</cbc:PaymentMeansCode>
        <cbc:PaymentID>{$factuurnummer}</cbc:PaymentID>
        <cac:PayeeFinancialAccount>
            <cbc:ID>BE68539007547034</cbc:ID>
            <cbc:Name>{$leverancier['naam']}</cbc:Name>
        </cac:PayeeFinancialAccount>
    </cac:PaymentMeans>
XML;

        // ✅ Belasting
        $xml .= <<<XML
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{$currency}">{$btw_fmt}</cbc:TaxAmount>
XML;

        foreach ($vat_totals as $rate => $values) {
            $taxable_fmt = number_format($values['taxable'], 2, '.', '');
            $tax_fmt = number_format($values['tax'], 2, '.', '');
            $xml .= <<<XML
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="{$currency}">{$taxable_fmt}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="{$currency}">{$tax_fmt}</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>{$rate}</cbc:Percent>
                <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>

XML;
}

        $xml .= "    </cac:TaxTotal>";

        // ✅ Totalen
        $xml .= <<<XML
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="{$currency}">{$subtotaal_fmt}</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="{$currency}">{$subtotaal_fmt}</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="{$currency}">{$totaal_fmt}</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="{$currency}">{$totaal_fmt}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
XML;

        // ✅ Factuurlijnen
        $line_id = 1;
        foreach ($regels as $regel) {
            $omschrijving = htmlspecialchars($regel['omschrijving']);
            $aantal = $regel['aantal'];
            $prijs = number_format($regel['prijs'], 2, '.', '');
            $lijn_totaal = number_format($regel['subtotaal'], 2, '.', '');
            $line_taxable = number_format($regel['subtotaal'], 2, '.', '');
            $line_tax_amount = number_format($regel['vat_amount'], 2, '.', '');
            $rate = $regel['vat_rate'];

            $xml .= <<<LINE
    <cac:InvoiceLine>
        <cbc:ID>{$line_id}</cbc:ID>
        <cbc:InvoicedQuantity unitCode="C62">{$aantal}</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="{$currency}">{$lijn_totaal}</cbc:LineExtensionAmount>
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{$currency}">{$line_tax_amount}</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="{$currency}">{$line_taxable}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{$currency}">{$line_tax_amount}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>S</cbc:ID>
                    <cbc:Percent>{$rate}</cbc:Percent>
                    <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        <cac:Item>
            <cbc:Name>{$omschrijving}</cbc:Name>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>S</cbc:ID>
                <cbc:Percent>21</cbc:Percent>
                <cac:TaxScheme><cbc:ID>VAT</cbc:ID></cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="{$currency}">{$prijs}</cbc:PriceAmount>
        </cac:Price>
    </cac:InvoiceLine>
LINE;
            $line_id++;
        }

        $xml .= "\n</Invoice>";

        return $xml;
    }
}