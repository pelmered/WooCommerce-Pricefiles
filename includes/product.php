<?php
/**
 * Unified interface to get product data
 *
 * @author peter
 */

class WC_Pricefiles_Product
{
    private $product;
    private $product_data = array();
    private $product_meta = array();
    
    
    private $options = array();
    
    public function __construct( $product_id )
    {
        $this->product = get_product( $product_id );
        
        $this->product_data = $this->product->get_post_data();

        $this->product_meta = get_post_meta( $product_id );
        
        $this->options = WC_Pricefiles()->get_options();
        //$this->options = get_option(WC_PRICEFILES_PLUGIN_SLUG . '_options', WC_Pricefiles_Admin_Options::default_pricelist_options());
    }
    
    /**
     * Should product be shown in product feed
     * 
     * @return boolean
     */
    function show()
    {
        if (!$this->product->is_purchasable() || $this->product->visibility == 'hidden')
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    
    
    
    // Getters
    
    
    /**
     * Get price with correct tax display option
	 *
     * @return  string  'incl' or 'excl'
     * @since   0.1.10
     */
    public function get_price($product)
    {
        if ($this->get_price_type() === 'excl')
        {
            return $product->get_price_excluding_tax(1);
        } else
        {
            return $product->get_price_including_tax(1);
        }
    }

    /**
     * Get price tax display option. I.e. whether we should out put prices including or excluding tax  
	 *
     * @return  string  'incl' or 'excl'
     * @since   0.1.10
     */
    public function get_price_type()
    {
        if (!empty($this->price_type))
        {
            return $this->price_type;
        }
        if ($this->options['output_prices'] == 'shop')
        {
            $wc_option = get_option('woocommerce_tax_display_cart');
            if(!empty($wc_option) )
            {
                $this->price_type = $wc_option;
                return $this->price_type;
            }
        } 
        if (!empty($this->options['output_prices']))
        {
            $this->price_type = $this->options['output_prices'];
            return $this->price_type;
        } else
        {
            $this->price_type = 'incl';
            return $this->price_type;
        }
    }

    /**
     * Get product categories formatted for pricefile.
	 *
     * @param   array   $product_meta Return value from get_post_meta()
     * @return  string  The manufacturer name or an empty string if it's missing.
     * @since   0.1.10
     */
    public function get_categories($product)
    {
        global $wc_pricefiles_globals;
        
        $product_id = $product->id;

        $cat = get_post_meta($product_id, WC_PRICEFILES_PLUGIN_SLUG . '_pricelist_cat', true);
        
        if ($cat && !empty($wc_pricefiles_globals['wc_pricefiles_categories'][$cat]))
        {
            return $wc_pricefiles_globals['wc_pricefiles_categories'][$cat];
        }

        $terms = get_the_terms($product_id, 'product_cat');
        
        if(is_wp_error( $terms ) || count($terms) == 0)
        {
            return '';
        }
        
        $cat = '';
        $parents = array();
        $t = array();
        $t2 = array();

        foreach ($terms AS $term)
        {
            if ($term->parent)
            {
                $parents[] = $term->parent;
            }

            $ancestors = get_ancestors($term->term_id, 'product_cat');

            $t_str = '';

            foreach ($ancestors AS $a)
            {
                $tt = get_term($a, 'product_cat');
                $t2[] = $tt->name;

                $t_str = $tt->name . ' > ' . $t_str;
            }
            $t[$term->term_id] = $t_str . $term->name;
        }

        foreach ($parents AS $p)
        {
            unset($t[$p]);
        }

        return implode(',', $t);
    }

    /**
     * This function is a hack to calculate the shipping cost for a single product. To do this we must first build a cart object and after that a package object that is needed to calculate the price. 
     * TODO: This need to be revisited. Has been improved, but not perfect
     * 
     * @global  object  $woocommerce
     * @param   object  $product Product object
     * @return  float   Lowest shipping price
     */
    public function get_shipping_cost($product)
    {
        global $woocommerce;

        // Packages array for storing package/cart object
        $packages = array();

        $price = $product->get_price_excluding_tax(1);
        $price_tax = $product->get_price_including_tax(1) - $price;

		// Build up a fake package object
        $cart = array(
            'product_id' => $product->id,
            'variation_id' => '',
            'variation' => '',
            'quantity' => 1,
            'data' => $product,
            'line_total' => $price,
            'line_tax' => $price_tax,
            'line_subtotal' => $price,
            'line_subtotal_tax' => $price_tax,
        );

		// Items in the package
        $packages[0]['contents'][md5('wc_pricefiles_' . $product->id . $price)] = $cart;  
		// Cost of items in the package, set below
        $packages[0]['contents_cost'] = $price;  
 		// Applied coupons - some, like free shipping, affect costs    
        $packages[0]['applied_coupons'] = ''; 
		// Fake destination address. Needed for calculation the shipping
        $packages[0]['destination'] = WC_Pricefiles()->get_shipping_destination();

		// Apply filters to mimic normal behaviour
        $packages = apply_filters('woocommerce_cart_shipping_packages', $packages);


        $package = $packages[0];

		// Calculate the shipping using our fake package object
        $shipping_method_rates = $woocommerce->shipping->calculate_shipping_for_package($package);

        $shipping_methods = WC_Pricefiles()->get_shipping_methods();

        $lowest_shipping_cost = 0;

        if (!empty($this->shipping_methods))
        {
            //$shipping_methods = array_intersect_key($shipping_method_rates['rates'], $this->shipping_methods);

            foreach ($shipping_method_rates['rates'] AS $rate)
            {
                if (in_array($rate->method_id, $this->shipping_methods))
                {
                    $total_tax = 0;
                    
                    if( $this->get_price_type() == 'incl' )
                    {
                        //Sum the taxes
                        foreach($rate->taxes AS $tax)
                        {
                            $total_tax += $tax;
                        }

                        //Calc shipping cost including tax
                        $total_cost = $rate->cost + $total_tax;
                    }
                    else
                    {
                        $total_cost = $rate->cost;
                    }

                    if (empty($lowest_shipping_cost) || $total_cost_inc_tax < $lowest_shipping_cost)
                    {
                        $lowest_shipping_cost = $total_cost_inc_tax;
                    }
                }
            }
        }

        return $lowest_shipping_cost;
    }

    /**
     * Extract the EAN code of a product.
     * 
     * @param array $product_meta Return value from get_post_meta()
     * @return string The EAN code or an empty string if it's missing.
     * @since    0.1.10
     */
    protected static function get_ean($product_meta)
    {
        if (isset($product_meta[WC_PRICEFILES_PLUGIN_SLUG . '_ean_code'][0]))
        {
            return $product_meta[WC_PRICEFILES_PLUGIN_SLUG . '_ean_code'][0];
        }
        else {
            return '';
        }
    }

    /**
     * Extract the manufacturer name of a product.
	 *
     * @param   array   $product_meta Return value from get_post_meta()
     * @return  string  The manufacturer name or an empty string if it's missing.
     * @since   0.1.10
     */
    protected static function get_manufacturer($product_meta)
    {
        if (isset($product_meta[WC_PRICEFILES_PLUGIN_SLUG . '_manufacturer'][0]))
	{
            $term = get_term_by('slug', $product_meta[WC_PRICEFILES_PLUGIN_SLUG . '_manufacturer'][0], 'pa_manufacturer');
            if ($term !== false) {
                return $term->name;
            }
            else {
                return '';
            }
        }
        else {
            return '';
        }
    }

    /**
     * Extract the manufacturer SKU of a product.
     * 
     * @param   array   $product_meta Return value from get_post_meta()
     * @return  string  The manufacturer SKU or an empty string if it's missing.
	 * @since    0.1.10
     */
    protected static function get_manufacturer_sku($product_meta)
    {
        if (isset($product_meta[WC_PRICEFILES_PLUGIN_SLUG . '_sku_manufacturer'][0]))
        {
            return $product_meta[WC_PRICEFILES_PLUGIN_SLUG . '_sku_manufacturer'][0];
        }
        else {
            return '';
        }
    }

    
}
