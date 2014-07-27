// Administration-specific JavaScripts for WooComemrce Pricefiles
(function ($) {
    "use strict";
    $(function () {

        //$('#options_group_manufacturer').on('change', '#wc_pricefiles_ean_code', function() {
        $('#options_group_manufacturer').on('input', '#woocommerce-pricefiles_ean_code', function() {

            var $this = $(this);
            var val = $this.val();
            var len = val.length;

            if( len === 8 || len === 13 )
            {
                $(document).trigger('EAN_check', [ $this, val ]);
            }
        });

        $('#options_group_manufacturer').on('blur', '#woocommerce-pricefiles_ean_code', function() {

            var $this = $(this);
            var val = $this.val();

            $(document).trigger('EAN_check', [ $this, val ]);

        });

        var ean_check_xhr = null;

        $(document).on('EAN_check', function( e, $this, ean ) {

            $this.css('background-position-x', ($this.width() - 15)+'px' );
            $this.css('background-image', 'url(' + wc_pricelists_options.woocommerce_url + '/assets/images/ajax-loader.gif)');

            var data = {
                action: 'wc_pricefiles_check_ean_code',
                code: ean
            };

            //if (ean_check_xhr) ean_check_xhr.abort();

            ean_check_xhr = $.ajax({
                type: 'POST',
                url: wc_pricelists_options.ajax_url,
                data: data,
                //dataType: 'json'
            }).done(function( response ) {

                if( response.status === 'valid' )
                {
                    $('#_ean_code').addClass('valid').removeClass('invalid');
                    $('#_ean_code_status').text('').slideUp();
                }
                else if( response.status === 'corrected' )
                {
                    $('#_ean_code').removeClass('valid invalid');

                    alert('corrected '.response.new_code)
                }
                else if( response.status === 'invalid' )
                {
                    $('#_ean_code').addClass('invalid').removeClass('valid');
                    $('#_ean_code_status').text(response.msg).slideDown();
                }


                $this.css('background-image', '');
                /*
                console.log('response');
                console.log(response);
                */

            });
        });
        
        $('#woocommerce-pricefiles_manufacturer, #woocommerce-pricefiles_pricelist_cat').each( function() {
            $(this).chosen();
        });

    });
}(jQuery));

