<?php

/**
 * Abstract Product Class
 *
 * The WooCommerce product class handles individual product data.
 *
 * @class 		WC_Pricefile_Prisjakt
 * @version		0.1.0
 * @author 		Peter Elmered
 */
class WC_Pricefile_Prisjakt extends WC_Pricefile_Generator
{

    /**
     * Implements WC_Pricefile_Generator->start()= and generates header for CSV-file
     * 
     * @since    0.1.12
     */
    protected function start()
    {
        $columns = array(
            'Produktnamn','Art.nr.','EAN','Tillverkare','Tillverkar-SKU','Kategori','Pris inkl.moms','Frakt','Produkt-URL','Bild-URL','Lagerstatus'
        );
        
        $header = '';
        foreach($columns AS $c)
        {
            $header .= self::VALUE_ENCLOSER_BEFORE . $c . self::VALUE_ENCLOSER_AFTER . self::VALUE_SEPARATOR;
        }
        
        echo trim($header, self::VALUE_SEPARATOR)."\n";
    }

    /**
     * Implements WC_Pricefile_Generator->product()= and echoes a formatted product
     * 
     * @param     array  An opaque object used by property getters.
     * @since    0.1.12
     */
    protected function product($product_obj)
    {
        echo $this::format_value($this->get_product_title($product_obj));
        echo $this::format_value($this->get_product_sku($product_obj));
        echo $this::format_value($this->get_ean($product_obj));
        echo $this::format_value($this->get_manufacturer($product_obj));
        echo $this::format_value($this->get_manufacturer_sku($product_obj));
        echo $this::format_value($this->get_categories($product_obj));
        echo $this::format_value($this->get_price($product_obj));
        echo $this::format_value($this->get_shipping_cost($product_obj));
        echo $this::format_value($this->get_product_url($product_obj));
        echo $this::format_value($this->get_image_url($product_obj));
        echo $this::format_value($this->get_stock_status($product_obj));
        echo "\n";
    }

    /**
     * Implements WC_Pricefile_Generator->finish()= and doing nothing (no wrapping up needed)
     * 
     * @since    0.1.12
     */
    protected function finish()
    {
    }

}

?>
