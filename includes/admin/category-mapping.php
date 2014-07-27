<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WC_Pricefiles_Admin_Category_Mapping extends WC_Pricefiles_Admin
{

    function __construct($plugin_slug)
    {
        $this->plugin_slug = $plugin_slug;

        add_filter($this->plugin_slug . '_option_tabs', array($this, 'add_options_tab'), 15);

        add_action('admin_init', array($this, 'initialize_category_mapping_options'));

        parent::__construct($plugin_slug);
    }

    function add_options_tab($tabs)
    {
        $tabs = $tabs + array(
            'pricelist_category_mappings' => array(
                'name' => __('Category mappings', $this->plugin_slug),
                'callback' => array($this, 'pricelist_category_mappings_page_settings')
            ),
        );

        return $tabs;
    }

    function pricelist_category_mappings_page_settings()
    {
        echo '<div style="max-width: 750px">';

        settings_fields($this->plugin_slug . '_category_mappings');
        do_settings_sections($this->plugin_slug . '_category_mappings_section');

        $this->submit_button();

        echo '</div>';
    }

    /* ------------------------------------------------------------------------ *
     * Setting Registration
     * ------------------------------------------------------------------------ */

    /**
     * Initializes the theme's display options page by registering the Sections,
     * Fields, and Settings.
     *
     * This function is registered with the 'admin_init' hook.
     */
    function initialize_category_mapping_options()
    {
        
        register_setting(
                $this->plugin_slug . '_category_mappings', $this->plugin_slug . '_category_mappings', array($this, 'apply_category_mappings')
        );
        
        add_settings_section(
                $this->plugin_slug . '_category_mappings', __('Category mappings', $this->plugin_slug), array($this, 'category_mappings_section_callback'), $this->plugin_slug . '_category_mappings_section'
        );
        add_settings_field(
            'category_mapping', 
            __('', $this->plugin_slug), 
            array($this, 'category_mappings_settings_callback'), 
            $this->plugin_slug . '_category_mappings_section', 
            $this->plugin_slug . '_category_mappings', 
            array(
                'description' => __('These products will not show up in the pricefile.', $this->plugin_slug),
            )
        );
    }

    /* ------------------------------------------------------------------------ *
     * Section Callbacks
     * ------------------------------------------------------------------------ */

    function category_mappings_section_callback()
    {
        echo '<h3>' . __('Map your shop categories with the pricefile categories.', $this->plugin_slug) . '</h3>';
        echo '<p>' . __('This will automattically sync the categories. Everytime a category of a product is added or changed the coresponding category will be added to the pricefiles automatically.', $this->plugin_slug) . '</p>';
    }

    /* ------------------------------------------------------------------------ *
     * Field Callbacks
     * ------------------------------------------------------------------------ */

    function category_mappings_settings_callback()
    {
        echo '</td></tr><tr>';
        $option = get_option($this->plugin_slug . '_category_mappings', false);

        $pricelist_cats = WC_Pricefiles::get_instance()->get_category_list();

        $shop_categories = get_terms('product_cat');

        echo '<tr><th>' . __('Shop categories', $this->plugin_slug) . '</th>';
        echo '<th>' . __('Pricefile categories', $this->plugin_slug) . '</th></tr>';

        foreach ($shop_categories AS $shop_cat) {
            if ($option)
            {
                $current = $option[$shop_cat->term_id];
            } else
            {
                $current = 0;
            }
            echo '<tr style="border-bottom: 1px solid #EEE; max-width: 750px"><td><label>' . $shop_cat->name . ' (' . $shop_cat->count . '): </label></td>';

            echo '<td><select id="woocommerce_pricefiles_category_mappings_' . $shop_cat->slug . '" name="' . $this->plugin_slug . '_category_mappings[' . $shop_cat->term_id . ']" class="pricelist_cat_mapping"  data-placeholder="' . __('Select category', $this->plugin_slug) . '" >';

            echo '<option value=""></option>';
            foreach ($pricelist_cats as $plc_id => $plc_name) {
                echo '<option value="' . esc_attr($plc_id) . '" ' . selected($plc_id, $current, false) . '>' . $plc_name . '</option>';
            }

            echo '</select></td></tr>';
        }

        echo '<tr><td><label for="' . $this->plugin_slug . '_category_mappings_overwrite"><input type="checkbox" id="' . $this->plugin_slug . '_category_mappings_overwrite" name="' . $this->plugin_slug . '_category_mappings[overwrite]" value="1" /> ' . __('Overwrite existing pricefile categories', $this->plugin_slug) . '</label></td></tr>';
    }

    function apply_category_mappings($input)
    {

        $overwrite = $input['overwrite'];


        if ($overwrite == 1)
        {
            unset($input['overwrite']);


            foreach ($input AS $shop_cat_id => $pl_cat_id) {
                $args['tax_query'][0]['terms'] = $shop_cat_id;

                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'hide_empty' => false,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'id',
                            'terms' => $shop_cat_id
                        )
                    ),
                );

                //$products = new WP_Query($args);
                $products = get_posts($args);

                if (count($products) > 0)
                {
                    foreach ($products AS $product) {
                        update_post_meta($product->ID, '_pricelist_cat', $pl_cat_id);
                        //wp_set_post_terms( $product->ID, $pl_cat_id, '_pricelist_cat' );
                    }
                }
            }
        }

        return $input;
    }

}
