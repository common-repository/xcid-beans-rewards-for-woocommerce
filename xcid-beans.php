<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly
/*
**************************************************************************

Plugin Name:  Beans Loyalty & Reward Program Woocommerce
Plugin URI:   https://wordpress.org/plugins/xcid-beans-rewards-for-woocommerce/
Description:  Loyalty & Reward Program integration for Woocommerce
Version:      1.1
Author:       XciD
Author URI:   mailto:xcid@xcid.fr
**************************************************************************/

use Beans\Exception\BeansException;
use Beans\Beans;

final class XC_Bean {
    protected static $_instance = null;

    /**
     * Plugin options
     * @var array
     */
    private $options;
    /**
     * Session data for storage
     * @var
     */
    private $session_data;
    /** @var Beans */
    private $bean_api;

    /**
     * Current Shop Card, with some info about the Shop
     * @var array
     */
    public $current_card;

    /**
     * Currency Spent, ratio to convert money to beans
     * @var array
     */
    public $currency_spent;

    /**
     * Current user if connected
     * @var array
     */
    public $current_account;
    public $current_account_id;
    public $current_account_authentication;

    private $redeem = false;

    /**
     * @var BeansException
     */
    public $last_api_error = null;

    /**
     * XC_Bean constructor
     */
    public function __construct() {
        // Put our object on WC in order to access quickly
        WC()->beans = $this;

        // Load includes and define const
        $this->load_includes();
        $this->define_const();

        load_plugin_textdomain( 'xcid_beans', false, 'xcid-beans/languages/' );

        // We also load our options
        $this->load_options();

        $this->init_beans();

        // Load admin page
        $this->load_admin_page();

        if ( $this->options[ 'enabled' ] !== 'yes' || !$this->current_card['is_active']){
            return;
        }

        // Then we add ours hooks
        $this->init_hooks();

        if ( $this->is_front() ) {
            // If we are on front, we load our sessions variables
            $this->load_session_variables();

            // Load webservices
            $this->create_webservices();

            // Load bean user
            $this->init_beans_user();

            // Then load front scripts
            $this->enqueue_front_scripts();
        }
    }

    /**
     * Define const
     */
    private function define_const() {
        define( 'XCID_BEAN_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
        define( 'XCID_BEAN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'XCID_BEAN_PLUGIN_VERSION', "0.1" );
    }

    /**
     * Load PHP includes
     */
    private function load_includes() {
        include_once ('classes/beans.php');
    }

    /**
     * Singleton function
     * @return null|XC_Bean
     */
    public static function instance() {
        if(!function_exists('WC'))
            return false;

        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Define if we are on front page or not
     * @return bool
     */
    public function is_front() {
        return ( !is_admin() || defined( 'DOING_AJAX' ) ) && !defined( 'DOING_CRON' );
    }

    /**
     * Load object variable from session
     * Array must be like :
     *
     * array(
            'name' => 'session_name_variable',
     *      'default' => 'default value'
     * )
     *
     */
    private function load_session_variables() {
        $sessions_variables = array(
            array(
                'name' => 'current_account_id',
                'default' => null
            ),
            array(
                'name' => 'redeem',
                'default' => false
            ),
        );

        $this->session_data = array();

        if(!WC()->session->has_session()){
            WC()->session->set_customer_session_cookie(true);
        }

        foreach($sessions_variables as $session_variable){
            $this->session_data[$session_variable['name']] = $this->get_session_variable($session_variable['name'], $session_variable['default']);
        }
    }

    /**
     * Set value on session
     * @param $key
     * @param $value
     */
    private function set_session_variable($key, $value){
        WC()->session->set('xcid_beans_' . $key, $value);
        $this->session_data[$key] = $value;
    }

    /**
     * Get value on session
     * @param $key
     * @param $value
     */
    private function get_session_variable($key, $default = null){
        return WC()->session->get('xcid_beans_' . $key, $default);
    }

    /**
     * Add hooks on WooCommerce
     */
    private function init_hooks() {
        // Hook to display the buttons on cart
        if ( $this->options[ 'cart_enabled' ] === 'yes')
            add_action( $this->options[ 'cart_hook' ],          array( $this, 'display_message_on_cart' ), 10 );

        // Hook to display the buttons on product
        if ( $this->options[ 'product_enabled' ] === 'yes')
            add_action( $this->options[ 'product_hook' ],       array( $this, 'display_message_on_product' ), 10 );

        // Hook to display the buttons on checkout
        if ( $this->options[ 'checkout_enabled' ] === 'yes' )
            add_action( $this->options[ 'checkout_hook' ],      array( $this, 'display_message_on_cart' ), 10 );


        /** Add Discount if reddem is on see add_discount */
        add_filter( 'woocommerce_cart_calculate_fees' ,         array( $this, 'add_discount') );

        /** Debit Beans on fee woocommerce check */
        add_action( 'woocommerce_add_order_fee_meta' ,          array( $this, 'debit_beans_after_order'), 10 , 4 );

        /** Credit the user once order is paid */
        add_action( 'woocommerce_order_status_processing' ,     array( $this, 'credit_beans_after_order'), 10 , 1 );

        /** Refund the user if the order is refunded / cancelle or failed */
        add_action( 'woocommerce_order_status_refunded' ,       array( $this, 'refund_beans'), 10 , 1 );
        add_action( 'woocommerce_order_status_cancelled' ,      array( $this, 'refund_beans'), 10 , 1 );
        add_action( 'woocommerce_order_status_failed' ,         array( $this, 'refund_beans'), 10 , 1 );

        /** Save user beans id after order meta */
        add_action( 'woocommerce_checkout_update_order_meta' ,  array( $this, 'save_current_user_id_meta'), 10 , 2 );

        /** Save the user to beans system */
        add_action( 'woocommerce_registration_errors' ,         array( $this, 'register_user'), 10 , 3 );
        /** Save beans user id to db */
        add_action( 'woocommerce_created_customer' ,            array( $this, 'save_user_db'), 10 , 3 );
        /** Add firstName + LastName on register form */
        add_action( 'woocommerce_register_form_start',          array( $this, 'add_register_fields' ) );
        /** Handle redirect to checkout or cart page after signin */
        add_filter( 'woocommerce_registration_redirect',        array( $this, 'registration_redirect'), 10, 1 );

        add_shortcode('reward_program',                            array($this, 'reward_program_display'));
    }

    /**
     * Load admin page on WC settings, also register script
     */
    private function load_admin_page() {
        add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ), 10, 1 );
        wp_enqueue_script( 'xcid-beans-settings', XCID_BEAN_PLUGIN_URL . '/assets/js/admin/settings.js', array( 'jquery', 'select2' ), XCID_BEAN_PLUGIN_VERSION, true );

        add_action( 'admin_notices', array( $this, 'display_first_install' ) );
        add_action( 'admin_notices', array( $this, 'display_callback_install' ) );
        add_action( 'admin_notices', array( $this, 'display_failtoken_notice' ) );

        if(is_admin() && is_super_admin() && isset($_GET['beans-success'])){
            $this->bulk_current_users();
        }
    }

    /**
     * Add our class-wc-settings-beans to WC setting page
     * @param $settings
     */
    public function add_settings_page( $settings ) {
        $settings[] = include( XCID_BEAN_PLUGIN_DIR_PATH . '/admin/class-wc-settings-beans.php' );
    }

    /**
     * Load plugin options from database
     */
    private function load_options() {
        // Callback Token
        if ( ! empty( $_GET['token'] ) && is_admin() && is_super_admin() ) {
            $bean_api = new Beans();

            $admin = admin_url();

            try{
                $result = $bean_api->get('app_key/' . wc_clean( $_GET['token']));
                update_option('xcid_beans_api_key', $result['secret']);
                update_option('xcid_beans_enabled', 'yes');
                wp_safe_redirect($admin . "?beans-success");
            }catch(Exception $ex){
                wp_safe_redirect($admin . "?beans-error");
            }
        }

        $options = array(
            'enabled'          => 'no',
            'cart_enabled' => 'yes',
            'cart_hook' => 'woocommerce_cart_collaterals',
            'product_enabled' => 'yes',
            'product_hook'     => 'woocommerce_after_add_to_cart_form',
            'checkout_enabled' => 'yes',
            'checkout_hook'     => 'woocommerce_before_checkout_form',
            'api_key' => ''
        );

        foreach ( $options as $option => $default ) {
            $this->options[ $option ] = get_option( 'xcid_beans_' . $option, $default );
        }
    }

    /**
     * Enqueue front Scripts.
     */
    private function enqueue_front_scripts() {
        // JS
        wp_enqueue_script( 'xcid-beans', '//www.trybeans.com/assets/static/js/lib/1.1/beans.js', array(), '0.9', true );
        wp_enqueue_script( 'xcid-beans-main', XCID_BEAN_PLUGIN_URL . '/assets/js/front/main.js', array('jquery', 'xcid-beans'), XCID_BEAN_PLUGIN_VERSION, true );

        // CSS
        wp_enqueue_style( 'xcid-beans-main', XCID_BEAN_PLUGIN_URL . '/assets/css/main.css', array(), XCID_BEAN_PLUGIN_VERSION );

        add_action('wp_head', array($this , 'custom_css'));

        wp_localize_script( 'xcid-beans-main', 'xcid_beans', array(
            'id' => $this->current_card['address'],
            'login_page' => wc_get_page_permalink( 'myaccount' ),
            'about_page' => get_permalink( get_option('xcid_beans_page_reward_program_id') ),
            'authentication' => $this->current_account_authentication != null ? $this->current_account_authentication : null
        ) );
    }

    public function custom_css(){
        ?>
        <style type="text/css">
            .beans-primary{
                color:<?php echo $this->current_card['style']['primary_color'] ?>;
            }
            .beans-primary-bg{
                background-color:<?php echo $this->current_card['style']['primary_color'] ?>;
            }
            .beans-unit, .beans-secondary{
                color:<?php echo $this->current_card['style']['secondary_color'] ?>;
            }
            .beans-header{
                background-image: url("<?php echo $this->current_card['cover'] ?>");
            }
        </style>
        <?php
    }

    /**
     * Init Beans Shop Card if the api_key is alive
     */
    private function init_beans() {
        $this->bean_api = new Beans($this->options['api_key']);

        // Check current Card to see if api key is right
        $this->last_api_error = null;

        $this->current_card = $this->call_bean_api('card/current');
        $this->currency_spent = $this->call_bean_api('rule/beans:currency_spent');
    }

    /**
     * Init current user if we have something on session
     */
    private function init_beans_user() {
        $user = wp_get_current_user();

        if($user->ID > 0){
            $bean_account = get_user_meta($user->ID, 'beans_account_id', true);
            if($bean_account){
                $this->current_account = $this->call_bean_api('account/' . $bean_account);

                if(isset($_COOKIE['beans_session'])) {
                    $beans_session = json_decode( stripslashes( $_COOKIE[ 'beans_session' ] ) );
                }else{
                    $beans_session = false;
                }

                if(!$beans_session || !$beans_session->current_account){
                    $data = array('account' => $this->current_account['id']);
                    $oauth = $this->call_bean_api('oauth', true, $data, 'POST');
                    if($oauth){
                        $this->current_account_authentication = $oauth['authentication'];
                    }
                }
            }else{
                // Check to API
                $client = $this->call_bean_api('account/' . $user->user_email);

                if(!$client){
                    $data = array(
                        'email' => $user->user_email,
                        'first_name' => $user->billing_first_name,
                        'last_name' => $user->billing_last_name
                    );

                    $client = $this->call_bean_api('account', true, $data, 'POST');
                }

                $this->current_account = $client;

                update_option('beans_account_id', $this->current_account['id']);
            }
        }

        $this->redeem = $this->session_data['redeem'];
    }

    /**
     * Generic call to Beans API
     * return false on error and store the error on last_api_error variable
     * If last_api_error null and no force => return false
     * @param $method
     * @return array|bool|mixed|object
     */
    public function call_bean_api($path, $force = false, $data = null, $method = 'GET' ){
        if(!is_null($this->last_api_error) && !$force)
            return false;

        if(is_null($this->bean_api))
            return false;

        if($force)
            $this->last_api_error = null;

        try{
            return $this->bean_api->make_request($path, $data, $method);
        }catch(BeansException $ex){
            $this->last_api_error = $ex;
            return false;
        }
    }

    /**
     * Reinit bean errors and api key, Used after Woocommerce Save Options pages
     */
    public function re_init_bean() {
        $this->load_options();
        $this->init_beans();
    }

    /**
     * Get Templates
     * @param $template_name
     * @param array $args
     * @param bool $return
     * @return null|string
     */
    public static function get_template( $template_name, $args = array(), $return = false ) {
        if ( $return ) {
            ob_start();
            self::get_template( $template_name, $args, false );
            return ob_get_clean();
        } else {
            $wc_get_template = function_exists( 'wc_get_template' ) ? 'wc_get_template' : 'woocommerce_get_template';
            $wc_get_template( $template_name, $args, '', XCID_BEAN_PLUGIN_DIR_PATH . 'templates/' );
            return null;
        }
    }

    public function display_message_on_cart( $return = false ){
        if(!is_null($this->last_api_error))
            return "";

        $return = $return === true;

        $disable_button = false;

        $worth = $this->get_template('worth.php', array(
            'worth' => $this->print_bean($this->current_card['beans_rate']),
            'link' => get_permalink( get_option('xcid_beans_page_reward_program_id') )
        ), true);

        // First if no user logged
        if(is_null($this->current_account)){
            $message = $this->get_template('cart-join.php', array(
                'beans' => $this->print_bean(WC()->cart->subtotal * $this->currency_spent['beans']),
                'worth' => $worth
            ), true);

            $button_message = 'Join';
            $button_action = 'join';
        }
        // If user logged and no credit used
        else if($this->current_account && !$this->redeem){
            $message = $this->get_template('cart-redeem.php', array(
                'beans' => $this->print_bean($this->current_account['beans']),
                'worth' => $worth
            ), true);

            $button_message = 'Redeem';
            $button_action = 'redeem';

            $disable_button = $this->current_account['beans'] == 0;
        }
        // If user logged and credit used
        else if($this->current_account && $this->redeem){
            $message = $this->get_template('cart-cancel.php', array(
                'beans' => $this->print_bean($this->current_account['beans']),
                'worth' => $worth
            ), true);

            $button_message = 'Cancel';
            $button_action = 'cancel';
        }

        return $this->get_template('cart-message.php', array(
            'message' => $message,
            'button_message' => $button_message,
            'button_action' => $button_action,
            'disable_button' => $disable_button
        ), $return);
    }

    /**
     * Display Message on Product Page
     */
    public function display_message_on_product(){
        global $post;
        $product = new WC_Product($post->ID);

        $worth = $this->get_template('worth.php', array(
            'worth' => $this->print_bean($this->current_card['beans_rate']),
            'link' => get_permalink( get_option('xcid_beans_page_reward_program_id') )
        ), true);

        $this->get_template('product-message.php', array(
            'beans' => $this->print_bean($product->price * $this->currency_spent['beans']),
            'worth' => $worth
        ));
    }

    /**
     * Create WebServices Hooks
     */
    private function create_webservices() {
        $webservices = array(
            array(
                'name' => 'redeem',
                'function' => array($this, 'beans_redeem'),
                'nopriv' => true
            ),
            array(
                'name' => 'cancel',
                'function' => array($this, 'beans_cancel'),
                'nopriv' => true
            )
        );

        foreach($webservices as $webservice){
            add_action('wp_ajax_xcid_beans_' . $webservice['name'], $webservice['function']);

            if($webservice['nopriv'])
                add_action('wp_ajax_nopriv_xcid_beans_' . $webservice['name'], $webservice['function']);
        }
    }

    /**
     * Set Redeem variable to true
     */
    public function beans_redeem(){
        header( 'Content-Type: application/json; charset=utf-8' );

        if($this->current_account == null)
            die('error');

        $this->set_session_variable('redeem', true);
        die();
    }

    /**
     * Cancel Redeem
     */
    public function beans_cancel(){
        header( 'Content-Type: application/json; charset=utf-8' );

        if($this->current_account == null)
            die('error');

        $this->set_session_variable('redeem', false);
        die();
    }

    /**
     * Add a discount the the cart as negative fee
     * @param WC_Cart|null $cart
     */
    function add_discount( WC_Cart $cart = null ){
        if($cart == null)
            return;

        if($this->redeem)
            $cart->add_fee( $this->current_card['beans_name'] , - round($this->current_account['beans'] / $this->current_card['beans_rate'], 2));
    }

    /**
     * Save beans_user_id on the order
     * @param $order_id
     * @param $posted
     */
    function save_current_user_id_meta($order_id, $posted){
        add_post_meta($order_id, '_xcid_beans_user_id', $this->current_account['id']);
    }

    /**
     * Refund the user on cancelled order
     * @param $order_id
     */
    function refund_beans($order_id){
        $order = new WC_Order($order_id);

        if(!$order->xcid_beans_de_id || empty($order->xcid_beans_user_id) || $order->xcid_beans_de_refunded){
            return;
        }

        $de = $order->xcid_beans_de_id;

        $new_de = $this->call_bean_api('debit/' . $de['id'] . '/cancel', true, null, 'POST');

        if($new_de){
            update_post_meta($order_id, '_xcid_beans_de_refunded', true);
            $order->add_order_note('Beans refunded');
        }else{
            $order->add_order_note('Error during redund : ' . $this->last_api_error->getMessage());
        }
    }

    /**
     * Credit beans to the user after the order
     * @param $order_id
     * @throws Exception
     */
    function credit_beans_after_order($order_id){
        $order = new WC_Order($order_id);

        if($order->xcid_beans_cr_id || empty($order->xcid_beans_user_id)){
            return;
        }

        $total = $order->get_total() - $order->get_total_shipping();

        $data = array(
            'quantity' => (string) $total,
            'rule' => 'beans:currency_spent',
            'account' => $order->xcid_beans_user_id,
            'description' => sprintf(__("Customer loyalty rewarded for a %s purchase", 'xcid_beans'), $total . $this->current_card['currency']),
            'uid' => 'order_' . $order_id
        );

        $cr = $this->call_bean_api('credit', true, $data, 'POST');

        if($cr){
            update_post_meta($order_id, '_xcid_beans_cr_id', $cr['id']);
            $order->add_order_note('Beans credit id : ' . $cr['id'] . 'for ' . $total);
        }else{
            $order->add_order_note('Error in beans credit : ' . $this->last_api_error->getMessage());
        }
    }

    /**
     * Debit the user if he choose to redeem
     * @param $order_id
     * @param $item_id
     * @param $fee
     * @param $fee_key
     * @throws Exception
     */
    function debit_beans_after_order($order_id, $item_id, $fee, $fee_key){
        if($fee->id !== strtolower($this->current_card['beans_name']))
            return;

        $this->set_session_variable('redeem', false);
        $this->redeem = false;

        $amount = (string) (-$fee->amount);

        $data = array(
            'account' => $this->current_account['id'],
            'rule' => $this->current_card['currency'],
            'quantity' => (string) $amount,
            'description' => sprintf(__("Debited for a %s %s discount", 'xcid_beans'), $amount, $this->current_card['currency']),
            'uid' =>  "order_" . $order_id
        );

        $order = new WC_Order($order_id);

        $de = $this->call_bean_api('debit', true, $data, 'POST');
        if($de){
            update_post_meta($order_id, '_xcid_beans_de_id', $de['id']);
            $order->add_order_note('Beans debit id : ' . $de['id']);
        }else{
            $order->add_order_note('Error in beans debit : ' . $this->last_api_error->getMessage());
        }
    }

    /**
     * Add FirstName and LastName as new field + referer for redirect
     */
    function add_register_fields(){
        $billing_first_name = ( isset( $_POST[ 'billing_first_name' ] ) ) ? $_POST[ 'billing_first_name' ] : '';
        $billing_last_name = ( isset( $_POST[ 'billing_last_name' ] ) ) ? $_POST[ 'billing_last_name' ] : '';
        $redirect = ( isset( $_POST[ 'redirect' ] ) ) ? $_POST[ 'redirect' ] : $_SERVER['HTTP_REFERER'];

        $this->get_template('register-form.php', array(
            'billing_first_name' => $billing_first_name,
            'billing_last_name' => $billing_last_name,
            'redirect' => $redirect
        ));
    }

    /**
     * Save the user to Beans, On error return WP_Error filled
     * On Save, save the user_id on $this->current_account_id
     * @param WP_Error $validation_errors
     * @param $username
     * @param $email
     * @return WP_Error
     */
    function register_user(WP_Error $validation_errors, $username, $email){
        $billing_first_name = ( isset( $_POST[ 'billing_first_name' ] ) ) ? $_POST[ 'billing_first_name' ] : '';
        $billing_last_name = ( isset( $_POST[ 'billing_last_name' ] ) ) ? $_POST[ 'billing_last_name' ] : '';

        $data = array(
            'email' => $email,
            'first_name' => $billing_first_name,
            'last_name' => $billing_last_name
        );

        $client = $this->call_bean_api('account', true, $data, 'POST');

        if($client){
            $this->current_account_id = $client['id'];
        }else{
            $validation_errors->add(400, $this->last_api_error->getMessage());
        }

        return $validation_errors;
    }

    /**
     * Save l'user en base de donnée
     * @param $user_id
     * @param $new_customer_data
     * @param $password_generated
     */
    function save_user_db($user_id, $new_customer_data, $password_generated){
        $billing_first_name = ( isset( $_POST[ 'billing_first_name' ] ) ) ? $_POST[ 'billing_first_name' ] : '';
        $billing_last_name = ( isset( $_POST[ 'billing_last_name' ] ) ) ? $_POST[ 'billing_last_name' ] : '';

        update_user_meta( $user_id, 'billing_last_name', $billing_last_name );
        update_user_meta( $user_id, 'billing_first_name', $billing_first_name );
        update_user_meta( $user_id, 'beans_account_id', $this->current_account_id );
    }

    /**
     * Filtre avant la redirection de l'inscription pour renvoyer vers la page commande.
     * @param $redirect
     * @return mixed
     *
     */
    function registration_redirect( $redirect ) {
        if ( isset( $_POST[ 'redirect' ] ) && !empty($_POST[ 'redirect' ]) ) {
            $redirect = $_POST[ 'redirect' ];
        }
        return $redirect;
    }

    /**
     * Send all users to Beans
     */
    function bulk_current_users(){
        $customers = get_users( 'role=customer' );

        while(sizeof($customers) != 0){
            $data = array();
            while(sizeof($data) < 1000 && sizeof($customers) != 0){
                $customer = array_shift($customers);
                $data[] = array(
                    'last_name' => $customer->billing_last_name,
                    'first_name' => $customer->billing_first_name,
                    'email' => $customer->user_email
                );
            }
            $this->call_bean_api('account', true, array("data" => $data), 'POST');
        }
    }

    /**
     * Display First Install
     */
    function reward_program_display(){
        $join = false;
        $balance = false;
        $history = false;

        $rules = $this->call_bean_api('rule');

        // First if no user logged
        if(is_null($this->current_account)){
            $join = true;
            $join_rule = $this->call_bean_api('rule/beans:new_beans_card');
        }
        // If user logged and no credit used
        else {
            $balance = $this->current_account['beans'];
            $history = $this->call_bean_api('account/' . $this->current_account['id'] . '/history');
        }

        $this->get_template('reward-program.php', array(
            'balance' => $this->get_template('reward-program-balance.php',array(
                'balance' => $balance,
                'beans_name' => $this->current_card['beans_name']
            ), true),
            'join' => $this->get_template('reward-program-join.php', array(
                'join' => $join,
                'beans' => $this->print_bean($join_rule['beans']),
                'signup_link' => wc_get_page_permalink( 'myaccount' )
            ),true),
            'rules' => $this->get_template('reward-program-rules.php', array(
                'rules' => $rules,
                'card' => $this->current_card
            ),true),
            'history' => $this->get_template('reward-program-history.php', array(
                'history' => $history
            ),true),
            'url' => $this->current_card['url']
        ));
    }

    static function install(){
        wc_create_page( esc_sql( __('reward-program', 'xcid_beans') ), 'xcid_beans_page_reward_program_id', __('Reward Program', 'xcid_beans'), '[reward_program]', '' );
    }

    function display_first_install(){
        if( empty($this->options['api_key']) )
            $this->get_template("install.php");
    }

    function display_callback_install(){
        if( isset($_GET['beans-success']))
            $this->get_template("callback.php");
    }

    function display_failtoken_notice(){
        if( isset($_GET['beans-error']))
            $this->get_template("fail.php");
    }

    static function uninstall(){
        wp_trash_post( get_option( 'xcid_beans_page_reward_program_id' ) );
    }

    function print_bean($beans, $return = true){
        $beans = round($beans, 2);
        return $this->get_template('beans.php', array(
            'beans_name' => $this->current_card['beans_name'],
            'beans' => $beans
        ), $return);
    }
}



add_action( 'init', array('XC_Bean', 'instance') );
register_activation_hook( __FILE__, array( 'XC_Bean', 'install' ) );
register_uninstall_hook(__FILE__, array( 'XC_Bean', 'uninstall' ));