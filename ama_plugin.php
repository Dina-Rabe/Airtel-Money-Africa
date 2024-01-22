<?php
/**
* Plugin Name: AMA
* Plugin URI: https://github.com/Dina-Rabe/Airtel-Money-Africa
* Description: This is a WordPress plugin who implements all Airtel Africa API available on their Developer Portal
* Version: 0.1
* Author: Dina Rabenarimanitra
* Author URI: https://www.linkedin.com/in/dina-rabenarimanitra-91aa0261/
**/
//require_once plugin_dir_path(__FILE__) .'ama_bin/Models.php';
//require_once plugin_dir_path(__FILE__) .'ama_content/ama_payment.php';
/*
function ama_create_plugin_pages() {
    // Check if plugin pages already created
    $ama_payment_page = get_page_by_title('Airtel Money Payment');
    $ama_check_transaction_page = get_page_by_title('Airtel Money Check Transaction');
    $ama_payment_page_content = plugin_dir_path(__FILE__) .'ama_content/ama_payment.php';
    // Create the Payement page if empty
    if (empty($ama_payment_page)){
        $page_content = new AMA_Payment_Page_Content(
            'Airtel Money Payment', 
            file_get_contents($ama_payment_page_content)
        );
        $ama_payment_page = array(
            'post_title' => $page_content->getTitle(),
            'post_content' => $page_content->getContent(),
            'post_status' => 'publish',
            'post_type' => 'page'
        );
        wp_insert_post($ama_payment_page);
    
    }
    
    // Create the check transaction page if empty
    if (empty($ama_check_transaction_page)){
        $ama_check_transaction_page = array(
            'post_title' => 'Airtel Money Check Transaction',
            'post_content' => 'Content of Page 2',
            'post_status' => 'publish',
            'post_type' => 'page'
        );
        wp_insert_post($ama_check_transaction_page);
    }
    
}

function ama_delete_plugin_pages(){
    $ama_payment_page = get_page_by_title('Airtel Money Payment');
    $ama_check_transaction_page = get_page_by_title('Airtel Money Check Transaction');

    if($ama_payment_page){
        wp_delete_post($ama_payment_page->ID, true);
    }

    if($ama_check_transaction_page){
        wp_delete_post($ama_check_transaction_page->ID, true);
    }
}
*/

//Create a short code


// function register_custom_route_get_currency(){
//     register_rest_route( 
//         'ama/v1', 
//         '/currency', 
//         array(
//             'methods' => 'GET',
//             'callback' => 'fetch_currency_used'
//     ) );

// }

// function fetch_currency_used(){
//     global $wpdb;
//     $table_name = $wpdb->prefix .'options';
//     $results = $wpdb->get_results('SELECT option_value from ' . $table_name .' where option_name = "ama_currency"');
//     return rest_ensure_response( $results );
// }

// add_action( 'ama_rest_api_get_currency', 'register_custom_route_get_currency');


function register_ama_currency_route() {
    register_rest_route( 'ama/v1', '/currency', array(
        'methods'  => 'GET',
        'callback' => 'ama_currency_callback',
    ) );
}

function ama_currency_callback( $request ) {
    // Your code to handle the API request and provide a response
    // Example: return an array of currency data
    $currency_data = array(
        'USD' => 1.23,
        'EUR' => 0.92,
        'GBP' => 0.81,
    );

    return $currency_data;
}

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

    $rest_server = rest_get_server();
    $rest_routes = $rest_server->get_routes();

    $api_path = 'ama/v1/currency/';
    $api_check = 'Misy';

    if(isset($rest_routes[$api_path])){
        $api_check = 'Misy';
    }else{
        $api_check = 'Tsisy pory!';
    }

    return '<p>'
            . $api_check .
            '</p>
            <form id="ama_form">
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
    $switch_mode = get_option( 'ama_switch_mode', 'test' );
    ?>
    <select name="ama_switch_mode">
        <option value="https://openapiuat.airtel.africa/" <?php selected( $switch_mode, 'test' ); ?>>Test Mode</option>
        <option value="https://openapi.airtel.africa/" <?php selected( $switch_mode, 'production' ); ?>>Production Mode</option>
    </select>
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
