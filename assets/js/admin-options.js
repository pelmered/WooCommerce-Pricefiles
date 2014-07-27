// Administration-specific JavaScripts for WC-Pricefiles
(function ($) {
    "use strict";
    $(function () {

        $("#wc_pricefiles_reset_address").click(function(e) {
            if (confirm('Do you really want to reset the address to the defaults?')) {
            } else {
                e.preventDefault();
            }
        });

        jQuery("#woocommerce_pricefiles_exclude_ids").ajaxChosen({
            method: 	'GET',
            url: 		wc_pricelists_options.ajax_url,
            dataType: 	'json',
            afterTypeDelay: 100,
            data: {
                action: 	'woocommerce_json_search_products',
                security: 	wc_pricelists_options.search_products_nonce
            }
        }, function (data) {

            var terms = {};

            $.each(data, function (i, val) {
                terms[i] = val;
            });

            return terms;
        });

        $('#woocommerce-pricefiles_options_use_cache').change(function(){

            if( $(this).is(':checked') )
            {
                $('#woocommerce-pricefiles_cache_additional').slideDown();
            }
            else
            {
                $('#woocommerce-pricefiles_cache_additional').slideUp();
            }

        });


        $('#woocommerce-pricefiles_refresh_cache_button').click(function(e){

            e.preventDefault();

            $('#woocommerce-pricefiles_cache_refresh_status').html('loading...');

            var cache_refresh_xhr = $.get( 'http://debug.nu/?pricefile=all&refresh=1&output=json', function() {
                
                $('#woocommerce-pricefiles_cache_refresh_status').html('Done');
                
            });


        });
    });

        
}(jQuery));


