<?php
require_once plugin_dir_path(__DIR__) . '/i18n.php';
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Plugin;

class OctopusVerwijderFacturenWidget extends Widget_Base {

    public function get_name() {
        return 'octopus_verwijder_facturen';
    }

    public function get_title() {
        return function_exists('octo_t') ? octo_t('Verwijder Facturen','Supprimer des factures') : 'Verwijder Facturen';
    }

    public function get_icon() {
        return 'eicon-trash';
    }

    public function get_categories() {
        return ['factuurstudio'];
    }

    protected function register_controls() {
        $this->start_controls_section('section_content', [
            'label' => __('Instellingen', 'plugin-name'),
        ]);

        $this->add_control('beschrijving', [
            'label' => __('Beschrijving', 'plugin-name'),
            'type' => Controls_Manager::TEXTAREA,
            'default' => 'Selecteer en verwijder gegenereerde facturen uit de lijst.',
        ]);

        $this->end_controls_section();
    }

   protected function render() {
    $settings = $this->get_settings_for_display();

    if (\Elementor\Plugin::instance()->editor->is_edit_mode()) {
        echo '<div class="octopus-verwijder-wrapper">';
        echo '<p>' . esc_html($settings['beschrijving']) . '</p>';
        echo '<em>Preview in editor. Facturen worden hier weergegeven zodra ze beschikbaar zijn.</em>';
        echo '</div>';
        return;
    }

    $upload_dir = wp_upload_dir();
    $verkoop_dir = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/verkoop/';
    $aankoop_dir = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/aankoop/';
    $pdfs = array_merge(
        glob($verkoop_dir . '*.pdf') ?: [],
        glob($aankoop_dir . '*.pdf') ?: []
    );

    echo '<div class="octopus-verwijder-wrapper">';
    echo '<p>' . esc_html($settings['beschrijving']) . '</p>';

    if (empty($pdfs)) {
        echo '<p><em>Geen facturen gevonden om te verwijderen.</em></p>';
        echo '</div>';
        return;
    }

    echo '<form method="post" class="octopus-verwijder-form">';
    echo '<input type="hidden" name="security" value="' . esc_attr(wp_create_nonce('octopus_delete_selected_files')) . '">';

    echo '<label style="display:block; margin-bottom:10px;">';
    echo '<input type="checkbox" id="select_all_delete"> <strong>Alles selecteren</strong>';
    echo '</label>';

    foreach ($pdfs as $pdf_path) {
        $filename = basename($pdf_path);
        $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

        echo '<label style="display:block; margin-bottom:8px;">';
        echo '<input type="checkbox" name="delete_pdfs[]" value="' . esc_attr($filename) . '"> ';
        echo '<a href="' . esc_url($pdf_url) . '" target="_blank">' . esc_html($filename) . '</a>';
        echo '</label>';
    }

    echo '<button type="submit" class="octopus-button" style="margin-top: 15px;">🗑️ Verwijder geselecteerde facturen</button>';
    echo '<div class="octopus-feedback" style="margin-top:10px;"></div>';
    echo '</form></div>';

    // ✅ AJAX + Select all + Feedback
    echo <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.octopus-verwijder-form');
    const master = document.getElementById('select_all_delete');
    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="delete_pdfs[]"]');
    const button = form.querySelector('button[type="submit"]');
    const feedback = form.querySelector('.octopus-feedback');

    if (master && checkboxes.length > 0) {
        master.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = master.checked);
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('Ben je zeker dat je deze facturen wil verwijderen?')) return;

        const formData = new FormData(form);
        formData.append('action', 'octopus_delete_selected_files');

        button.disabled = true;
        button.textContent = 'Even bezig...';

        fetch('{$this->get_ajax_url()}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedback.innerHTML = '<span style="color:green;">🗑️ ' + data.data.deleted + ' bestand(en) verwijderd.</span>';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                feedback.innerHTML = '<span style="color:red;">❌ Verwijderen mislukt.</span>';
            }
        })
        .catch(() => {
            feedback.innerHTML = '<span style="color:red;">⚠️ Er ging iets mis bij het verzenden.</span>';
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = '🗑️ Verwijder geselecteerde facturen';
        });
    });
});
</script>
EOT;
}

/**
 * Helper om admin-ajax.php URL op te halen.
 */
private function get_ajax_url() {
    return admin_url('admin-ajax.php');
}

}
