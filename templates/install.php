<?php
/**
 * Notice Install
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$website = urlencode(get_site_url());
$redirect = urlencode(admin_url());
$email = urlencode(get_option('admin_email', true));
$currency = urlencode(get_woocommerce_currency());
$blog_name = urlencode(get_option('blogname', true));
$country = urlencode(get_option('woocommerce_default_country', true));

// https://business.trybeans.com/app/xcid-woocommerce/authorize/?redirect=http%3A%2F%2Fsocial.dev%2Fw
?>
<div id="message" class="updated woocommerce-message wc-connect">
    <p><?php _e( '<strong>Welcome to XciD Beans Integration</strong> &#8211; You&lsquo;re almost ready to start :)', 'xcid_beans' ); ?></p>
    <p class="submit">
        <?php $url = "https://business.trybeans.com/app/xcid-woocommerce/authorize/?redirect=$redirect&email=$email&website=$website&currency=$currency&company_name=$blog_name&country=$country" ?>
        <a href="<?php echo $url ?>" class="button-primary">
            <?php _e( 'Authorize the app', 'xcid_beans' ); ?></a>
    </p>
</div>
