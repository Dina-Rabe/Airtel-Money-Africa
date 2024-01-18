<?php
/**
* Plugin Name: AMA
* Plugin URI: https://github.com/Dina-Rabe/Airtel-Money-Africa
* Description: This is a WordPress plugin who implements all Airtel Africa API available on their Developer Portal
* Version: 0.1
* Author: Dina Rabenarimanitra
* Author URI: https://www.linkedin.com/in/dina-rabenarimanitra-91aa0261/
**/

require_once 'ama_bin/AM_Payment_content.php';

function ama_create_plugin_pages() {
    // Check if plugin pages already created
    $ama_payment_page = get_page_by_title('Airtel Money Payment');
    $ama_check_transaction_page = get_page_by_title('Airtel Money Check Transaction');

    // Create the Payement page if empty
    if (empty($ama_payment_page)){
        $page_content = new AMA_Payment_Page_Content(
            get_bloginfo('name'), 
            get_bloginfo('description')
        );
        $content_html = $page_content->get_content_html();
        $ama_payment_page = array(
            'post_title' => 'Airtel Money Payment',
            'post_content' => $content_html,
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

register_activation_hook(__FILE__, 'ama_create_plugin_pages');
register_deactivation_hook(__FILE__, 'ama_delete_plugin_pages');