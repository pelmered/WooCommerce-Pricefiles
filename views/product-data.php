<?php
global $woocommerce, $post;
?>
<div id="options_group_manufacturer" class="options_group">

    <h3><?php _e('Optional information for price files integration'); ?></h3>

    <?php woocommerce_wp_text_input(array(
        'id' => WC_PRICEFILES_PLUGIN_SLUG.'_ean_code', 
        'class' => '', 
        'label' => '<abbr title="' . __('European Article Number / International Article Number barcode number', 'woocommerce') . '">' . __('EAN code', 'woocommerce') . '</abbr>', 
        'desc_tip' => 'true', 
        'description' => __('EAN is the international standard for product barcodes. Type in the whole 8 or 13 digit number below the product barcode.', $this->plugin_slug)
    )); ?>

    <p id="<?php echo WC_PRICEFILES_PLUGIN_SLUG; ?>_ean_code_status"></p>
    
    <?php woocommerce_wp_text_input(array(
        'id' => WC_PRICEFILES_PLUGIN_SLUG.'_sku_manufacturer', 
        'class' => '', 
        'label' => '<abbr title="' . __('Stock Keeping Unit manufacturer', 'woocommerce') . '">' . __('Manufacturer SKU', $this->plugin_slug) . '</abbr>', 
        'desc_tip' => 'true', 
        'description' => __('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', $this->plugin_slug)
    )); ?>

    <?php //woocommerce_wp_text_input(array('id' => '_manufacturer', 'class' => '', 'label' => __('Manufacturer', $this->plugin_slug), 'desc_tip' => 'true', 'description' => __('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', $this->plugin_slug))); ?>

    <?php

    $manufacturer = $this->get_manufacturer_attribute_taxonomy();
    
    // Get name of taxonomy we're now outputting (pa_xxx)
    $attribute_taxonomy_name = wc_attribute_taxonomy_name( $manufacturer->attribute_name );

    // Make sure it exists
    if ( taxonomy_exists( $attribute_taxonomy_name ) ) 
    {
        $current = get_post_meta( $post->ID, WC_PRICEFILES_PLUGIN_SLUG.'_manufacturer', true );
    
        $manufacturer_field = array(
            'id'    => WC_PRICEFILES_PLUGIN_SLUG.'_manufacturer',
            'label' => __('Manufacturer'),
            'class' => 'chosen-select',
            //'wrapper_class' => '',
            'options' => array()
        );

        $all_terms = get_terms($attribute_taxonomy_name, 'orderby=name&hide_empty=0');
        if ($all_terms) 
        {
            $m = get_post_meta( $post->ID, WC_PRICEFILES_PLUGIN_SLUG.'_manufacturer', true );

            if(empty($m))
            {
                $manufacturer_field['options'][''] = __('Choose manufacturer', WC_PRICEFILES_PLUGIN_SLUG);
            }
            
            foreach ($all_terms as $term) 
            {
                //echo '<option value="' . esc_attr($term->slug) . '" ' . selected($term->slug, $current, false) . '>' . $term->name . '</option>';
                $manufacturer_field['options'][$term->slug] = $term->name;
            }

            woocommerce_wp_select($manufacturer_field);
        }
        else
        {
            _e(
                sprintf(
                    'You need to add manufacturers to use the manufaturer field.<br />You can add manufacturers <a href="%s">here</a>', 
                    admin_url('edit-tags.php?taxonomy=pa_manufacturer&post_type=product')
                ), 
                WC_PRICEFILES_PLUGIN_SLUG
            );
        }

    }
    
    
    $pricelist_cats = WC_Pricefiles::get_instance()->get_category_list();
    
    // Ensure it exists 
    if ( !(empty($pricelist_cats)) ) 
    {

        $current = get_post_meta( $post->ID, '_pricelist_cat', true );
        
        $category_field = array(
            'id'    => WC_PRICEFILES_PLUGIN_SLUG.'_pricelist_cat',
            'label' => __('Category'),
            'class' => 'chosen-select',
            //'wrapper_class' => '',
            'options' => array()
        );
        
        $c = get_post_meta( $post->ID, WC_PRICEFILES_PLUGIN_SLUG.'_pricelist_cat', true );
        
        if(empty($c))
        {
            $category_field['options'][''] = __('Choose category', WC_PRICEFILES_PLUGIN_SLUG);
        }

        foreach ($pricelist_cats as $id => $name) {
            $category_field['options'][esc_attr($id)] = esc_attr($name);
        }

        woocommerce_wp_select($category_field);
    }
   
    do_action( WC_PRICEFILES_PLUGIN_SLUG . '_product_options'); 
    
    ?>

</div>

