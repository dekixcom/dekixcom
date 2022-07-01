<?php
/**
 * Project: Minerva KB
 * Copyright: 2015-2016 @KonstruktStudio
 */

require_once(MINERVA_KB_PLUGIN_DIR . 'lib/helpers/icon-options.php');

/**
 * Class MinervaKB_TicketTaxonomyPages
 * Manages shared ticket taxonomy settings
 */
class MinervaKB_TicketTaxonomyPages {

    private $info;

    /**
     * Constructor
     */
    public function __construct($deps) {

        $this->setup_dependencies($deps);

        // topic settings
        add_action('mkb_ticket_type_edit_form_fields', array($this, 'ticket_type_edit_screen_html'), 10, 2);
        add_action('mkb_ticket_priority_edit_form_fields', array($this, 'ticket_type_edit_screen_html'), 10, 2);
        add_action('mkb_ticket_product_edit_form_fields', array($this, 'ticket_type_edit_screen_html'), 10, 2);
        add_action('mkb_ticket_department_edit_form_fields', array($this, 'ticket_type_edit_screen_html'), 10, 2);

        // edit & create
        add_action('edited_term', array($this, 'save_ticket_tax_meta'), 10, 3);
        add_action('create_term', array($this, 'save_ticket_tax_meta'), 10, 3);

        // delete
        add_action('delete_term', array($this, 'delete_ticket_tax_meta'), 10, 3);
    }

    /**
     * Sets up dependencies
     * @param $deps
     */
    private function setup_dependencies($deps) {
        if (isset($deps['info'])) {
            $this->info = $deps['info'];
        }
    }

    /**
     * Ticket type edit screen settings
     * @param $term
     */
    public function ticket_type_edit_screen_html($term, $taxonomy) {

        $term_id = $term->term_id;
        $term_meta = get_option( "taxonomy_" . $taxonomy . "_" . $term_id );

        $settings_helper = new MKB_SettingsBuilder(array(
            'topic' => true, // TODO: rename
            'no_tabs' => true
        ));

        $options = array(
            array(
                'id' => 'color',
                'type' => 'color',
                'label' => __( 'Ticket taxonomy color', 'minerva-kb' ),
                'default' => '#4a90e2',
                'description' => __( 'Select a color for ticket taxonomy', 'minerva-kb' )
            ),
            array(
                'id' => 'icon',
                'type' => 'icon_select',
                'label' => __( 'Ticket taxonomy icon', 'minerva-kb' ),
                'default' => 'fa-list-alt',
                'description' => __( 'Select an icon for ticket taxonomy', 'minerva-kb' )
            )
        );

        ?>

        </tbody>
        <tbody class="mkb-term-settings">

        <?php

        foreach ( $options as $option ): ?>

            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="term_meta[<?php echo esc_attr($option["id"]); ?>]"><?php echo esc_html($option["label"]); ?></label>
                </th>
                <td>
                    <?php

                    $value = isset( $term_meta[$option["id"]] ) ? stripslashes($term_meta[$option["id"]]) : $option['default'];

                    $settings_helper->render_option(
                        $option["type"],
                        $value,
                        $option
                    );

                    ?>
                    <p class="description"><?php echo esc_html($option["description"]); ?></p>
                </td>
            </tr>
        <?php

        endforeach;

        ?>

        <!-- WPML controls box fix begin -->
        <tr class="form-field">
            <th scope="row" valign="top"></th>
            <td></td>
        </tr>
        <!-- WPML controls box fix end -->

        </tbody>
        <?php
    }

    /**
     * Handle term settings save
     * @param $term_id
     */
    public function save_ticket_tax_meta( $term_id, $tt_id, $taxonomy ) {
        $handled_taxonomies = array(
            'mkb_ticket_type',
            'mkb_ticket_priority',
            'mkb_ticket_product',
            'mkb_ticket_department'
        );

        if (!in_array($taxonomy, $handled_taxonomies)) {
            return;
        }

        if (isset($_POST['term_meta'])) {
            $term_meta = get_option("taxonomy_" . $taxonomy . "_" . $term_id);
            $cat_keys = array_keys($_POST['term_meta']);

            foreach ($cat_keys as $key) {
                if (isset ($_POST['term_meta'][$key])) {
                    $term_meta[$key] = $_POST['term_meta'][$key];
                }
            }

            update_option("taxonomy_" . $taxonomy . "_" . $term_id, $term_meta);
        }
    }

    /**
     * Handle tax settings delete
     * @param $term_id
     */
    public function delete_ticket_tax_meta( $term_id, $tt_id, $taxonomy ) {
        $handled_taxonomies = array(
            'mkb_ticket_type',
            'mkb_ticket_priority',
            'mkb_ticket_product',
            'mkb_ticket_department'
        );

        if (!in_array($taxonomy, $handled_taxonomies)) {
            return;
        }

        delete_option( "taxonomy_" . $taxonomy . "_" . $term_id );
    }
}
