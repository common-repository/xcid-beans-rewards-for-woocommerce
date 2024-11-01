<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'XC_Settings_Beans' ) ) :

    /**
     * WC_Settings_Shipping
     */
    class XC_Settings_Beans extends WC_Settings_Page {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id = 'beans';
            $this->label = __( 'Beans', 'xcid_beans' );

            parent::__construct();
        }

        /**
         * Return XC_Bean
         * @return XC_Bean
         */
        public function get_xc_bean(){
            return WC()->beans;
        }

        /**
         * Get sections
         *
         * @return array
         */
        public function get_sections() {
            $sections = array( '' => __( 'General Options', 'xcid_beans' ) );
            return $sections;
        }
        
        /**
         * Get settings array
         *
         * @return array
         */
        public function get_activate_option() {
            $settings = array(
                array(
                    'title' => __( 'Beans', 'xcid_beans' ),
                    'type'  => 'title',
                    'id'    => 'xcid_beans_title' ),
                array(
                    'title'   => __( 'Enable', 'xcid_beans' ),
                    'desc'    => __( 'Enable this plugin', 'xcid_beans' ),
                    'id'      => 'xcid_beans_enabled',
                    'default' => 'no',
                    'type'    => 'checkbox', ),
                array(
                    'type' => 'sectionend',
                    'id'   => 'xcid_beans_title_end' ), );

            return $settings;
        }

        /**
         * Get Activate Array
         *
         * @return array
         */
        public function get_settings() {
            $settings = array(
                array(
                    'title' => __( 'Configuration', 'xcid_beans' ),
                    'type'  => 'title',
                    'id'    => 'xcid_beans_configuration' ),
                array(
                    'title'   => __( 'Api Key', 'xcid_beans' ),
                    'desc'    => __( 'Your beans API Key', 'xcid_beans' ),
                    'id'      => 'xcid_beans_api_key',
                    'default' => '',
                    'desc_tip' => true,
                    'type'    => 'text', ),
                array(
                    'type' => 'sectionend',
                    'id'   => 'xcid_beans_configuration_end' ),

                array(
                    'title' => __( 'Cart setup', 'xcid_beans' ),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'cart_setup' ),

                array(
                    'title'   => __( 'Enable', 'xcid_beans' ),
                    'desc'    => __( 'Enable on cart page', 'xcid_beans' ),
                    'id'      => 'xcid_beans_cart_enabled',
                    'default' => 'yes',
                    'type'    => 'checkbox', ),

                array(
                    'title'   => __( 'Position', 'xcid_beans' ),
                    'desc'    => __( 'Choose the position of the box', 'xcid_beans' ),
                    'id'      => 'xcid_beans_cart_hook',
                    'class'   => 'wc-enhanced-select',
                    'default' => 'woocommerce_cart_collaterals',
                    'type'    => 'select',
                    'options' => array(
                        'woocommerce_cart_collaterals' => __( 'Cart collaterals', 'xcid_beans' ),
                        'woocommerce_before_cart_table' => __( 'Before cart table', 'xcid_beans' ),
                        'woocommerce_before_cart_contents' => __( 'Before cart contents', 'xcid_beans' ),
                        'woocommerce_after_cart_contents' => __( 'After cart contents', 'xcid_beans' ),
                        'woocommerce_after_cart' => __( 'After cart', 'xcid_beans' ),
                    ), ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'beans_cart_end' ),

                array(
                    'title' => __( 'Product setup', 'xcid_beans' ),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'product_setup' ),

                array(
                    'title'   => __( 'Enable', 'xcid_beans' ),
                    'desc'    => __( 'Enable on product page', 'xcid_beans' ),
                    'id'      => 'xcid_beans_product_enabled',
                    'default' => 'yes',
                    'type'    => 'checkbox', ),

                array(
                    'title'   => __( 'Position', 'xcid_beans' ),
                    'desc'    => __( 'Choose the position of the box', 'xcid_beans' ),
                    'id'      => 'xcid_beans_product_hook',
                    'class'   => 'wc-enhanced-select',
                    'default' => 'woocommerce_after_single_product_summary',
                    'type'    => 'select',
                    'options' => array(
                        'woocommerce_after_add_to_cart_form' => __( 'After add to cart button', 'xcid_beans' ),
                        'woocommerce_after_single_product_summary' => __( 'After summary', 'xcid_beans' ),
                        'woocommerce_before_single_product_summary' => __( 'Before summary', 'xcid_beans' ),
                        'woocommerce_product_meta_end' => __( 'After meta', 'xcid_beans' ),
                        'woocommerce_product_meta_start' => __( 'Before meta', 'xcid_beans' ),
                        'woocommerce_product_thumbnails' => __( 'Product Thumbnails', 'xcid_beans' ),
                        'woocommerce_share' => __( 'Share position', 'xcid_beans' ),
                    ), ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'beans_product_end' ),

                array(
                    'title' => __( 'Checkout setup', 'xcid_beans' ),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'checkout_setup' ),

                array(
                    'title'   => __( 'Enable', 'xcid_beans' ),
                    'desc'    => __( 'Enable on checkout page', 'xcid_beans' ),
                    'id'      => 'xcid_beans_checkout_enabled',
                    'default' => 'yes',
                    'type'    => 'checkbox', ),

                array(
                    'title'   => __( 'Position', 'xcid_beans' ),
                    'desc'    => __( 'Choose the position of the box', 'xcid_beans' ),
                    'id'      => 'xcid_beans_checkout_hook',
                    'class'   => 'wc-enhanced-select',
                    'default' => 'woocommerce_before_checkout_form',
                    'type'    => 'select',
                    'options' => array(
                        'woocommerce_before_checkout_form' => __( 'Before form', 'xcid_beans' ),
                        'woocommerce_checkout_after_customer_details' => __( 'After customer details', 'xcid_beans' ),
                        'woocommerce_after_checkout_form' => __( 'After form', 'xcid_beans' )
                    ), ),

                array(
                    'type' => 'sectionend',
                    'id'   => 'beans_checkout_end' ),
            );

            return $settings;
        }

        /**
         * Output the settings
         */
        public function output() {
            global $current_section;
            if ( $current_section ) {

            } else {
                $activate = get_option('xcid_beans_enabled' , "no");
                $api = get_option('xcid_beans_api_key' , '');

                if($activate === "yes" ){
                    $settings = array_merge($this->get_activate_option(), $this->get_settings());

                    if(!empty($api)){
                        $this->display_api_info();
                    }

                }else{
                    $settings = $this->get_activate_option();
                }


                WC_Admin_Settings::output_fields( $settings );
            }
        }

        /**
         * Save settings
         */
        public function save() {
            global $current_section;

            if ( !$current_section ) {
                $activate = get_option('xcid_beans_enabled' , false);

                if($activate){
                    $settings = array_merge($this->get_activate_option(), $this->get_settings());
                }else{
                    $settings = $this->get_activate_option();
                }

                WC_Admin_Settings::save_fields( $settings );
            }
        }

        private function display_api_info() {
            $this->get_xc_bean()->re_init_bean();

            if(!is_null($this->get_xc_bean()->last_api_error)){
                $ex = $this->get_xc_bean()->last_api_error;
                printf(__('<div id="message2" class="error"><p>Your api key is invalid, message : %s</p></div>', 'xcid_beans'), $ex->getMessage());
            }else{
                $card = $this->get_xc_bean()->current_card;
                $currency_spent = $this->get_xc_bean()->currency_spent;

                $rate = $card['beans_rate'];

                if($card['is_active']){
                    printf(__('<div id="message1" class="update-nag">Your current rate is : %s beans are worth %s</div>', 'xcid_beans'), $rate, wc_price(1));

                    if($currency_spent['is_active']){
                        printf(__('<div id="message2" class="update-nag">Your currency spent is : %s.</div>', 'xcid_beans'), $currency_spent['beans']);
                    }else{
                        printf(__('<div id="message2" class="error"><p>Your currency spent is inactive</p></div>', 'xcid_beans'));
                    }

                }else{
                    printf(__('<div id="message" class="error"><p>Your account is on test mode</p></div>', 'xcid_beans'));
                }
            }
        }
    }

endif;

return new XC_Settings_Beans();
