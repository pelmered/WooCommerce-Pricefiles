<?php
global $woocommerce, $post;
?>
<div id="options_group_manufacturer" class="options_group">

    <h3><?php _e('Optional information for price files integration'); ?></h3>

    <?php woocommerce_wp_text_input(array(
        'id' => 'wc_pricefiles_ean_code', 
        'class' => '', 
        'label' => '<abbr title="' . __('European Article Number / International Article Number barcode number', 'woocommerce') . '">' . __('EAN code', 'woocommerce') . '</abbr>', 
        'desc_tip' => 'true', 
        'description' => __('EAN is the international standard for product barcodes. Type in the whole 8 or 13 digit number below the product barcode.', $this->plugin_slug)
    )); ?>

    <p id="_ean_code_status"></p>
    
    <?php woocommerce_wp_text_input(array(
        'id' => 'wc_pricefiles_sku_manufacturer', 
        'class' => '', 
        'label' => '<abbr title="' . __('Stock Keeping Unit manufacturer', 'woocommerce') . '">' . __('Manufacturer SKU', $this->plugin_slug) . '</abbr>', 
        'desc_tip' => 'true', 
        'description' => __('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', $this->plugin_slug)
    )); ?>

    <?php //woocommerce_wp_text_input(array('id' => '_manufacturer', 'class' => '', 'label' => __('Manufacturer', $this->plugin_slug), 'desc_tip' => 'true', 'description' => __('SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', $this->plugin_slug))); ?>

    <?php

    $manufacturer = $this->get_manufacturer_attribute_taxonomy();
    
    // Get name of taxonomy we're now outputting (pa_xxx)
    $attribute_taxonomy_name = $woocommerce->attribute_taxonomy_name( $manufacturer->attribute_name );

    // Make sure it exists
    if ( taxonomy_exists( $attribute_taxonomy_name ) ) :
        $current = get_post_meta( $post->ID, '_manufacturer', true );
    ?>
    <p class="form-field _manufacturer_field ">
        <label for="_manufacturer"><?php _e('Manufacturer'); ?></label>
        <select class="chosen-select" name="_manufacturer" id="wc_pricefiles_manufacturer" data-placeholder="<?php _e('Select manufacturer', $this->plugin_slug); ?>" >
            <option value=""></option>
            <?php
            $all_terms = get_terms($attribute_taxonomy_name, 'orderby=name&hide_empty=0');
            if ($all_terms) {
                foreach ($all_terms as $term) {
                    echo '<option value="' . esc_attr($term->slug) . '" ' . selected($term->slug, $current, false) . '>' . $term->name . '</option>';
                }
            }
            ?>
        </select>
        <?php /*
        <img class="help_tip" src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16">
        */ ?>
        <a style="display: block; clear: both; /*margin-left: 150px*/" href="<?php echo admin_url( 'edit-tags.php?taxonomy=pa_manufacturer&post_type=product'); ?>"><?php _e('Add new Manufacturers'); ?></a>        
        
    </p>
<?php endif; ?>
<?php 
    $manufacturer = $this->get_manufacturer_attribute_taxonomy();

    // Get name of taxonomy we're now outputting (pa_xxx)
    $attribute_taxonomy_name = $woocommerce->attribute_taxonomy_name( $manufacturer->attribute_name );

    global $wc_pricefiles_globals;
    //$pricelist_cats = $wc_pricefiles_globals['wc_pricefiles_categories'];
    
    $pricelist_cats = WC_Pricefiles::get_instance()->get_category_list();
    
    // Ensure it exists 
    if ( taxonomy_exists( $attribute_taxonomy_name ) ) :        

        $current = get_post_meta( $post->ID, '_pricelist_cat', true );
?>
    <p class="form-field _pricelist_cat_field ">
        <label for="_manufacturer"><?php _e('Category'); ?></label>
        <select class="chosen-select" name="_pricelist_cat" id="wc_pricefiles_pricelist_cat" data-placeholder="<?php _e('Select category', $this->plugin_slug); ?>" >
            <option value=""></option>
            <?php
            $all_terms = get_terms($attribute_taxonomy_name, 'orderby=name&hide_empty=0');
            if ($all_terms) {
                foreach ($pricelist_cats as $id => $name) {
                    echo '<option value="' . esc_attr($id) . '" ' . selected($id, $current, false) . '>' . $name . '</option>';
                }
            }
            ?>
        </select>
       
   </p>

    <?php endif; ?>

</div>
