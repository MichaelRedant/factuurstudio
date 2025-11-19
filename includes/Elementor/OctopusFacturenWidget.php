<?php
require_once plugin_dir_path(__DIR__) . '/i18n.php';
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

class OctopusFacturenWidget extends Widget_Base {

    public function get_name() {
        return 'octopus_facturen_overzicht';
    }

    public function get_title() {
        return function_exists('octo_t') ? octo_t('Facturenoverzicht','Aperçu des factures') : 'Facturenoverzicht';
    }

    public function get_icon() {
        return 'eicon-posts-ticker';
    }

    public function get_categories() {
        return ['factuurstudio'];
    }

    public function get_keywords() {
        return ['factuur', 'overzicht', 'octopus', 'peppol'];
    }

    public function get_style_depends() {
        return ['elementor-icons'];
    }

    protected function register_controls() {
        // Content
        $this->start_controls_section(
            'content_section',
            ['label' => __('Instellingen', 'plugin-name')]
        );

        $this->add_control(
            'intro_text',
            [
                'label' => __('Introductietekst', 'plugin-name'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Hieronder zie je een overzicht van alle gegenereerde facturen.',
            ]
        );

        $this->end_controls_section();

        // Tabelstijl
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Tabelstijl', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'table_bg_color',
            [
                'label' => __('Tabel achtergrondkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-facturen-table' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'row_alt_bg',
            [
                'label' => __('Alternatieve rijkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-facturen-table tr:nth-child(even)' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-facturen-table td,
                     {{WRAPPER}} .octopus-facturen-table th' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'table_typography',
                'label' => __('Typografie', 'plugin-name'),
                'selector' => '{{WRAPPER}} .octopus-facturen-table td, {{WRAPPER}} .octopus-facturen-table th',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'table_border',
                'selector' => '{{WRAPPER}} .octopus-facturen-table td, {{WRAPPER}} .octopus-facturen-table th',
            ]
        );

        $this->add_control(
            'cell_padding',
            [
                'label' => __('Cel padding', 'plugin-name'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .octopus-facturen-table td,
                     {{WRAPPER}} .octopus-facturen-table th' => 'padding: {{TOP}} {{RIGHT}} {{BOTTOM}} {{LEFT}};',
                ],
            ]
        );

        $this->end_controls_section();

         // Acties en formulier
        $this->start_controls_section(
            'actions_style',
            [
                'label' => __('Acties', 'plugin-name'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'actions_gap',
            [
                'label' => __('Afstand tussen elementen', 'plugin-name'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => __('Input achtergrond', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions .octopus-input' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => __('Input tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions .octopus-input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} .octopus-actions .octopus-input',
            ]
        );

        $this->add_control(
            'input_padding',
            [
                'label' => __('Input padding', 'plugin-name'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions .octopus-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Knop achtergrond', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions .octopus-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Knop tekstkleur', 'plugin-name'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions .octopus-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .octopus-actions .octopus-button',
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Knop padding', 'plugin-name'),
                'type' => Controls_Manager::DIMENSIONS,
                'selectors' => [
                    '{{WRAPPER}} .octopus-actions .octopus-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'actions_typography',
                'selector' => '{{WRAPPER}} .octopus-actions .octopus-input, {{WRAPPER}} .octopus-actions .octopus-button',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
    $upload_dir = wp_upload_dir();
    $verkoop_dir = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/verkoop/';
    $aankoop_dir = trailingslashit($upload_dir['basedir']) . 'octopus-invoices/aankoop/';
    $verkoop = glob($verkoop_dir . '*.pdf') ?: [];
    $aankoop = glob($aankoop_dir . '*.pdf') ?: [];
    $pdfs = array_merge($verkoop, $aankoop);

    echo '<div class="octopus-facturen-wrapper">';
    echo '<style>
    .octopus-facturen-wrapper .octopus-actions{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;margin-top:15px;}
    .octopus-facturen-wrapper .octopus-email{flex:1 1 250px;display:flex;flex-direction:column;}
    .octopus-facturen-wrapper .octopus-label{margin-bottom:4px;font-weight:600;}
    .octopus-facturen-wrapper .octopus-input{padding:8px 12px;border:1px solid #ccc;border-radius:4px;width:100%;}
    .octopus-facturen-wrapper .octopus-button{padding:8px 16px;border:none;border-radius:4px;cursor:pointer;}
    .octopus-facturen-wrapper .octopus-button.octopus-delete{background:#e74c3c;color:#fff;}
    .octopus-facturen-wrapper .octopus-button.octopus-send{background:#3498db;color:#fff;}
    .octopus-facturen-wrapper .octopus-button:disabled{opacity:0.6;cursor:not-allowed;}
    .octopus-facturen-wrapper .octopus-feedback{flex-basis:100%;margin-top:5px;}
    </style>';
    echo '<p>' . esc_html($this->get_settings('intro_text')) . '</p>';

    if (empty($pdfs)) {
        echo '<p><em>Geen gegenereerde facturen gevonden.</em></p>';
        echo '</div>';
        return;
    }

    echo '<form method="post" class="octopus-delete-form">';
    echo '<input type="hidden" name="delete_security" value="' . esc_attr(wp_create_nonce('octopus_delete_selected_files')) . '">';
    echo '<input type="hidden" name="mail_security" value="' . esc_attr(wp_create_nonce('octopus_mail_xml_by_selection')) . '">';

    echo <<<HTML
    <div style="margin-bottom: 15px;">
        <label><input type="radio" name="filter_type" value="alle" checked> Alle</label>
        <label style="margin-left: 10px;"><input type="radio" name="filter_type" value="verkoop"> Verkoop</label>
        <label style="margin-left: 10px;"><input type="radio" name="filter_type" value="aankoop"> Aankoop</label>
    </div>
    <div id="factuur-counter" style="margin-bottom:10px; font-weight: bold;">Aantal facturen: 0</div>
HTML;

    echo '<table class="octopus-facturen-table" id="facturenTable">';
    echo '<thead>
        <tr>
            <th><input type="checkbox" id="select_all_facturen"></th>
            <th onclick="sortTable(1)">Factuur</th>
            <th onclick="sortTable(2)">Klant</th>
            <th onclick="sortTable(3)">Leverancier</th>
            <th onclick="sortTable(4)">Datum</th>
        </tr>
    </thead><tbody>';

    foreach ($pdfs as $pdf_path) {
        $filename = basename($pdf_path);
        $base = basename($filename, '.pdf');
        $type = strpos($pdf_path, '/verkoop/') !== false ? 'verkoop' : 'aankoop';
        $url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

        $json_path = trailingslashit($upload_dir['basedir']) . "octopus-invoices/{$type}/{$base}.json";
        $klant = $leverancier = $datum = 'Onbekend';
        if (file_exists($json_path)) {
            $json = json_decode(file_get_contents($json_path), true);
            $klant = esc_html($json['klant'] ?? $klant);
            $leverancier = esc_html($json['leverancier'] ?? $leverancier);
            $datum = esc_html($json['datum'] ?? $datum);
        }

        echo '<tr data-type="' . esc_attr($type) . '">';
        echo '<td><input type="checkbox" name="selected_pdfs[]" value="' . esc_attr($filename) . '"></td>';
        echo '<td><a href="' . esc_url($url) . '" target="_blank">' . esc_html($filename) . '</a></td>';
        echo '<td>' . $klant . '</td>';
        echo '<td>' . $leverancier . '</td>';
        echo '<td>' . $datum . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    echo '<div class="octopus-actions">';
    echo '<div class="octopus-email">';
    echo '<label for="email_to_xml" class="octopus-label">E-mailadres ontvanger</label>';
    echo '<input type="email" name="email_to_xml" id="email_to_xml" class="octopus-input" placeholder="naam@voorbeeld.com" required>';
    echo '</div>';
    
    echo '<button type="button" id="send_xml_btn" class="octopus-button octopus-send">📤 Verstuur geselecteerde XML</button>';
    echo '<button type="submit" class="octopus-button octopus-delete">🗑️ Verwijder geselecteerde facturen</button>';
    echo '<div class="octopus-feedback"></div>';
    echo '</div>';

    echo '<div id="pagination" style="margin-top: 15px;"></div>';
    echo '</form></div>';

    // ✅ JavaScript
    echo <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById("facturenTable");
    const rows = table.querySelectorAll("tbody tr");
    const form = document.querySelector('.octopus-delete-form');
    const master = document.getElementById('select_all_facturen');
    const feedback = form.querySelector('.octopus-feedback');
    const deleteBtn = form.querySelector('button[type="submit"]');
    const sendBtn = document.getElementById('send_xml_btn');
    const emailInput = document.getElementById('email_to_xml');
    const rowsPerPage = 20;
    let currentPage = 1;
    let activeFilter = "alle";

    const filterRadios = document.querySelectorAll('input[name="filter_type"]');
    filterRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            activeFilter = this.value;
            paginate(currentPage);
        });
    });

    function paginate(page) {
        const allRows = Array.from(rows);
        const visibleRows = allRows.filter(row => activeFilter === "alle" || row.dataset.type === activeFilter);
        const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
        currentPage = Math.min(page, totalPages) || 1;

        updateCounter(visibleRows.length);

        allRows.forEach(row => row.style.display = 'none');
        visibleRows.forEach((row, i) => {
            if (i >= (currentPage - 1) * rowsPerPage && i < currentPage * rowsPerPage) {
                row.style.display = '';
            }
        });

        const pag = document.getElementById("pagination");
        pag.innerHTML = "";
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            btn.className = "button" + (i === currentPage ? " button-primary" : "");
            btn.onclick = () => paginate(i);
            pag.appendChild(btn);
        }
    }

    function updateCounter(count) {
        const counter = document.getElementById('factuur-counter');
        if (counter) counter.textContent = 'Aantal facturen: ' + count;
    }

    window.sortTable = function (colIndex) {
        const tbody = table.querySelector("tbody");
        const rowsArray = Array.from(tbody.querySelectorAll("tr"));
        const asc = table.getAttribute("data-sort-dir") !== "asc";

        rowsArray.sort((a, b) => {
            const aText = a.children[colIndex].innerText.toLowerCase();
            const bText = b.children[colIndex].innerText.toLowerCase();
            return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
        });

        rowsArray.forEach(row => tbody.appendChild(row));
        table.setAttribute("data-sort-dir", asc ? "asc" : "desc");
        paginate(1);
    };

    // Alles selecteren
    if (master) {
        master.addEventListener('change', function () {
            form.querySelectorAll('input[type="checkbox"][name="selected_pdfs[]"]').forEach(cb => {
                cb.checked = master.checked;
            });
        });
    }

    // AJAX verwijderen
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!confirm('Ben je zeker dat je deze facturen wil verwijderen?')) return;

        const formData = new FormData(form);
        formData.append('action', 'octopus_delete_selected_files');

        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Even bezig...';

        fetch("{$this->get_ajax_url()}", {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                feedback.innerHTML = '<span style="color:green;">🗑️ factuur/facturen verwijderd.</span>';
                setTimeout(() => window.location.reload(), 1000);
            } else {
                feedback.innerHTML = '<span style="color:red;">❌ Verwijderen mislukt.</span>';
            }
        })
        .catch(() => {
            feedback.innerHTML = '<span style="color:red;">⚠️ Fout bij verbinden met server.</span>';
        })
        .finally(() => {
            deleteBtn.disabled = false;
            deleteBtn.textContent = '🗑️ Verwijder geselecteerde facturen';
        });
    });

    // AJAX versturen
    if (sendBtn) {
        sendBtn.addEventListener('click', function () {
            const checked = form.querySelectorAll('input[name="selected_pdfs[]"]:checked');
            if (checked.length === 0) {
                alert('Selecteer minstens één factuur.');
                return;
            }
            if (!emailInput.value) {
                alert('Vul een e-mailadres in.');
                return;
            }

            const formData = new FormData(form);
            formData.append('action', 'octopus_mail_selected_xml');

            sendBtn.disabled = true;
            sendBtn.textContent = 'Bezig met verzenden...';

            fetch("{$this->get_ajax_url()}", {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
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
                sendBtn.disabled = false;
                sendBtn.textContent = '📤 Verstuur geselecteerde XML';
            });
        });
    }

    paginate(1);
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
