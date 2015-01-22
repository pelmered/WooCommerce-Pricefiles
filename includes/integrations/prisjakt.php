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
     * @since    0.1.12
     */
    protected function product($product_info)
    {
        echo $this::format_value($product_info['product_title']);
        echo $this::format_value($product_info['product_sku']);
        echo $this::format_value($product_info['ean_code']);
        echo $this::format_value($product_info['manufacturer_name']);
        echo $this::format_value($product_info['manufacturer_sku']);
        echo $this::format_value($product_info['category']);
        echo $this::format_value($product_info['price']);
        echo $this::format_value($product_info['shipping_cost']);
        echo $this::format_value($product_info['product_url']);
        echo $this::format_value($product_info['image_url']);
        echo $this::format_value($product_info['stock_status']);
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
