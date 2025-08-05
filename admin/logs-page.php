<?php
// /admin/log-page.php

if (!current_user_can('manage_options')) {
    wp_die('⛔ Geen toegang.');
}

require_once plugin_dir_path(__DIR__) . 'includes/logger.php';

$log_entries = OctopusEmailLogger::get_all();
$log_entries = array_reverse($log_entries); // Laatste eerst

$cleared = isset($_GET['cleared']) && $_GET['cleared'] == 1;

if (isset($_POST['clear_log']) && check_admin_referer('octopus_clear_log')) {
    OctopusEmailLogger::clear();
    wp_redirect(admin_url('admin.php?page=facturatiegenerator_logs&cleared=1'));
    exit;
}

?>

<div class="wrap">
    <h1>📨 E-mailverzendlog – Octopus Facturatiegenerator</h1>

    <?php if ($cleared): ?>
        <div class="notice notice-success"><p>✅ Log succesvol gewist.</p></div>
    <?php endif; ?>

    <form method="post" style="margin-bottom: 20px;">
        <?php wp_nonce_field('octopus_clear_log'); ?>
        <input type="submit" name="clear_log" class="button button-secondary" value="🧹 Log wissen" onclick="return confirm('Ben je zeker dat je de volledige log wil wissen?');" />
    </form>

    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th>🕒 Datum</th>
                <th>📧 Naar</th>
                <th>📎 Bestand(en)</th>
                <th>📂 Type</th>
                <th>✉️ Modus</th>
                <th>👤 Gebruiker</th>
                <th>🌍 IP</th>
                <th>✅ Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($log_entries)): ?>
                <tr><td colspan="8">Nog geen verzonden e-mails gelogd.</td></tr>
            <?php else: ?>
                <?php foreach ($log_entries as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['timestamp']); ?></td>
                        <td><?php echo esc_html($entry['to']); ?></td>
                        <td>
                            <?php
                            foreach ($entry['files'] as $file) {
                                echo esc_html($file) . "<br>";
                            }
                            ?>
                        </td>
                        <td><?php echo esc_html($entry['type']); ?></td>
                        <td><?php echo esc_html($entry['mode']); ?></td>
                        <td><?php echo esc_html($entry['user'] ?? '-'); ?></td>
                        <td><?php echo esc_html($entry['ip'] ?? '-'); ?></td>
                        <td><?php echo esc_html($entry['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
