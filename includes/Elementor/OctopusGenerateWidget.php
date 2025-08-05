<?php
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

class OctopusGenerateWidget extends Widget_Base {

    public function get_name() {
        return 'octopus_generate_facturen';
    }

    public function get_title() {
        return 'Genereer Facturen';
    }

    public function get_icon() {
        return 'eicon-file-download';
    }

    public function get_categories() {
        return ['factuurstudio'];
    }

    public function get_keywords() {
        return ['factuur', 'octopus', 'peppol', 'ubl', 'generate'];
    }

    public function get_style_depends() {
        return ['elementor-icons'];
    }

    

    protected function register_controls() {
        // Content
        $this->start_controls_section(
            'section_content',
            ['label' => __('Instellingen', 'plugin-name')]
        );

        $this->add_control(
            'intro_text',
            [
                'label' => __('Introductietekst', 'plugin-name'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Simuleer hier verkoop- of aankoopfacturen, optioneel met je eigen klant- of leveranciersgegevens.',
            ]
        );

        $this->end_controls_section();

        // Stijl: Algemene tekst
        $this->start_controls_section(
            'section_style_text',
            [
                'label' => __('Tekst', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-generate-wrapper, 
                     {{WRAPPER}} .octopus-generate-wrapper label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'label' => __('Typografie', 'plugin-name'),
                'selector' => '{{WRAPPER}} .octopus-generate-wrapper, {{WRAPPER}} label',
            ]
        );

        $this->end_controls_section();

        // Stijl: Inputs
        $this->start_controls_section(
            'section_style_inputs',
            [
                'label' => __('Inputvelden', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} input[type="text"], {{WRAPPER}} input[type="number"], {{WRAPPER}} input[type="date"]',
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => __('Tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => __('Achtergrondkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} input',
            ]
        );

        $this->end_controls_section();

        // Stijl: Knop
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => __('Knop', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_color',
            [
                'label' => __('Knopkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Knop tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-button' => 'color: {{VALUE}}',
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

        $this->end_controls_section();
    }


    protected function render() {
    $settings = $this->get_settings_for_display();

    $branch_options = '';
$branches_path = plugin_dir_path(__FILE__) . '../../data/branches.json';
if (file_exists($branches_path)) {
    $branches = json_decode(file_get_contents($branches_path), true);
    if (is_array($branches)) {
        foreach (array_keys($branches) as $branch) {
            $branch_options .= '<option value="' . esc_attr($branch) . '">' . esc_html($branch) . '</option>';
        }
    }
}


    echo '<div class="octopus-generate-wrapper">';

    // ✅ Meldingen (binnen wrapper, verdwijnen automatisch)
    if (isset($_GET['factuur_success'])) {
        echo '<div class="octopus-success octopus-dismissible">✅ Facturen succesvol gegenereerd!</div>';
    }

    if (isset($_GET['factuur_error'])) {
        echo '<div class="octopus-error octopus-dismissible">❌ Fout bij genereren: ' . esc_html($_GET['factuur_error']) . '</div>';
    }

    echo '<p>' . esc_html($settings['intro_text']) . '</p>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="octopus-generate-form">';
    echo '<input type="hidden" name="action" value="octopus_generate_facturen_frontend">';
    wp_nonce_field('octopus_generate_invoices');

    echo '<label><input type="checkbox" name="simulate_verkoop" value="1"> Verkoopfacturen</label><br>';
    echo '<label><input type="checkbox" name="simulate_aankoop" value="1"> Aankoopfacturen</label><br><br>';

    echo '<label>Aantal per type:</label><br>';
    echo '<input type="number" name="count" value="1" min="1" max="100"><br><br>';

    echo '<label>Datum bereik:</label><br>';
    echo '<input type="date" name="date_from"> tot ';
    echo '<input type="date" name="date_to"><br><br>';

    echo '<h4>Klant- of Leveranciersgegevens</h4>';

    echo '<label>Branche:</label><br>';
echo '<select name="branche"><option value="">-- Kies een branche --</option>' . $branch_options . '</select><br><br>';


    echo '<label>Naam:</label><br>';
    echo '<input type="text" name="naam" placeholder="bv. Octopus BV"><br>';

    echo '<label>Adres:</label><br>';
    echo '<input type="text" name="adres" placeholder="Straat 1, 1000 Brussel"><br>';

    echo '<label>BTW-nummer:</label><br>';
    echo '<input type="text" name="btw" placeholder="BE0123456789"><br><br>';

    echo '<label><input type="checkbox" name="anonymous"> Genereer anoniem (zonder klantgegevens)</label><br><br>';

    echo '<button type="submit" class="octopus-button">Genereer Facturen</button>';
    echo '</form></div>';

    // ✅ Meldingen automatisch laten verdwijnen
    echo <<<JS
    <style>
        .octopus-dismissible {
            transition: opacity 0.6s ease-out;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(() => {
                document.querySelectorAll('.octopus-dismissible').forEach(el => {
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 600);
                });
            }, 4000);
        });
    </script>
JS;
}

}
