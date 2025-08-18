<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class OctopusDownloadZipsWidget extends Widget_Base {

    public function get_name() {
        return 'octopus_download_zips';
    }

    public function get_title() {
        return 'Download ZIP’s';
    }

    public function get_icon() {
        return 'eicon-download-bold';
    }

    public function get_categories() {
        return ['factuurstudio'];
    }

    public function get_style_depends() {
        return ['elementor-icons'];
    }

    protected function register_controls() {
        // Content tab
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Download ZIP-bestanden', 'plugin-name'),
            ]
        );

        $this->add_control(
            'beschrijving',
            [
                'label' => __('Beschrijving', 'plugin-name'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Download de meest recente batch verkoop- of aankoopfacturen als ZIP.',
            ]
        );

        $this->end_controls_section();

        // Style tab
        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Stijl: Container', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-download-wrapper' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'label' => __('Typografie tekst', 'plugin-name'),
                'selector' => '{{WRAPPER}} .octopus-download-wrapper',
            ]
        );

        $this->end_controls_section();

        // Style: Buttons
        $this->start_controls_section(
            'section_buttons',
            [
                'label' => __('Stijl: Knoppen', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-button' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_background_color',
            [
                'label' => __('Achtergrondkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .octopus-button',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .octopus-button',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_shadow',
                'selector' => '{{WRAPPER}} .octopus-button',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Padding', 'plugin-name'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .octopus-button' => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
    $settings = $this->get_settings_for_display();
    $upload_dir = wp_upload_dir();
    $user_id = sanitize_text_field($_GET['user_id'] ?? '');

    $verkoop_files = glob($upload_dir['basedir'] . "/octopus-invoices/{$user_id}/verkoop/*.{pdf,xml}", GLOB_BRACE);
    $aankoop_files = glob($upload_dir['basedir'] . "/octopus-invoices/{$user_id}/aankoop/*.{pdf,xml}", GLOB_BRACE);

    echo '<div class="octopus-download-wrapper">';
    echo '<p>' . esc_html($settings['beschrijving']) . '</p>';

    echo '<div class="octopus-download-buttons">';
    if (!empty($verkoop_files)) {
        echo '<button class="octopus-button" data-type="verkoop" style="margin-right:10px;">📦 Download verkoopfacturen (ZIP)</button>';
    }
    if (!empty($aankoop_files)) {
        echo '<button class="octopus-button" data-type="aankoop">📦 Download aankoopfacturen (ZIP)</button>';
    }
    if (empty($verkoop_files) && empty($aankoop_files)) {
        echo '<p><em>Geen bestanden gevonden om te downloaden.</em></p>';
    }
    echo '</div>';

    echo '<div class="octopus-feedback" style="margin-top:10px;"></div>';
    echo '</div>';

    echo <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function () {
    let uid = localStorage.getItem('octopus_uid');
    if (!uid) {
        uid = 'u' + Math.random().toString(36).substring(2,10);
        localStorage.setItem('octopus_uid', uid);
    }
    const loc = new URL(window.location);
    if (!loc.searchParams.get('user_id')) {
        loc.searchParams.set('user_id', uid);
        window.location.replace(loc);
        return;
    }
    const userId = loc.searchParams.get('user_id');

    const buttons = document.querySelectorAll('.octopus-download-buttons .octopus-button');
    const feedback = document.querySelector('.octopus-feedback');

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            const type = this.getAttribute('data-type');
            feedback.innerHTML = '⏳ ZIP wordt aangemaakt...';

            fetch('{$this->get_ajax_url()}?action=octopus_download_zip_ajax&type=' + type + '&uid=' + userId)
            .then(response => {
                if (!response.ok) throw new Error('Fout bij downloaden');
                return response.blob();
            })
            .then(blob => {
                const zipUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = zipUrl;
                a.download = type + '_facturen.zip';
                document.body.appendChild(a);
                a.click();
                a.remove();
                feedback.innerHTML = '✅ ZIP-bestand gedownload.';
            })
            .catch(error => {
                feedback.innerHTML = '❌ Fout bij downloaden.';
            });
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
