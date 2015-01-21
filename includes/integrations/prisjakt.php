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
     * Generates header for CSV-file
     * 
     * @since    0.1.10
     */
    function get_header()
    {
        $columns = array(
            'Produktnamn','Art.nr.','EAN','Tillverkare','Tillverkar-SKU','Kategori','Pris inkl.moms','Frakt','Produkt-URL','Bild-URL','Lagerstatus'
        );
        
        $header = '';
        foreach($columns AS $c)
        {
            $header .= self::VALUE_ENCLOSER_BEFORE . $c . self::VALUE_ENCLOSER_AFTER . self::VALUE_SEPARATOR;
        }
        
        return trim($header, self::VALUE_SEPARATOR)."\n";
    }

    /**
     * Implements WC_Pricefile_Generator->generate_pricefile)= and  genereates the pricefile
     * 
     * @since    0.1.0
     */
    public function generate_pricefile()
    {
        if($this->read_cache())
        {
            die();    
        }
         
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'nopaging' => TRUE
        );

        $loop = new WP_Query($args);

        if ($loop->have_posts())
        {
            //Output headers
            echo  $this->get_header();

            //Get list of excluded products
            if (empty($this->options['exclude_ids']))
            {
                $excluded = array();
            } else
            {
                $excluded = $this->options['exclude_ids'];
            }

            while ($loop->have_posts())
            {
                $loop->the_post();

                $product_id = get_the_id();

                if (in_array($product_id, $excluded))
                {
                    continue;
                }

                $product = get_product($product_id);

                if (!$product->is_purchasable() || $product->visibility == 'hidden')
                {
                    continue;
                }

                $product_data = $product->get_post_data();


                $product_meta = get_post_meta($product_id);

                //Product title
                echo $this::format_value($product_data->post_title);

                //Product SKU
                echo $this::format_value($product->get_sku());

                //EAN code
                echo $this::format_value($this::get_ean($product_meta));

                //Manufacturer name
                echo $this::format_value($this::get_manufacturer($product_meta));

                //Manufacturer SKU/Product id
                echo $this::format_value($this::get_manufacturer_sku($product_meta));

                //Category
                echo $this::format_value($this->get_categories($product));

                //Price
                echo $this::format_value($product->get_price_including_tax(1));

                //Shipping cost
                if ($product->needs_shipping())
                {
                    echo $this::format_value($this->get_shipping_cost($product));
                    //echo $this::format_value( $product->get_price_including_tax(1) );
                } else
                {
                    echo $this::format_value('');
                }

                //Product URL
                echo $this::format_value(get_permalink($product_id));

                //Image URL
                if (has_post_thumbnail($product_id))
                {
                    echo $this::format_value(wp_get_attachment_url(get_post_thumbnail_id($product_id)));
                } else
                {
                    echo $this::format_value('');
                }

                //Stock status
                if ($product->is_in_stock())
                {
                    echo $this::format_value('Ja');
                } else if ($product->is_on_backorder())
                {
                    echo $this::format_value('Nej');
                } else
                {
                    echo $this::format_value('Nej');
                }

                echo "\n";
            }

            return $this->save_cache();
        } else
        {
            if($this->is_debug())
            {
                echo 'No products found';
                return false;
            }
        }

        //wp_reset_postdata();
    }

}

?>
