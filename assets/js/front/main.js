jQuery(document).ready( function($) {

    Beans.init({
        id : xcid_beans.id,
        login : xcid_beans.login_page,
        about : xcid_beans.about_page
    });

    if(xcid_beans.authentication){
        Beans.authenticate(xcid_beans.authentication);
    }

    jQuery(document).on('click', '.beans-cart-action-div input.btn[name="beans_join"]', function () {
        jQuery(this).val('Pending ...').attr('disabled', 'disabled');
        //Beans.connect("authorization");
        window.location = xcid_beans.login_page;
    });

    jQuery(document).on('click', '.beans-cart-action-div input.btn[name="beans_cancel"]', function () {
        jQuery(this).val('Pending ...').attr('disabled', 'disabled');

        var data = {
            action          : 'xcid_beans_cancel'
        };

        jQuery.post( woocommerce_params.ajax_url, data, function( response ) {
            window.location.reload();
        });
    });

    jQuery(document).on('click', '.beans-cart-action-div input.btn[name="beans_redeem"]', function () {
        jQuery(this).val('Pending ...').attr('disabled', 'disabled');

        var data = {
            action          : 'xcid_beans_redeem'
        };

        jQuery.post( woocommerce_params.ajax_url, data, function( response ) {
            window.location.reload();
        });
    });

});

jQuery(window).unload(function () { jQuery(window).unbind('unload'); });
jQuery(window).bind('pageshow', function(event) {
    if (jQuery('.beans-cart-action-div input.btn[name="beans_join"]').length > 0) {
        jQuery('.beans-cart-action-div input.btn').removeAttr('disabled');
    }
});

