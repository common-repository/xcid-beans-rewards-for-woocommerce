jQuery( window ).load( function() {

    jQuery( 'select#xcid_social_reward_coupon_mode' ).change( function() {
        if ( jQuery( this ).val() === 'simple' ) {
            jQuery( this ).parent().parent().next( 'tr' ).show();
        } else {
            jQuery( this ).parent().parent().next( 'tr' ).hide();
        }
    }).change();


});