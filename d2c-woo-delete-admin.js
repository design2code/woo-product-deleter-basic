jQuery(document).ready(function(){

    /* Delete All Product in listing */
    jQuery('.d2c_delete_products').on('click', function(e){
        e.preventDefault();
        
        if(confirm('Are you sure, this is not reversable?') == true){
            //Scroll to top
            jQuery(window).scrollTop(0);
            //Loading message
            jQuery('#d2c_response').html('<div class="updated"><p>Processing deletion request..</p></div>').show();
            //Post data 
            jQuery.post(ajaxurl, {
                action: 'd2c_delete_products',
                wp_nonce: jQuery(this).data('d2c_delete_products_nonce'),
                count: jQuery(this).data('count'),
                status: jQuery(this).data('status'),
                category: jQuery(this).data('cat')
                }, function(response) {
                    //Display Output
                    jQuery('#d2c_response').html(response).show();

                    //hide message after 2 secs
                    // setTimeout(function(){
                    //     jQuery('#d2c_response').hide()
                    // }, 3000);
                }
            );
        }
    });

    /* Delete single Product in listing */
    jQuery('.d2c_delete_single_product').on('click', function(e){
        e.preventDefault();
        if(confirm('Are you sure, this is not reversable?') == true){
            //Scroll to top
            jQuery(window).scrollTop(0);
            //Loading message
            jQuery('#d2c_response').html('<div class="updated"><p>Processing deletion request..</p></div>').show();
            //Post data 
            jQuery.post(ajaxurl, {
                action: 'd2c_delete_product',
                wp_nonce: jQuery(this).data('d2c_delete_product_nonce'),
                pid: jQuery(this).data('product_id')
                }, function(response) {
                    //Display Output
                    jQuery('#d2c_response').html(response).show();
                }
            );
        }
    });


    /* Check Product Count */
    jQuery('#check_count').on('click', function(e){
        e.preventDefault();
        //Scroll to top
        jQuery(window).scrollTop(0);
        //Loading message
        jQuery('#d2c_response').html('<div class="updated"><p>Processing request..</p></div>').show();
        
        //Post data 
        jQuery.post(ajaxurl, {
            action: 'd2c_check_count',
            status: jQuery("#product_status").val(),
            category: jQuery("#product_category").val()
            }, function(response) {
                //Display Output
                jQuery('#d2c_response').html(response).show();
            }
        );
    });

});