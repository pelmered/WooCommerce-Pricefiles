<?php

/**
 * Base file or specific pricefile genereators
 *
 * @author peter
 */
abstract class WC_Pricefile_Generator
{

    /**
     * Base file or specific pricefile genereators
     *
     * @since    0.1.0
     *
     * @var      object
     */
    private static $_instances = array();
    
    protected $options = array();
    protected $shipping_methods = array();
    protected $shipping_destination = array();
    protected $plugin_slug = array();
    public $price_type = null;
    public $pricefile_slug = null;

    //Default CSV separators
    const VALUE_SEPARATOR = ';';
    const VALUE_ENCLOSER_BEFORE = '"';
    const VALUE_ENCLOSER_AFTER = '"';

    public function __construct($pricefile_slug)
    {
        global $wc_pricefiles_globals;

        if(!@set_time_limit(0)) {
            //TODO: Debug log: Could not set time limit
        }
        
        $this->pricefile_slug = $pricefile_slug;
        
        require_once( WP_PRICEFILES_PLUGIN_PATH . 'includes/admin.php' );
        require_once( WP_PRICEFILES_PLUGIN_PATH . 'includes/admin/options.php' );

        $this->options = get_option(WC_PRICEFILES_PLUGIN_SLUG . '_options', WC_Pricefiles_Admin_Options::default_pricelist_options());
        
        //var_dump($this->options);
        
        //add_action('init', array($this, 'generate_pricefile'));
        $shipping_destination_values = $this->options['shipping_destination'];

        if (!is_array($shipping_destination_values))
        {
            $shipping_destination_values = $wc_pricefiles_globals['default_shipping_destination'];
        }

        foreach ($shipping_destination_values AS $key => $value)
        {
            $this->shipping_destination[str_replace('shipping_', '', $key)] = $value;
        }

        $shipping_methods = $this->options['shipping_methods'];

        if (is_array($shipping_methods) && count($shipping_methods))
        {
            foreach ($shipping_methods AS $sm)
            {
                $s[$sm] = $sm;
            }
        } else
        {
            $s = array();
        }

        $this->shipping_methods = $s;
    }
    
    
    final public static function get_instance($slug)
    {
        $calledClass = get_called_class();

        if (!isset(self::$_instances[$calledClass]))
        {
            self::$_instances[$calledClass] = new $calledClass($slug);
        }

        return self::$_instances[$calledClass];
    }
    
    final public static function get_instances()
    {
        return self::$_instances;
    }

    //protected static abstract function get_instance();

    
    protected abstract function generate_pricefile();

    /**
     * Check if cache is activated
     * 
     * @return  bool   
     */
    protected function use_cache()
    {
        if (empty($this->options['use_cache']) || (!empty($this->options['use_cache']) && $this->options['use_cache'] != 1) )
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Check if cache can be written
     * 
     * @return  bool   
     */
    protected function can_read_cache()
    {
        if ( !$this->use_cache() )
        {
            return false;
        }
        
        if (!empty($_GET['refresh']) && $_GET['refresh'] == 1)
        {            
            return false;
        }

        if ( ( $time = get_transient(WC_PRICEFILES_PLUGIN_SLUG . '_file_cache_time_' . $this->pricefile_slug) ) === false )
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Read pricefile form cache
     * 
     * @return  boolstring  Status   
     */
    function read_cache()
    {
        if($this->can_read_cache())
        {
            $cache_path = WP_CONTENT_DIR . '/cache/' . WC_PRICEFILES_PLUGIN_SLUG . '/' . $this->pricefile_slug . '.txt';

            if (file_exists($cache_path))
            {
                //echo 'serverd from cache:';
                echo file_get_contents($cache_path);

                //return 'cache_read';
                return true;
            }
        }
        
        if ( $this->use_cache() )
        {
            ob_start();
        }
        
        return false;
    }

    /**
     * Save generated pricefile to cache
     * 
     * @return  string  Status   
     */
    protected function save_cache()
    {
        if ( !$this->use_cache() )
        {
            return 'no_cache';
        }
        
        $data = ob_get_clean();
        
        $cache_path = WP_CONTENT_DIR . '/cache/' . WC_PRICEFILES_PLUGIN_SLUG . '/' . $this->pricefile_slug . '.txt';

        if (!is_writable($cache_path))
        {
            if (!file_exists(dirname($cache_path)))
            {
                if (!mkdir(dirname($cache_path), 0777, true))
                {
                    if($this->is_debug())
                    {
                        echo 'Could not create cache path (' . $cache_path . '). Not writable by PHP';
                    }
                }
            } else
            {
                if($this->is_debug())
                {
                    echo 'Cache path (' . $cache_path . ') is not writable by PHP';
                }
            }
            $return = 'not_writable';
        } else
        {
            $return = file_put_contents($cache_path, $data);
            if ($return && $return > 0)
            {
                set_transient(WC_PRICEFILES_PLUGIN_SLUG . '_file_cache_time_' . $this->pricefile_slug, time(), 12 * HOUR_IN_SECONDS);
                $return = 'cache_written';
            }
            else
            {
                $return = 'cache_not_writable';
                if($this->is_debug())
                {
                    echo 'Cache not written';
                }
            }
        }
        
        if(empty($_GET['output']) || $_GET['output'] != 'json')
        {
            echo $data;
            // Stop execution completely to prevent garbage data (unlike wp_die()). 
            die(); 
        }
        else
        {
            return $return;
        }
    }
    
    /**
     * Is debug more on?
     * 
     * @return  bool   
     */
    function is_debug()
    {
        if(!empty($this->options['use_debug']) && $this->options['use_debug'] == 1)
        {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }

    
    /**
     * Formats the value for output in pricefile and adds the required field separators.
     * 
     * @param   string/numeric  Value to be formatted
     * @return  string   Formatted value
     */
    public static function format_value($value)
    {
		if (empty($value) && $value !== 0 && $value !== 0.0 )
        {
            $value = '';
        }
        
        $c = get_called_class();

        return $c::VALUE_ENCLOSER_BEFORE . addcslashes($value, '"\\') . $c::VALUE_ENCLOSER_AFTER . $c::VALUE_SEPARATOR;
    }

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

        $destination = $this->shipping_destination;

        $destination['state'] = '';
        $destination['address_2'] = '';

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
        $packages[0]['destination'] = $destination;

		// Apply filters to mimic normal behaviour
        $packages = apply_filters('woocommerce_cart_shipping_packages', $packages);


        $package = $packages[0];

		// Calculate the shipping using our fake package object
        $shipping_methods = $woocommerce->shipping->calculate_shipping_for_package($package);

        $lowest_shipping_cost = 0;

        if (!empty($this->shipping_methods))
        {
            //$shipping_methods = array_intersect_key($shipping_methods['rates'], $this->shipping_methods);

            foreach ($shipping_methods['rates'] AS $rate)
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

                    if (empty($lowest_shipping_cost) || $total_cost < $lowest_shipping_cost)
                    {
                        $lowest_shipping_cost = $total_cost;
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

/*
 * This function exist only in PHP >= 5.3.
 * For for previous versions, we need to emulate this function.
 * Ugly hack, but it works.
 */
if (!function_exists('get_called_class'))
{
    function get_called_class()
    {
        $bt = debug_backtrace();
        $l = 0;
        do
        {
            $l++;
            $lines = file($bt[$l]['file']);
            $callerLine = $lines[$bt[$l]['line']-1];
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/', $callerLine, $matches);
        } while ($matches[1] === 'parent' && $matches[1]);

        return $matches[1];
    }
}

?>
