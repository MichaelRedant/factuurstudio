<?php
use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

class OctopusVerstuurXmlWidget extends Widget_Base {

    public function get_name() {
        return 'octopus_verstuur_xml';
    }

    public function get_title() {
        return 'Verstuur XML-facturen';
    }

    public function get_icon() {
        return 'eicon-envelope';
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
            'default' => 'Selecteer facturen en verstuur de bijhorende XML-bestanden per e-mail.',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
    $settings = $this->get_settings_for_display();

    if (\Elementor\Plugin::instance()->editor->is_edit_mode()) {
        echo '<div class="octopus-verstuur-wrapper">';
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

    echo '<div class="octopus-verstuur-wrapper">';
    echo '<p>' . esc_html($settings['beschrijving']) . '</p>';

    if (empty($pdfs)) {
        echo '<p><em>Geen facturen gevonden om te verzenden.</em></p>';
        echo '</div>';
        return;
    }

    echo '<form method="post" class="octopus-verstuur-form">';
    echo '<input type="hidden" name="security" value="' . esc_attr(wp_create_nonce('octopus_mail_xml_by_selection')) . '">';

    echo '<label style="display:block; margin-bottom:10px;">';
    echo '<input type="checkbox" id="select_all_xml"> <strong>Alles selecteren</strong>';
    echo '</label>';

    foreach ($pdfs as $pdf_path) {
        $filename = basename($pdf_path);
        $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

        echo '<label style="display:block; margin-bottom:8px;">';
        echo '<input type="checkbox" name="selected_pdfs[]" value="' . esc_attr($filename) . '"> ';
        echo '<a href="' . esc_url($pdf_url) . '" target="_blank">' . esc_html($filename) . '</a>';
        echo '</label>';
    }

    echo '<p style="margin-top:1em;">';
    echo '<label for="email_to_xml"><strong>E-mailadres ontvanger:</strong></label><br>';
    echo '<input type="email" name="email_to_xml" id="email_to_xml" class="regular-text" required>';
    echo '</p>';

    echo '<input type="text" name="website" style="display:none;" autocomplete="off">';

    echo '<p><button type="submit" class="octopus-button">📤 Verstuur XML-bestanden</button></p>';
    echo '<div class="octopus-feedback" style="margin-top:10px;"></div>';
    echo '</form></div>';

    echo <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.octopus-verstuur-form');
    const master = document.getElementById('select_all_xml');
    const checkboxes = form.querySelectorAll('input[type="checkbox"][name="selected_pdfs[]"]');
    const button = form.querySelector('button[type="submit"]');
    const feedback = form.querySelector('.octopus-feedback');

    if (master && checkboxes.length > 0) {
        master.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = master.checked);
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append('action', 'octopus_mail_selected_xml');

        button.disabled = true;
        button.textContent = 'Bezig met verzenden...';

        fetch('{$this->get_ajax_url()}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedback.innerHTML = '<span style="color:green;">✅ ' + data.data.sent + ' XML-bestand(en) verzonden.</span>';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                feedback.innerHTML = '<span style="color:red;">❌ ' + (data.data.message || 'Versturen mislukt.') + '</span>';
            }
        })
        .catch(() => {
            feedback.innerHTML = '<span style="color:red;">⚠️ Er ging iets mis bij het verzenden.</span>';
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = '📤 Verstuur XML-bestanden';
        });
    });
});
</script>
EOT;
}

private function get_ajax_url() {
    return admin_url('admin-ajax.php');
}




}
