<?php
/**
* Plugin Name: AMA
* Plugin URI: https://github.com/Dina-Rabe/Airtel-Money-Africa
* Description: This is a WordPress plugin who implements all Airtel Africa API available on their Developer Portal
* Version: 0.1
* Author: Dina Rabenarimanitra
* Author URI: https://www.linkedin.com/in/dina-rabenarimanitra-91aa0261/
**/
require_once plugin_dir_path(__FILE__) .'ama_content/Models.php';

function register_ama_currency_route() {
    register_rest_route( 'ama/v1', '/currency', array(
        'methods'  => 'GET',
        'callback' => 'ama_currency_callback',
    ) );
}

function ama_currency_callback( $request ) {
    // Your code to handle the API request and provide a response
    // Example: return an array of currency data
    $mainOption = new AMA_Options();
    $currency_data = array(
        'Currency' => $mainOption->currency_configured,
    );

    return $currency_data;
}

function register_ama_fetch_token(){
    register_rest_route( 
        'ama/v1', 
        '/token', 
        array(
            'methods' => 'GET',
            'callback' => 'ama_fetch_token_callback',
        ));
}

function ama_fetch_token_callback(){
    $ama_token = new AMA_Options();
    return $ama_token->get_ama_token();
}

function register_ama_fetch_kyc_info(){
    register_rest_route(
        'ama/v1',
        '/kyc',
        array(
            'methods' => 'POST',
            'callback' => 'ama_fetch_kyc_info_callback'
        ));
}

function ama_fetch_kyc_info_callback($request){
    $msisdn = $request->get_params('msisdn');
    $kyc_resp = new AMA_Kyc($msisdn);

    return json_encode($kyc_resp);

}

function register_ama_do_payment(){
    register_rest_route(
        'ama/v1',
        'payment',
        array(
            'methods' => 'POST',
            'callback' => 'ama_do_payment'        
        )
    );
}

function ama_do_payment($request){
    $payment = new AMA_Payment($request);
    $isTrue = $payment->do_payment();
    if($isTrue){
        return json_encode($payment);
    }else{
        return $payment->message;
    }
    
}

function register_ama_check_transaction_status(){
    register_rest_route(
        'ama/v1',
        '/transaction',
        array(
            'methods' => 'POST',
            'callback' => 'ama_check_transaction_status_callback'
        ));
}

function ama_check_transaction_status_callback($request){
    $payment = new AMA_Payment($request);
    $isTrue = $payment->check_transaction_status();
    if($isTrue){
        return json_encode($payment);
    }else{
        return $payment->message;
    }

}

//This is commented to avoid token to be fetched directly through the REST API
add_action('rest_api_init', 'register_ama_fetch_token');

add_action('rest_api_init', 'register_ama_check_transaction_status');
add_action('rest_api_init', 'register_ama_do_payment');
add_action('rest_api_init', 'register_ama_fetch_kyc_info');
add_action( 'rest_api_init', 'register_ama_currency_route' );

function set_amount($atts, $content = null) {
    $content = wp_kses_post($content);

    wp_enqueue_script('ama_transaction_amount', plugin_dir_url(__FILE__) . 'ama_content/script.js', array('jquery'), '1.0', true);

    return '<span id="transaction_amount">' . $content . '</span>';
}

function set_product_code($atts, $content = null) {
    $content = wp_kses_post($content);

    wp_enqueue_script('ama_product_code', plugin_dir_url(__FILE__) . 'ama_content/script.js', array('jquery'), '1.0', true);

    return '<span id="product_code">' . $content . '</span>';
}

function set_form($atts, $content = null) {
    $content = wp_kses_post($content);
    $payment_confirmation = file_get_contents(plugin_dir_url( __FILE__ ) . 'ama_content/payment_confirmation.html');

    wp_enqueue_script('ama_form', plugin_dir_url(__FILE__) . 'ama_content/script.js', array('jquery'), '1.0', true);
    wp_enqueue_style( 'ama_style', plugin_dir_url( __FILE__ ) . 'ama_content/style.css' );

    return  '<form id="ama_form">
                <label for="msisdn" id="ama_label">MSISDN:</label>
                <input type="tel" id="ama_msisdn" name="msisdn" placeholder="Phone number" reauired />
                <button type="button" id="ama_submit" url="" onclick="displayPaymentInformation()">Airtel Money</button>
            </form>'
            . $payment_confirmation 
            ;
}

add_shortcode( 'ama_amount', 'set_amount' );
add_shortcode( 'ama_product_code', 'set_product_code' );
add_shortcode( 'ama_form', 'set_form' );

// Add the admin menu page
add_action( 'admin_menu', 'ama_add_admin_page' );

function ama_add_admin_page() {
    add_menu_page(
        'AMA Plugin Settings',    // Page title
        'AMA Collection',         // Menu title
        'manage_options',         // Capability required to access the menu
        'ama_admin_page',         // Menu slug (unique identifier)
        'ama_render_settings_page',    // Callback function to render the page
        'dashicons-money-alt'     // Icon for the menu item
    );
}

// Render the settings page
function ama_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>AMA Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ama-settings-group' );
            do_settings_sections( 'ama-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action( 'admin_init', 'ama_register_settings' );

function ama_register_settings() {
    // Register the settings fields
    register_setting( 'ama-settings-group', 'ama_switch_mode' );
    register_setting( 'ama-settings-group', 'ama_client_id' );
    register_setting( 'ama-settings-group', 'ama_client_secret' );
    register_setting( 'ama-settings-group', 'ama_country' );
    register_setting( 'ama-settings-group', 'ama_currency' );
    register_setting( 'ama-settings-group', 'ama_email' );

    // Add settings sections
    add_settings_section(
        'ama-general-section',              // Section ID
        'General Settings',                 // Section title
        'ama_render_general_section',       // Callback function to render the section
        'ama-settings'                      // Page slug
    );

    // Add settings fields
    add_settings_field(
        'ama-switch-mode',                  // Field ID
        'Switch Mode',                      // Field title
        'ama_render_switch_mode_field',     // Callback function to render the field
        'ama-settings',                     // Page slug
        'ama-general-section'               // Section ID
    );
    add_settings_field(
        'ama-client-id',                    // Field ID
        'Client ID',                        // Field title
        'ama_render_client_id_field',       // Callback function to render the field
        'ama-settings',                     // Page slug
        'ama-general-section'               // Section ID
    );
    add_settings_field(
        'ama-client-secret',                // Field ID
        'Client Secret',                    // Field title
        'ama_render_client_secret_field',   // Callback function to render the field
        'ama-settings',                     // Page slug
        'ama-general-section'               // Section ID
    );
    add_settings_field(
        'ama-country',                      // Field ID
        'Country',                          // Field title
        'ama_render_country_field',         // Callback function to render the field
        'ama-settings',                     // Page slug
        'ama-general-section'               // Section ID
    );
    add_settings_field(
        'ama-currency',                     // Field ID
        'Currency',                         // Field title
        'ama_render_currency_field',        // Callback function to render the field
        'ama-settings',                     // Page slug
        'ama-general-section'               // Section ID
    );
    add_settings_field(
        'ama-email',                        // Field ID
        'Email',                            // Field title
        'ama_render_email_field',           // Callback function to render the field
        'ama-settings',                     // Page slug
        'ama-general-section'               // Section ID
    );
}

// Render the general settings section
function ama_render_general_section() {
    echo '<p>General settings for AMA Plugin</p>';
}

// Render the switch mode field
function ama_render_switch_mode_field() {
    $switch_mode = get_option( 'ama_switch_mode', 'https://openapiuat.airtel.africa/' );
    ?>
    <label>
        <input type="radio" name="ama_switch_mode" value="https://openapiuat.airtel.africa/" <?php checked( $switch_mode, 'https://openapiuat.airtel.africa/' ); ?>>
        Test Mode
    </label>
    <label>
        <input type="radio" name="ama_switch_mode" value="https://openapi.airtel.africa/" <?php checked( $switch_mode, 'https://openapi.airtel.africa/' ); ?>>
        Production Mode
    </label>
    <?php
}

// Render the client ID field
function ama_render_client_id_field() {
    $client_id = get_option( 'ama_client_id' );
    ?>
    <input type="text" name="ama_client_id" value="<?php echo esc_attr( $client_id ); ?>" />
    <?php
}

// Render the client secret field
function ama_render_client_secret_field() {
    $client_secret = get_option( 'ama_client_secret' );
    ?>
    <input type="text" name="ama_client_secret" value="<?php echo esc_attr( $client_secret ); ?>" />
    <?php
}

// Render the country field
function ama_render_country_field() {
    $country = get_option( 'ama_country' );
    ?>
    <select name="ama_country">
        <option value="UG" <?php selected( $country, 'UG' ); ?>>UGANDA</option>
        <option value="NG" <?php selected( $country, 'NG' ); ?>>NIGERIA</option>
        <option value="TZ" <?php selected( $country, 'TZ' ); ?>>TANZANIA</option>
        <option value="KE" <?php selected( $country, 'KE' ); ?>>KENYA</option>
        <option value="RW" <?php selected( $country, 'RW' ); ?>>RWANDA</option>
        <option value="ZM" <?php selected( $country, 'ZH' ); ?>>ZAMBIA</option>
        <option value="ZM" <?php selected( $country, 'ZM' ); ?>>ZAMBIA</option>
        <option value="GA" <?php selected( $country, 'GA' ); ?>>GABON</option>
        <option value="NE" <?php selected( $country, 'NE' ); ?>>NIGER</option>
        <option value="CG" <?php selected( $country, 'CG' ); ?>>CONGO-BRAZZAVILLE</option>
        <option value="CD" <?php selected( $country, 'CD' ); ?>>DR CONGO</option>
        <option value="SC" <?php selected( $country, 'SC' ); ?>>CHAD</option>
        <option value="SC" <?php selected( $country, 'SC' ); ?>>SEYCHELLES</option>
        <option value="MG" <?php selected( $country, 'MG' ); ?>>MADAGASCAR</option>
        <option value="MW" <?php selected( $country, 'MW' ); ?>>MALAWI</option>
    </select>
    <?php
}

// Render the currency field
function ama_render_currency_field() {
    $currency = get_option( 'ama_currency' );
    ?>
    <select name="ama_currency">
        <option value="UGX" <?php selected( $currency, 'UGX' ); ?>>Ugandan shilling</option>
        <option value="NGN" <?php selected( $currency, 'NGN' ); ?>>Nigerian naira</option>
        <option value="TZS" <?php selected( $currency, 'TZS' ); ?>>Tanzanian shilling</option>
        <option value="KES" <?php selected( $currency, 'KES' ); ?>>Kenyan shilling</option>
        <option value="RWF" <?php selected( $currency, 'RWF' ); ?>>Rwandan franc</option>
        <option value="ZMW" <?php selected( $currency, 'ZMW' ); ?>>Zambian kwacha</option>
        <option value="CFA" <?php selected( $currency, 'CFA' ); ?>>CFA franc</option>
        <option value="XOF" <?php selected( $currency, 'XOF' ); ?>>CFA franc BCEAO</option>
        <option value="XAF" <?php selected( $currency, 'XAF' ); ?>>CFA franc BCEA</option>
        <option value="CDF" <?php selected( $currency, 'CDF' ); ?>>Congolese franc</option>
        <option value="USD" <?php selected( $currency, 'USD' ); ?>>United States dollar</option>
        <option value="XAF" <?php selected( $currency, 'XAF' ); ?>>CFA franc BEAC</option>
        <option value="SCR" <?php selected( $currency, 'SCR' ); ?>>Seychelles rupee</option>
        <option value="MGA" <?php selected( $currency, 'MGA' ); ?>>Malagasy ariary</option>
        <option value="MWK" <?php selected( $currency, 'MWK' ); ?>>Malawian kwacha</option>
    </select>
    <?php
}
// Render the email field
function ama_render_email_field() {
    $email = get_option( 'ama_email' );
    ?>
    <input type="email" name="ama_email" value="<?php echo esc_attr( $email ); ?>" />
    <?php
}

function create_ama_payment_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ama_payments';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        msisdn VARCHAR(255) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        reference VARCHAR(255) NOT NULL,
        internal_id VARCHAR(255) NOT NULL,
        am_id VARCHAR(255) DEFAULT NULL,
        message VARCHAR(255) DEFAULT NULL,
        status VARCHAR(255) DEFAULT NULL,
        response_code VARCHAR(255) DEFAULT NULL,
        code VARCHAR(255) DEFAULT NULL,
        success BOOLEAN DEFAULT NULL,
        transaction_date DATETIME NOT NULL,
        transaction_type VARCHAR(255) DEFAULT NULL,
        base_url VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id),
        INDEX idx_msisdn (msisdn),
        INDEX idx_internal_id (internal_id),
        INDEX idx_am_id (am_id),
        INDEX idx_status (status),
        INDEX idx_transaction_date (transaction_date),
        INDEX idx_amount (amount)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'create_ama_payment_table' );