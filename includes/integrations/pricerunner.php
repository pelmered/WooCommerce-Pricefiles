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
    protected function print_header()
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
     * Implements WC_Pricefile_Generator->print_product()= and echoes a formatted product
     * 
     * @param     array  An opaque object used by property getters.
     * @since     0.1.12
     */
    protected function print_product( $product )
    {
        echo $this::format_value($product->get_categories());
        echo $this::format_value($product->get_sku());
        echo $this::format_value($product->get_price());
        echo $this::format_value($product->get_url());
        echo $this::format_value($product->get_title());
        echo $this::format_value($product->get_manufacturer_sku());
        echo $this::format_value($product->get_manufacturer());
        echo $this::format_value($product->get_ean());
        echo $this::format_value($product->get_description());
        echo $this::format_value($product->get_image_url());
        echo $this::format_value($product->get_stock_status());
        echo $this::format_value($product->get_stock_quantity());
        echo $this::format_value($product->get_delivery_time());
        echo $this::format_value($product->get_shipping_cost());
        echo "\n";
    }

    /**
     * Implements WC_Pricefile_Generator->finish()= and doing nothing (no wrapping up needed)
     * 
     * @since     0.1.12
     */
    protected function print_footer()
    {
    }

}

?>
