<?php require_once plugin_dir_path(__DIR__) . '/../i18n.php'; ?>
<form method="post" action="" style="max-width: 700px; margin-top: 20px;">
    <?php wp_nonce_field('octopus_generate_invoices'); ?>

    <h2 class="title"><?php echo esc_html(octo_t('Genereer demo-facturen', 'Générer des factures de démonstration')); ?></h2>

    <table class="form-table" style="width:100%">
        <tr>
            <th scope="row"><?php echo esc_html(octo_t('Soort facturen','Type de factures')); ?></th>
            <td>
                <label><input type="checkbox" name="simulate_verkoop" value="1"> <?php echo esc_html(octo_t('Verkoopfacturen','Factures de vente')); ?></label><br>
                <label><input type="checkbox" name="simulate_aankoop" value="1"> <?php echo esc_html(octo_t('Aankoopfacturen','Factures d\'achat')); ?></label>
            </td>
        </tr>
        <tr>
            <th><?php echo esc_html(octo_t('Aantal per type','Nombre par type')); ?></th>
            <td><input type="number" name="count" min="1" max="100" value="1" /></td>
        </tr>
        <tr>
            <th colspan="2"><h4><?php echo esc_html(octo_t('Extra opties','Options supplémentaires')); ?></h4></th>
        </tr>
        <tr>
            <th><?php echo esc_html(octo_t('Bedrijfsnaam','Nom de l\'entreprise')); ?></th>
            <td><input type="text" name="naam" placeholder="bv. Xinu BV" /></td>
        </tr>
        <tr>
            <th><?php echo esc_html(octo_t('Adres','Adresse')); ?></th>
            <td><input type="text" name="adres" placeholder="Straat 1, 1000 Brussel" /></td>
        </tr>
        <tr>
            <th><?php echo esc_html(octo_t('BTW-nummer','Numéro de TVA')); ?></th>
            <td><input type="text" name="btw" placeholder="BE0123456789" /></td>
        </tr>
        <tr>
            <th><?php echo esc_html(octo_t('Branche','Branche')); ?></th>
            <td>
                <?php
                $branches_path = plugin_dir_path(__DIR__) . '/../../data/branches.json';
                $branch_options = [];
                if (file_exists($branches_path)) {
                    $json = file_get_contents($branches_path);
                    $data = json_decode($json, true);
                    if (is_array($data)) {
                        $branch_options = array_keys($data);
                    }
                }
                ?>
                <select name="branche">
                    <option value=""><?php echo esc_html(octo_t('-- Kies branche (optioneel) --','-- Choisissez une branche (optionnel) --')); ?></option>
                    <?php foreach ($branch_options as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>"><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php echo esc_html(octo_t('Anoniem','Anonyme')); ?></th>
            <td><input type="checkbox" name="anonymous" /> <?php echo esc_html(octo_t('Genereer zonder klantgegevens','Générer sans données client')); ?></td>
        </tr>
    </table>

    <p><button type="submit" class="button button-primary"><?php echo esc_html(octo_t('Start genereren','Démarrer la génération')); ?></button></p>
</form>

<?php
// VERWERK formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'octopus_generate_invoices')) {
    require_once plugin_dir_path(__DIR__) . '/../../FactuurGenerator.php';
    $generator = new FactuurGenerator($_POST);
    $result = $generator->generate();

    if ($result === true) {
        echo '<div class="notice notice-success" style="padding:10px;background:#e7f7ed;border-left:5px solid green;">' . esc_html(octo_t('Facturen gegenereerd!','Factures générées !')) . '</div>';
    } else {
        echo '<div class="notice notice-error" style="padding:10px;background:#fbeaea;border-left:5px solid red;">' . esc_html(octo_t('Fout: ','Erreur : ')) . esc_html($result) . '</div>';
    }
}
?>
