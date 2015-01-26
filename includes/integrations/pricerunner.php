<?php

/**
 * Abstract Product Class
 *
 * The WooCommerce product class handles individual product data.
 *
 * @class 		WC_Pricefile_Pricerunner
 * @version		0.1.0
 * @author 		Peter Elmered
 */
class WC_Pricefile_Pricerunner extends WC_Pricefile_Generator
{
    const VALUE_SEPARATOR = ',';
    const VALUE_ENCLOSER_BEFORE = '"';
    const VALUE_ENCLOSER_AFTER = '"';
    
    /**
     * Implements WC_Pricefile_Generator->start()= and generates header for CSV-file
     * 
     * @since    0.1.12
     */
    protected function start()
    {
        $columns = array(
            'Category','SKU','Price','Product URL','Product Name','Manufacturer SKU','Manufacturer','EAN','Description','Graphic URL','In Stock','Stock Level','Delivery Time','Shippingcost'
        );
        
        $header = '';
        foreach($columns AS $c)
        {
            $header .= self::VALUE_ENCLOSER_BEFORE . $c . self::VALUE_ENCLOSER_AFTER . self::VALUE_SEPARATOR;
        }
        
        echo $header."\n";
        //return trim($header, self::VALUE_SEPARATOR)."\n";
    }

    /**
     * Implements WC_Pricefile_Generator->product()= and echoes a formatted product
     * 
     * @param     array  An opaque object used by property getters.
     * @since     0.1.12
     */
    protected function product($product_obj)
    {
        echo $this::format_value($this->get_categories($product_obj));
        echo $this::format_value($this->get_product_sku($product_obj));
        echo $this::format_value($this->get_price($product_obj));
        echo $this::format_value($this->get_product_url($product_obj));
        echo $this::format_value($this->get_product_title($product_obj));
        echo $this::format_value($this->get_manufacturer_sku($product_obj));
        echo $this::format_value($this->get_manufacturer($product_obj));
        echo $this::format_value($this->get_ean($product_obj));
        echo $this::format_value(strip_tags($this->get_description($product_obj)));
        echo $this::format_value($this->get_image_url($product_obj));
        echo $this::format_value($this->get_stock_status($product_obj));
        echo $this::format_value($this->get_stock_quantity($product_obj));
        echo $this::format_value($this->get_delivery_time($product_obj));
        echo $this::format_value($this->get_shipping_cost($product_obj));
        echo "\n";
    }

    /**
     * Implements WC_Pricefile_Generator->finish()= and doing nothing (no wrapping up needed)
     * 
     * @since     0.1.12
     */
    protected function finish()
    {
    }

}

?>
