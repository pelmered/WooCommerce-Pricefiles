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
    protected function print_header()
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
     * Implements WC_Pricefile_Generator->print_product()= and echoes a formatted product
     * 
     * @param     array  An opaque object used by property getters.
     * @since    0.1.12
     */
    protected function print_product($product_id)
    {
        $product = new WC_Pricefiles_Product($product_id);
        
        echo $this::format_value($product->get_product_title());
        echo $this::format_value($product->get_product_sku());
        echo $this::format_value($product->get_ean());
        echo $this::format_value($product->get_manufacturer());
        echo $this::format_value($product->get_manufacturer_sku());
        echo $this::format_value($product->get_categories());
        echo $this::format_value($product->get_price());
        echo $this::format_value($product->get_shipping_cost());
        echo $this::format_value($product->get_product_url());
        echo $this::format_value($product->get_image_url());
        echo $this::format_value($product->get_stock_status());
        echo "\n";
    }

    /**
     * Implements WC_Pricefile_Generator->finish()= and doing nothing (no wrapping up needed)
     * 
     * @since    0.1.12
     */
    protected function print_footer()
    {
    }

}

?>
