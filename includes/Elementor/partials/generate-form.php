<form method="post" action="" style="max-width: 700px; margin-top: 20px;">
    <?php wp_nonce_field('octopus_generate_invoices'); ?>

    <h2 class="title">🧾 Genereer demo-facturen</h2>

    <table class="form-table" style="width:100%">
        <tr>
            <th scope="row">Soort facturen</th>
            <td>
                <label><input type="checkbox" name="simulate_verkoop" value="1"> Verkoopfacturen</label><br>
                <label><input type="checkbox" name="simulate_aankoop" value="1"> Aankoopfacturen</label>
            </td>
        </tr>
        <tr>
            <th>Aantal per type</th>
            <td><input type="number" name="count" min="1" max="100" value="1" /></td>
        </tr>
        <tr>
            <th colspan="2"><h4>Extra opties</h4></th>
        </tr>
        <tr>
            <th>Klantnaam</th>
            <td><input type="text" name="naam" placeholder="bv. Xinu BV" /></td>
        </tr>
        <tr>
            <th>Adres</th>
            <td><input type="text" name="adres" placeholder="Straat 1, 1000 Brussel" /></td>
        </tr>
        <tr>
            <th>BTW-nummer</th>
            <td><input type="text" name="btw" placeholder="BE0123456789" /></td>
        </tr>
        <tr>
            <th>Anoniem</th>
            <td><input type="checkbox" name="anonymous" /> Genereer zonder klantgegevens</td>
        </tr>
    </table>

    <p><button type="submit" class="button button-primary">🚀 Start Generatie</button></p>
</form>

<?php
// VERWERK formulier (kan ook in aparte handler.php voor nettere structuur)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'octopus_generate_invoices')) {
    require_once plugin_dir_path(__DIR__) . '/../../FactuurGenerator.php';
    $generator = new FactuurGenerator($_POST);
    $result = $generator->generate();

    if ($result === true) {
        echo '<div class="notice notice-success" style="padding:10px;background:#e7f7ed;border-left:5px solid green;">✅ Facturen gegenereerd!</div>';
    } else {
        echo '<div class="notice notice-error" style="padding:10px;background:#fbeaea;border-left:5px solid red;">❌ Fout: ' . esc_html($result) . '</div>';
    }
}
?>
